"""
Model Registry — lightweight versioning for ML models.

Provides:
- save_model: persist a model with metadata + hash + timestamp
- load_model: load a model by ticker/name or latest version
- list_models: list registered models with metadata
- get_latest_model: get the latest model path/metadata

Storage layout:
    src/models/registry/
        <ticker>/
            <model_name>_<timestamp>.pkl
            <model_name>_<timestamp>.json  (metadata)

All metadata is JSON so it is human-readable and diff-friendly.
"""

import hashlib
import json
import logging
import os
import pickle
from dataclasses import asdict, dataclass, field
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple

from .config import MODELS_DIR

logger = logging.getLogger(__name__)

REGISTRY_DIR = os.path.join(MODELS_DIR, "registry")
os.makedirs(REGISTRY_DIR, exist_ok=True)


@dataclass
class ModelMetadata:
    """Metadata describing a registered model artifact."""

    ticker: str
    model_name: str
    version: str
    created_at: str
    file_hash: str
    file_size: int
    metrics: Dict[str, Any] = field(default_factory=dict)
    tags: List[str] = field(default_factory=list)
    notes: str = ""

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "ModelMetadata":
        return cls(**data)


def _compute_hash(file_path: str) -> str:
    """Compute SHA-256 hash of a file."""
    h = hashlib.sha256()
    with open(file_path, "rb") as f:
        for chunk in iter(lambda: f.read(8192), b""):
            h.update(chunk)
    return h.hexdigest()


def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def _metadata_path(artifact_path: str) -> str:
    return artifact_path.replace(".pkl", ".json")


def save_model(
    model: Any,
    ticker: str,
    model_name: str,
    metrics: Optional[Dict[str, Any]] = None,
    tags: Optional[List[str]] = None,
    notes: str = "",
    version: Optional[str] = None,
) -> Tuple[str, ModelMetadata]:
    """
    Save a model artifact to the registry.

    Args:
        model: model object (must be picklable)
        ticker: e.g. "IHSG", "BBCA"
        model_name: e.g. "ensemble", "lgbm", "xgb"
        metrics: optional dict of metrics (accuracy, f1, etc)
        tags: optional list of tags
        notes: optional human notes
        version: optional explicit version; defaults to timestamp-based

    Returns:
        (artifact_path, metadata)
    """
    ticker_dir = os.path.join(REGISTRY_DIR, ticker)
    os.makedirs(ticker_dir, exist_ok=True)

    timestamp = datetime.now(timezone.utc).strftime("%Y%m%d_%H%M%S")
    version = version or f"v{timestamp}"
    filename = f"{model_name}_{version}"
    artifact_path = os.path.join(ticker_dir, f"{filename}.pkl")
    meta_path = _metadata_path(artifact_path)

    with open(artifact_path, "wb") as f:
        pickle.dump(model, f, protocol=pickle.HIGHEST_PROTOCOL)

    file_size = os.path.getsize(artifact_path)
    file_hash = _compute_hash(artifact_path)

    metadata = ModelMetadata(
        ticker=ticker,
        model_name=model_name,
        version=version,
        created_at=_now_iso(),
        file_hash=file_hash,
        file_size=file_size,
        metrics=metrics or {},
        tags=tags or [],
        notes=notes,
    )

    with open(meta_path, "w", encoding="utf-8") as f:
        json.dump(metadata.to_dict(), f, indent=2, default=str)

    logger.info(f"[MODEL REGISTRY] Saved {ticker}/{model_name} {version} -> {artifact_path}")
    return artifact_path, metadata


def list_models(ticker: Optional[str] = None) -> List[ModelMetadata]:
    """
    List all registered models, optionally filtered by ticker.
    """
    results: List[ModelMetadata] = []
    search_dir = REGISTRY_DIR if ticker is None else os.path.join(REGISTRY_DIR, ticker)

    if not os.path.exists(search_dir):
        return results

    for root, _dirs, files in os.walk(search_dir):
        for f in files:
            if f.endswith(".json"):
                path = os.path.join(root, f)
                try:
                    with open(path, "r", encoding="utf-8") as fh:
                        data = json.load(fh)
                    results.append(ModelMetadata.from_dict(data))
                except Exception as e:
                    logger.warning(f"[MODEL REGISTRY] Failed to read metadata {path}: {e}")

    return sorted(results, key=lambda m: m.created_at, reverse=True)


def get_latest_model(ticker: str, model_name: str) -> Optional[Tuple[str, ModelMetadata]]:
    """
    Get the latest artifact path and metadata for a ticker/model_name.
    """
    ticker_dir = os.path.join(REGISTRY_DIR, ticker)
    if not os.path.exists(ticker_dir):
        return None

    candidates: List[Tuple[str, ModelMetadata]] = []
    for f in os.listdir(ticker_dir):
        if f.startswith(f"{model_name}_") and f.endswith(".json"):
            meta_path = os.path.join(ticker_dir, f)
            try:
                with open(meta_path, "r", encoding="utf-8") as fh:
                    metadata = ModelMetadata.from_dict(json.load(fh))
                artifact_path = _metadata_path(meta_path).replace(".json", ".pkl")
                candidates.append((artifact_path, metadata))
            except Exception as e:
                logger.warning(f"[MODEL REGISTRY] Failed to read {meta_path}: {e}")

    if not candidates:
        return None

    candidates.sort(key=lambda x: x[1].created_at, reverse=True)
    return candidates[0]


def load_model(ticker: str, model_name: str, version: Optional[str] = None) -> Any:
    """
    Load a model from the registry.

    If version is None, load the latest version.
    """
    if version is None:
        latest = get_latest_model(ticker, model_name)
        if latest is None:
            raise FileNotFoundError(f"No registered model found for {ticker}/{model_name}")
        artifact_path, _metadata = latest
    else:
        ticker_dir = os.path.join(REGISTRY_DIR, ticker)
        artifact_path = os.path.join(ticker_dir, f"{model_name}_{version}.pkl")
        if not os.path.exists(artifact_path):
            raise FileNotFoundError(f"Model artifact not found: {artifact_path}")

    with open(artifact_path, "rb") as f:
        model = pickle.load(f)

    logger.info(f"[MODEL REGISTRY] Loaded {ticker}/{model_name} from {artifact_path}")
    return model


def verify_model(ticker: str, model_name: str, version: Optional[str] = None) -> bool:
    """
    Verify that a registered model artifact matches its recorded hash.
    """
    if version is None:
        latest = get_latest_model(ticker, model_name)
        if latest is None:
            return False
        artifact_path, metadata = latest
    else:
        ticker_dir = os.path.join(REGISTRY_DIR, ticker)
        artifact_path = os.path.join(ticker_dir, f"{model_name}_{version}.pkl")
        meta_path = _metadata_path(artifact_path)
        if not os.path.exists(meta_path):
            return False
        with open(meta_path, "r", encoding="utf-8") as f:
            metadata = ModelMetadata.from_dict(json.load(f))

    if not os.path.exists(artifact_path):
        return False

    actual_hash = _compute_hash(artifact_path)
    return actual_hash == metadata.file_hash
