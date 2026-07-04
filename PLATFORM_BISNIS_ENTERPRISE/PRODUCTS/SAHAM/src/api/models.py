"""
Pydantic models for FastAPI request/response schemas.
"""
from pydantic import BaseModel, Field
from typing import Dict, List, Optional, Any
from datetime import datetime


class PredictionResponse(BaseModel):
    ticker: str
    current_price: float
    predicted_price: float
    direction: str = Field(..., description="UP or DOWN")
    signal: str = Field(..., description="BUY, SELL, or HOLD")
    confidence: float
    model_votes: Dict[str, str]
    rules: str
    ai_score: Optional[int] = None
    composite_score: Optional[float] = None
    timestamp: str


class BacktestResponse(BaseModel):
    total_return_pct: float
    buy_hold_return_pct: float
    n_trades: int
    win_rate: float
    max_drawdown_pct: float
    sharpe_ratio: float
    sortino_ratio: float
    calmar_ratio: float
    profit_factor: float


class AccuracyResponse(BaseModel):
    total: int
    benar: int
    salah: int
    directional_accuracy: float
    mape: Optional[float] = None


class PatternResponse(BaseModel):
    candlestick_patterns: List[Dict[str, Any]]
    chart_patterns: List[Dict[str, Any]]
    market_structure: Dict[str, Any]
    volume_anomalies: List[Dict[str, Any]]
    trendlines: List[Dict[str, Any]]
    summary: str


class SentimentResponse(BaseModel):
    composite_score: float
    label: str
    emoji: Optional[str] = None
    advice: Optional[str] = None
    components: Dict[str, Any]
    news_sentiment: Optional[Dict[str, float]] = None


class BriefingResponse(BaseModel):
    date: str
    market_summary: str
    signal: str
    confidence: float
    final_recommendation: str
    actionable_items: List[str]
    risk_assessment: str
    bull_case: Optional[str] = None
    bear_case: Optional[str] = None
    debate: Optional[Dict[str, Any]] = None


class ScoreResponse(BaseModel):
    ai_score: int = Field(..., ge=1, le=10)
    composite_score: float
    technical_rating: float
    sentiment_rating: float
    momentum_rating: float
    risk_rating: float
    signal_strength: str


class HealthResponse(BaseModel):
    status: str
    version: str
    timestamp: str


class FullHealthResponse(BaseModel):
    status: str
    version: str
    timestamp: str
    database: str
    redis: str
    data_source: str
    data_validation: str


class AlertCreateRequest(BaseModel):
    ticker: str
    alert_type: str = Field(..., description="price_above, price_below, volume_spike, etc.")
    condition_value: Optional[float] = None
    condition_text: str = ""
    message: str = ""


class AlertResponse(BaseModel):
    id: int
    ticker: str
    alert_type: str
    condition_value: Optional[float] = None
    condition_text: str = ""
    is_active: int
    is_triggered: int
    triggered_at: Optional[str] = None
    message: str = ""
    created_at: Optional[str] = None


class SimOrderRequest(BaseModel):
    symbol: str
    side: str = "BUY"
    quantity: int = 100
