"""
Local LLM Integration via Ollama — untuk reasoning berita lebih dalam & Q&A.

Fitur:
- Deep news reasoning: "BI naik rate 25bps → KPR naik → properti tertekan → sell properti"
- Trading thesis generation dari kombinasi semua data
- Q&A interface: "Kenapa BUY BBCA hari ini?"
- Fallback ke InAppCommentary jika Ollama tidak tersedia

Requirements:
- Install Ollama: https://ollama.ai
- Pull model: ollama pull llama3 (atau mistral, phi3, dll)
- Run: ollama serve

Usage:
    from src.local_llm import LocalLLM
    llm = LocalLLM()
    thesis = llm.generate_trading_thesis(ticker="BBCA.JK", data=context)
    answer = llm.ask("Kenapa BUY BBCA hari ini?")
"""

import json
import os
import requests
from typing import Optional, Dict, List
from dataclasses import dataclass


@dataclass
class LLMResponse:
    text: str
    model: str
    success: bool
    error: str = ""


class LocalLLM:
    """
    Local LLM via Ollama untuk reasoning mendalam.
    
    Ollama berjalan di localhost:11434, gratis, tanpa API key.
    Model direkomendasikan: phi3 (3.8B, ringan), llama3 (8B), mistral (7B), qwen2.5 (7B).
    
    Config via .env:
    - OLLAMA_HOST (default: http://localhost:11434)
    - OLLAMA_MODEL (default: phi3)
    """

    def __init__(self, model: str = None, host: str = None):
        self.model = model or os.getenv("OLLAMA_MODEL", "phi3")
        self.host = host or os.getenv("OLLAMA_HOST", "http://localhost:11434")
        self._available = None

    def is_available(self) -> bool:
        """Check if Ollama is running and model is available."""
        if self._available is not None:
            return self._available
        try:
            resp = requests.get(f"{self.host}/api/tags", timeout=5)
            if resp.status_code == 200:
                models = resp.json().get("models", [])
                model_names = [m.get("name", "") for m in models]
                # Check if any installed model matches
                self._available = any(self.model in name for name in model_names)
                if not self._available and model_names:
                    # Use first available model
                    self.model = model_names[0].split(":")[0]
                    self._available = True
            else:
                self._available = False
        except Exception:
            self._available = False
        return self._available

    def _call(self, prompt: str, system: str = "", temperature: float = 0.3) -> LLMResponse:
        """Call Ollama API."""
        if not self.is_available():
            return LLMResponse(text="", model=self.model, success=False, error="Ollama not available")
        try:
            full_prompt = f"{system}\n\n{prompt}" if system else prompt
            resp = requests.post(
                f"{self.host}/api/generate",
                json={
                    "model": self.model,
                    "prompt": full_prompt,
                    "stream": False,
                    "options": {"temperature": temperature},
                },
                timeout=60,
            )
            if resp.status_code == 200:
                text = resp.json().get("response", "").strip()
                return LLMResponse(text=text, model=self.model, success=True)
            return LLMResponse(text="", model=self.model, success=False, error=f"HTTP {resp.status_code}")
        except Exception as e:
            return LLMResponse(text="", model=self.model, success=False, error=str(e))

    # =========================================================================
    # DEEP NEWS REASONING
    # =========================================================================

    def reason_about_news(self, headline: str, summary: str, context: str = "") -> str:
        """
        Generate deep reasoning about a news article.
        
        Example: "BI naik rate 25bps" → "Rate hike → KPR naik → properti tertekan → 
        saham properti (LPKR, BSDE) akan turun. Tapi perbankan (BBCA, BBRI) 
        benefit dari margin expansion."
        """
        prompt = f"""Anda adalah analis pasar modal Indonesia yang berpengalaman.

Berita: {headline}
Ringkasan: {summary}
Konteks pasar: {context or 'Tidak ada konteks tambahan'}

Analisa dampak berita ini secara mendalam:
1. APA yang terjadi? (event type, severity)
2. SIAPA yang terdampak? (sektor, saham spesifik di BEI)
3. BAGAIMANA dampaknya? (mechanism: A → B → C → saham)
4. KAPALI dampaknya? (immediate, short, medium term)
5. APA yang harus dilakukan? (action per ticker)

Jawab dalam Bahasa Indonesia, singkat dan actionable."""

        resp = self._call(prompt, system="Anda adalah analis pasar modal Indonesia yang expert.")
        if resp.success:
            return resp.text
        # Fallback to InAppCommentary
        return self._fallback_reasoning(headline, summary)

    def _fallback_reasoning(self, headline: str, summary: str) -> str:
        """Fallback reasoning when LLM not available."""
        text = f"{headline}\n{summary}\n\n"
        text += "[Fallback analysis — Ollama tidak tersedia]\n"
        text += "Gunakan NewsUnderstandingEngine untuk analisis otomatis.\n"
        text += "Install Ollama untuk reasoning yang lebih mendalam: https://ollama.ai"
        return text

    # =========================================================================
    # TRADING THESIS GENERATION
    # =========================================================================

    def generate_trading_thesis(
        self,
        ticker: str,
        signal: str = "HOLD",
        confidence: float = 0.0,
        sentiment: float = 0.0,
        event_risk: float = 0.0,
        dcf: Optional[Dict] = None,
        technical: Optional[Dict] = None,
        news: Optional[List[str]] = None,
    ) -> str:
        """
        Generate comprehensive trading thesis untuk sebuah ticker.
        
        Menggabungkan: ML signal + sentiment + event risk + DCF + teknikal + berita
        → satu narasi yang menjelaskan KENAPA buy/sell/hold.
        """
        prompt = f"""Buat trading thesis untuk {ticker} di Bursa Efek Indonesia.

DATA:
- ML Signal: {signal} (confidence: {confidence:.1%})
- Market Sentiment: {sentiment:.0f}/100
- Event Risk: {event_risk:.0f}/100
- DCF Valuation: {json.dumps(dcf, indent=2) if dcf else 'Tidak ada'}
- Technical: {json.dumps(technical, indent=2) if technical else 'Tidak ada'}
- Berita terkait: {chr(10).join(f'- {n}' for n in (news or [])) if news else 'Tidak ada'}

Buat thesis dalam format:
1. REKOMENDASI: BUY/SELL/HOLD dengan alasan utama
2. THESIS: Narasi 2-3 kalimat menjelaskan kenapa
3. RISK: Apa yang bisa salah
4. CATALYST: Apa yang bisa memperkuat thesis
5. TARGET: Entry, stop loss, target harga

Jawab dalam Bahasa Indonesia, profesional dan concise."""

        resp = self._call(prompt, system="Anda adalah portfolio manager saham Indonesia.")
        if resp.success:
            return resp.text
        return f"[Fallback] Signal: {signal} ({confidence:.1%}) | Sentiment: {sentiment:.0f} | Event Risk: {event_risk:.0f}\nInstall Ollama untuk thesis yang lebih mendalam."

    # =========================================================================
    # Q&A INTERFACE
    # =========================================================================

    def ask(self, question: str, context: Optional[str] = None) -> str:
        """
        Q&A interface untuk pertanyaan tentang pasar, portfolio, atau saham.
        
        Example: "Kenapa BUY BBCA hari ini?"
        Example: "Apa dampak BI rate hike ke portfolio saya?"
        Example: "Saham apa yang bagus untuk dibeli sekarang?"
        """
        prompt = f"Pertanyaan: {question}"
        if context:
            prompt += f"\n\nKonteks: {context}"

        prompt += "\n\nJawab dalam Bahasa Indonesia, berdasarkan data yang diberikan. Jika tidak yakin, katakan tidak yakin."

        resp = self._call(prompt, system="Anda adalah asisten trading saham Indonesia yang helpful dan honest.")
        if resp.success:
            return resp.text
        return "[Fallback] Ollama tidak tersedia. Install dari https://ollama.ai untuk Q&A.\nUntuk analisis dasar, gunakan halaman Prediksi atau Pipeline."

    # =========================================================================
    # PORTFOLIO REVIEW
    # =========================================================================

    def review_portfolio(self, positions: List[Dict], market_context: str = "") -> str:
        """
        Generate portfolio review dengan LLM.
        
        Args:
            positions: List of {ticker, shares, entry_price, current_price, pnl}
            market_context: Additional market context string
        """
        pos_text = "\n".join(
            f"- {p['ticker']}: {p['shares']} shares, entry {p.get('entry_price', 0):,.0f}, "
            f"current {p.get('current_price', 0):,.0f}, PnL {p.get('pnl', 0):+,.0f}"
            for p in positions
        )

        prompt = f"""Review portfolio saham Indonesia berikut:

POSISI:
{pos_text}

KONTEKS PASAR:
{market_context or 'Tidak ada'}

Beri analisis:
1. KEKUATAN portfolio: Apa yang sudah bagus
2. KELEMAHAN: Apa yang perlu diperbaiki
3. REKOMENDASI: Action per posisi (hold/add/cut)
4. DIVERSIFIKASI: Apakah sektor sudah cukup diverse
5. RISK: VaR estimasi dan worst case scenario

Jawab dalam Bahasa Indonesia."""

        resp = self._call(prompt, system="Anda adalah portfolio advisor saham Indonesia.")
        if resp.success:
            return resp.text
        return "[Fallback] Install Ollama untuk portfolio review yang mendalam."
