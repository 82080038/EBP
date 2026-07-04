"""
RAG (Retrieval-Augmented Generation) system with vector store.

Provides context-aware AI research by retrieving relevant documents
before generating analysis. Uses TF-IDF vectors as a lightweight
embedding approach with cosine similarity, with optional upgrade
to sentence-transformers when available.

References:
- Lewis et al. (2020), "Retrieval-Augmented Generation for Knowledge-Intensive NLP Tasks"
- Qdrant: Vector database for production RAG
"""
from __future__ import annotations

import hashlib
import json
import logging
import pickle
from dataclasses import dataclass, field
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional

import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

logger = logging.getLogger(__name__)

TRY_SENTENCE_TRANSFORMERS = True
try:
    from sentence_transformers import SentenceTransformer
    HAS_ST = True
except ImportError:
    HAS_ST = False
    logger.info("sentence-transformers not available — using TF-IDF for RAG embeddings")


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class Document:
    """A document in the RAG knowledge base."""
    doc_id: str = ""
    content: str = ""
    metadata: Dict = field(default_factory=dict)
    embedding: Optional[np.ndarray] = None
    source: str = ""
    timestamp: str = ""
    category: str = ""


@dataclass
class RetrievalResult:
    """Result from RAG retrieval."""
    query: str = ""
    retrieved_docs: List[Document] = field(default_factory=list)
    similarity_scores: List[float] = field(default_factory=list)
    context: str = ""
    n_docs_retrieved: int = 0


@dataclass
class RAGResult:
    """Complete RAG result with retrieval and generation context."""
    retrieval: RetrievalResult = field(default_factory=RetrievalResult)
    answer: str = ""
    sources: List[str] = field(default_factory=list)
    confidence: float = 0.0


# =============================================================================
# VECTOR STORE
# =============================================================================


class VectorStore:
    """
    Lightweight vector store using TF-IDF or sentence-transformers embeddings.
    Persists to disk as JSON + pickle.
    """

    def __init__(
        self,
        storage_path: str = "src/data/rag_store",
        embedding_method: str = "tfidf",
        model_name: str = "all-MiniLM-L6-v2",
    ):
        self.storage_path = Path(storage_path)
        self.storage_path.mkdir(parents=True, exist_ok=True)
        self.embedding_method = embedding_method
        self.model_name = model_name

        self.documents: List[Document] = []
        self.embeddings_matrix: Optional[np.ndarray] = None
        self.tfidf_vectorizer: Optional[TfidfVectorizer] = None
        self.st_model = None

        if embedding_method == "sentence_transformer" and HAS_ST:
            try:
                self.st_model = SentenceTransformer(model_name)
                logger.info(f"Loaded sentence-transformer model: {model_name}")
            except Exception as e:
                logger.warning(f"Failed to load sentence-transformer: {e}, falling back to TF-IDF")
                self.embedding_method = "tfidf"

        self._load()

    def _generate_id(self, content: str) -> str:
        """Generate unique doc ID from content hash."""
        return hashlib.md5(content.encode()).hexdigest()[:12]

    def _embed(self, texts: List[str]) -> np.ndarray:
        """Generate embeddings for a list of texts."""
        if self.embedding_method == "sentence_transformer" and self.st_model is not None:
            embeddings = self.st_model.encode(texts, show_progress_bar=False)
            return np.array(embeddings)
        else:
            if self.tfidf_vectorizer is None:
                self.tfidf_vectorizer = TfidfVectorizer(
                    max_features=5000,
                    stop_words="english",
                    ngram_range=(1, 2),
                )
                if texts:
                    self.tfidf_vectorizer.fit(texts)
            embeddings = self.tfidf_vectorizer.transform(texts).toarray()
            return np.array(embeddings)

    def _embed_query(self, query: str) -> np.ndarray:
        """Embed a single query."""
        if self.embedding_method == "sentence_transformer" and self.st_model is not None:
            return self.st_model.encode([query], show_progress_bar=False)
        else:
            if self.tfidf_vectorizer is None:
                # Fallback: create a basic vector
                return np.zeros((1, 100))
            return self.tfidf_vectorizer.transform([query]).toarray()

    def add_documents(
        self,
        texts: List[str],
        metadatas: Optional[List[Dict]] = None,
        sources: Optional[List[str]] = None,
        categories: Optional[List[str]] = None,
    ) -> int:
        """Add documents to the vector store."""
        if not texts:
            return 0

        if metadatas is None:
            metadatas = [{} for _ in texts]
        if sources is None:
            sources = ["unknown" for _ in texts]
        if categories is None:
            categories = ["general" for _ in texts]

        # Generate embeddings
        embeddings = self._embed(texts)

        new_docs = []
        for i, (text, meta, source, category) in enumerate(zip(texts, metadatas, sources, categories)):
            doc = Document(
                doc_id=self._generate_id(text),
                content=text,
                metadata=meta,
                embedding=embeddings[i],
                source=source,
                timestamp=datetime.now().isoformat(),
                category=category,
            )
            new_docs.append(doc)

        self.documents.extend(new_docs)
        self._rebuild_index()
        self._save()
        return len(new_docs)

    def _rebuild_index(self):
        """Rebuild the embeddings matrix."""
        if self.documents:
            self.embeddings_matrix = np.array([d.embedding for d in self.documents])
        else:
            self.embeddings_matrix = None

    def search(
        self,
        query: str,
        top_k: int = 5,
        min_similarity: float = 0.1,
    ) -> RetrievalResult:
        """
        Search for relevant documents.

        Args:
            query: Search query
            top_k: Number of top results to return
            min_similarity: Minimum similarity threshold

        Returns:
            RetrievalResult with matched documents
        """
        if not self.documents or self.embeddings_matrix is None:
            return RetrievalResult(query=query)

        query_embedding = self._embed_query(query)

        # Cosine similarity
        similarities = cosine_similarity(query_embedding, self.embeddings_matrix)[0]

        # Sort by similarity
        sorted_indices = np.argsort(similarities)[::-1]

        retrieved = []
        scores = []
        for idx in sorted_indices[:top_k]:
            if similarities[idx] >= min_similarity:
                retrieved.append(self.documents[idx])
                scores.append(float(similarities[idx]))

        # Build context string
        context_parts = []
        for doc, score in zip(retrieved, scores):
            context_parts.append(
                f"[{doc.category}] (sim: {score:.2f}) {doc.content[:500]}"
            )
        context = "\n\n".join(context_parts)

        return RetrievalResult(
            query=query,
            retrieved_docs=retrieved,
            similarity_scores=scores,
            context=context,
            n_docs_retrieved=len(retrieved),
        )

    def clear(self):
        """Clear all documents."""
        self.documents = []
        self.embeddings_matrix = None
        self.tfidf_vectorizer = None
        self._save()

    def get_stats(self) -> Dict:
        """Get vector store statistics."""
        categories = {}
        for doc in self.documents:
            cat = doc.category
            categories[cat] = categories.get(cat, 0) + 1

        return {
            "total_documents": len(self.documents),
            "categories": categories,
            "embedding_method": self.embedding_method,
            "storage_path": str(self.storage_path),
        }

    def _save(self):
        """Save to disk."""
        # Save documents as JSON
        docs_data = []
        for doc in self.documents:
            docs_data.append({
                "doc_id": doc.doc_id,
                "content": doc.content,
                "metadata": doc.metadata,
                "source": doc.source,
                "timestamp": doc.timestamp,
                "category": doc.category,
            })

        with open(self.storage_path / "documents.json", "w", encoding="utf-8") as f:
            json.dump(docs_data, f, ensure_ascii=False, indent=2)

        # Save embeddings and vectorizer
        if self.embeddings_matrix is not None:
            np.save(self.storage_path / "embeddings.npy", self.embeddings_matrix)
        if self.tfidf_vectorizer is not None:
            with open(self.storage_path / "tfidf.pkl", "wb") as f:
                pickle.dump(self.tfidf_vectorizer, f)

    def _load(self):
        """Load from disk."""
        docs_path = self.storage_path / "documents.json"
        if docs_path.exists():
            try:
                with open(docs_path, "r", encoding="utf-8") as f:
                    docs_data = json.load(f)

                embeddings_path = self.storage_path / "embeddings.npy"
                embeddings = None
                if embeddings_path.exists():
                    embeddings = np.load(embeddings_path, allow_pickle=True)

                for i, d in enumerate(docs_data):
                    doc = Document(
                        doc_id=d["doc_id"],
                        content=d["content"],
                        metadata=d.get("metadata", {}),
                        embedding=embeddings[i] if embeddings is not None else None,
                        source=d.get("source", ""),
                        timestamp=d.get("timestamp", ""),
                        category=d.get("category", "general"),
                    )
                    self.documents.append(doc)

                self._rebuild_index()

                # Load TF-IDF vectorizer
                tfidf_path = self.storage_path / "tfidf.pkl"
                if tfidf_path.exists() and self.embedding_method == "tfidf":
                    with open(tfidf_path, "rb") as f:
                        self.tfidf_vectorizer = pickle.load(f)

                logger.info(f"Loaded {len(self.documents)} documents from {self.storage_path}")
            except Exception as e:
                logger.warning(f"Failed to load RAG store: {e}")


# =============================================================================
# RAG SYSTEM
# =============================================================================


class RAGSystem:
    """
    Full RAG pipeline: retrieve relevant context, then generate analysis.
    """

    def __init__(
        self,
        storage_path: str = "src/data/rag_store",
        embedding_method: str = "tfidf",
    ):
        self.vector_store = VectorStore(storage_path, embedding_method)
        self.llm = None

        # Try to load local LLM
        try:
            from src.local_llm import LocalLLM
            self.llm = LocalLLM()
        except Exception:
            logger.info("Local LLM not available — RAG will return context only")

    def ingest_news(self, news_items: List[Dict]) -> int:
        """
        Ingest news articles into the knowledge base.

        Args:
            news_items: List of dicts with 'title', 'summary', 'source', 'date'

        Returns:
            Number of documents added
        """
        texts = []
        metadatas = []
        sources = []
        categories = []

        for item in news_items:
            title = item.get("title", "")
            summary = item.get("summary", item.get("description", ""))
            content = f"{title}. {summary}" if title and summary else title or summary

            if content.strip():
                texts.append(content)
                metadatas.append({
                    "date": item.get("date", ""),
                    "url": item.get("url", ""),
                    "ticker": item.get("ticker", ""),
                })
                sources.append(item.get("source", "news"))
                categories.append("news")

        return self.vector_store.add_documents(texts, metadatas, sources, categories)

    def ingest_research(self, research_items: List[Dict]) -> int:
        """
        Ingest research reports into the knowledge base.

        Args:
            research_items: List of dicts with 'title', 'content', 'category'

        Returns:
            Number of documents added
        """
        texts = []
        metadatas = []
        sources = []
        categories = []

        for item in research_items:
            content = item.get("content", item.get("summary", ""))
            if content.strip():
                texts.append(content)
                metadatas.append({
                    "title": item.get("title", ""),
                    "date": item.get("date", ""),
                })
                sources.append(item.get("source", "research"))
                categories.append(item.get("category", "research"))

        return self.vector_store.add_documents(texts, metadatas, sources, categories)

    def query(self, question: str, top_k: int = 5) -> RAGResult:
        """
        Query the RAG system.

        Args:
            question: Natural language question
            top_k: Number of documents to retrieve

        Returns:
            RAGResult with retrieved context and generated answer
        """
        retrieval = self.vector_store.search(question, top_k=top_k)

        # Generate answer if LLM available
        answer = ""
        confidence = 0.0
        if self.llm is not None and retrieval.context:
            try:
                prompt = (
                    f"Based on the following context, answer the question.\n\n"
                    f"Context:\n{retrieval.context}\n\n"
                    f"Question: {question}\n\n"
                    f"Answer:"
                )
                answer = self.llm.generate(prompt, max_tokens=500)
                confidence = float(np.mean(retrieval.similarity_scores)) if retrieval.similarity_scores else 0
            except Exception as e:
                logger.warning(f"LLM generation failed: {e}")
                answer = retrieval.context
                confidence = 0.5
        else:
            # Return context as answer
            answer = retrieval.context
            if retrieval.similarity_scores:
                confidence = float(np.mean(retrieval.similarity_scores))

        sources = [doc.source for doc in retrieval.retrieved_docs]

        return RAGResult(
            retrieval=retrieval,
            answer=answer,
            sources=sources,
            confidence=confidence,
        )

    def get_market_context(self, ticker: str, top_k: int = 5) -> Dict:
        """
        Get relevant market context for a ticker.

        Returns:
            Dict with retrieved context for analysis
        """
        query = f"latest news and analysis for {ticker}"
        result = self.query(query, top_k=top_k)

        return {
            "ticker": ticker,
            "context": result.answer,
            "sources": result.sources,
            "confidence": result.confidence,
            "n_docs": result.retrieval.n_docs_retrieved,
            "top_similarities": result.retrieval.similarity_scores[:3],
        }


# =============================================================================
# CONVENIENCE FUNCTIONS
# =============================================================================


def create_rag_system(embedding_method: str = "tfidf") -> RAGSystem:
    """Create a RAG system instance."""
    return RAGSystem(embedding_method=embedding_method)


def run_rag_query(question: str, top_k: int = 5) -> Dict:
    """
    Run a RAG query.

    Returns:
        Dict with answer, context, and sources
    """
    rag = create_rag_system()
    result = rag.query(question, top_k=top_k)

    return {
        "answer": result.answer,
        "confidence": result.confidence,
        "sources": result.sources,
        "n_docs_retrieved": result.retrieval.n_docs_retrieved,
        "context": result.retrieval.context[:1000] if result.retrieval.context else "",
    }
