"""
Modul Fear & Greed Index
Mengukur sentimen pasar menggunakan kombinasi indikator:
- VIX (Volatility)
- Market Momentum (MA cross)
- RSI (Price momentum)
- Put/Call ratio proxy (via volume analysis)
- Market Breadth (advance/decline)
- Safe Haven Demand (Gold vs Stock)
- Junk Bond Demand (High yield vs Treasury proxy)
"""

import pandas as pd
import numpy as np
from typing import Dict
from .config import TICKERS


def calc_fear_greed_index(market_data: Dict[str, pd.DataFrame]) -> dict:
    """
    Fear & Greed Index skala 0-100.
    0-25: Extreme Fear
    25-45: Fear
    45-55: Neutral
    55-75: Greed
    75-100: Extreme Greed
    """
    components = {}

    # 1. VIX Component (inverse - high VIX = fear)
    if "VIX" in market_data and not market_data["VIX"].empty:
        vix = market_data["VIX"]["Close"]
        vix_current = vix.iloc[-1]


        if vix_current <= 15:
            vix_score = 85
        elif vix_current <= 20:
            vix_score = 70
        elif vix_current <= 25:
            vix_score = 50
        elif vix_current <= 30:
            vix_score = 30
        elif vix_current <= 35:
            vix_score = 15
        else:
            vix_score = 5

        components["VIX"] = {
            "score": vix_score,
            "value": f"{vix_current:.1f}",
            "interpretation": "Low volatility = Greed" if vix_score > 50 else "High volatility = Fear",
        }

    # 2. Market Momentum (IHSG vs 125-day MA)
    if "IHSG" in market_data and not market_data["IHSG"].empty:
        ihsg = market_data["IHSG"]["Close"]
        current = ihsg.iloc[-1]
        ma_125 = ihsg.rolling(window=125).mean().iloc[-1]
        if pd.notna(ma_125) and ma_125 > 0:
            momentum_pct = ((current - ma_125) / ma_125) * 100
        else:
            momentum_pct = 0.0

        if momentum_pct > 10:
            mom_score = 90
        elif momentum_pct > 5:
            mom_score = 75
        elif momentum_pct > 0:
            mom_score = 60
        elif momentum_pct > -5:
            mom_score = 40
        elif momentum_pct > -10:
            mom_score = 25
        else:
            mom_score = 10

        components["Momentum"] = {
            "score": mom_score,
            "value": f"{momentum_pct:+.1f}% vs MA125",
            "interpretation": "Above MA = Greed" if mom_score > 50 else "Below MA = Fear",
        }

    # 3. RSI Component
    if "IHSG" in market_data and not market_data["IHSG"].empty:
        df_temp = market_data["IHSG"].copy()
        delta = df_temp["Close"].diff()
        gain = delta.where(delta > 0, 0.0)
        loss = -delta.where(delta < 0, 0.0)
        avg_gain = gain.rolling(14).mean()
        avg_loss = loss.rolling(14).mean()
        rs = avg_gain / avg_loss.replace(0, np.nan)
        rsi = 100 - (100 / (1 + rs))
        rsi_current = rsi.iloc[-1]
        if pd.isna(rsi_current):
            rsi_current = 50.0

        components["RSI"] = {
            "score": round(rsi_current),
            "value": f"{rsi_current:.1f}",
            "interpretation": "Overbought = Greed" if rsi_current > 70 else "Oversold = Fear" if rsi_current < 30 else "Neutral",
        }

    # 4. Safe Haven Demand (Gold performance vs IHSG)
    if "GOLD" in market_data and "IHSG" in market_data:
        gold_ret = market_data["GOLD"]["Close"].pct_change(periods=20).iloc[-1] * 100
        ihsg_ret = market_data["IHSG"]["Close"].pct_change(periods=20).iloc[-1] * 100
        spread = gold_ret - ihsg_ret

        if spread > 5:
            safe_score = 15  # Gold outperforming = Fear
        elif spread > 2:
            safe_score = 30
        elif spread > -2:
            safe_score = 50
        elif spread > -5:
            safe_score = 70
        else:
            safe_score = 85  # Stock outperforming = Greed

        components["Safe Haven"] = {
            "score": safe_score,
            "value": f"Gold {gold_ret:+.1f}% vs IHSG {ihsg_ret:+.1f}%",
            "interpretation": "Gold > Stock = Fear" if safe_score < 50 else "Stock > Gold = Greed",
        }

    # 5. Market Breadth (Advance/Decline proxy via volume)
    if "IHSG" in market_data and not market_data["IHSG"].empty:
        df = market_data["IHSG"]
        if "Volume" in df.columns:
            up_days = (df["Close"].diff().tail(20) > 0).sum()
            breadth_score = int((up_days / 20) * 100)

            components["Breadth"] = {
                "score": breadth_score,
                "value": f"{up_days}/20 hari naik",
                "interpretation": "More up days = Greed" if breadth_score > 55 else "More down days = Fear",
            }

    # 6. Volatility Trend (ATR-based)
    if "IHSG" in market_data and not market_data["IHSG"].empty:
        df = market_data["IHSG"]
        high_low = df["High"] - df["Low"]
        atr = high_low.rolling(14).mean()
        atr_pct = (atr / df["Close"]) * 100
        current_atr_pct = atr_pct.iloc[-1]
        avg_atr_pct = atr_pct.rolling(60).mean().iloc[-1]

        if pd.isna(current_atr_pct) or pd.isna(avg_atr_pct) or avg_atr_pct == 0:
            vol_score = 50
        elif current_atr_pct > avg_atr_pct * 1.5:
            vol_score = 20
        elif current_atr_pct > avg_atr_pct * 1.2:
            vol_score = 35
        elif current_atr_pct > avg_atr_pct * 0.8:
            vol_score = 55
        else:
            vol_score = 75

        components["Volatility Trend"] = {
            "score": vol_score,
            "value": f"ATR {current_atr_pct:.2f}% vs avg {avg_atr_pct:.2f}%",
            "interpretation": "Low ATR = Greed" if vol_score > 50 else "High ATR = Fear",
        }

    # 7. Commodity/Oil Sentiment
    if "OIL" in market_data and not market_data["OIL"].empty:
        oil_ret = market_data["OIL"]["Close"].pct_change(periods=20).iloc[-1] * 100
        if oil_ret > 10:
            oil_score = 75
        elif oil_ret > 0:
            oil_score = 60
        elif oil_ret > -10:
            oil_score = 40
        else:
            oil_score = 25

        components["Oil Sentiment"] = {
            "score": oil_score,
            "value": f"{oil_ret:+.1f}% (20d)",
            "interpretation": "Rising oil = Growth optimism" if oil_score > 50 else "Falling oil = Growth concern",
        }

    # Calculate composite
    if components:
        scores = [c["score"] for c in components.values()]
        composite = round(np.mean(scores))
    else:
        composite = 50

    # Interpretation
    if composite <= 25:
        label = "Extreme Fear"
        emoji = "😱"
        advice = "Pasar sangat takut. Bisa jadi peluang beli untuk value investor, tapi hati-hati falling knife."
    elif composite <= 45:
        label = "Fear"
        emoji = "😨"
        advice = "Pasar dalam ketakutan. Tunggu konfirmasi reversal sebelum masuk."
    elif composite <= 55:
        label = "Neutral"
        emoji = "😐"
        advice = "Pasar netral. Tidak ada sentimen ekstrem. Ikuti trend yang ada."
    elif composite <= 75:
        label = "Greed"
        emoji = "😊"
        advice = "Pasar serakah. Hati-hati, pertimbangkan take profit sebagian."
    else:
        label = "Extreme Greed"
        emoji = "🤑"
        advice = "Pasar sangat serakah. Berhati-hati, kemungkinan koreksi tinggi. Jangan FOMO."

    return {
        "composite_score": composite,
        "label": label,
        "emoji": emoji,
        "advice": advice,
        "components": components,
    }


def calc_market_regime(market_data: Dict[str, pd.DataFrame], ticker: str = "^JKSE") -> dict:
    """
    Market Regime Detection: Bull, Bear, atau Sideways
    Berdasarkan: MA alignment, ADX, volatility, breadth
    """
    target_name = next((name for name, t in TICKERS.items() if t == ticker), None)

    if target_name is None or target_name not in market_data:
        return {"regime": "Unknown", "confidence": 0}

    df = market_data[target_name]
    if len(df) < 100:
        return {"regime": "Unknown", "confidence": 0}

    close = df["Close"]

    # MA alignment
    ma20 = close.rolling(20).mean().iloc[-1]
    ma50 = close.rolling(50).mean().iloc[-1]
    ma100 = close.rolling(100).mean().iloc[-1]
    ma200 = close.rolling(200).mean().iloc[-1] if len(close) >= 200 else ma100

    current = close.iloc[-1]

    # ADX for trend strength
    from .indicators import calc_adx
    df_adx = calc_adx(df.copy())
    adx = df_adx["ADX"].iloc[-1]
    plus_di = df_adx["Plus_DI"].iloc[-1]
    minus_di = df_adx["Minus_DI"].iloc[-1]

    # Volatility
    returns = close.pct_change()
    vol_20 = returns.rolling(20).std().iloc[-1] * np.sqrt(252) * 100
    vol_60 = returns.rolling(60).std().iloc[-1] * np.sqrt(252) * 100 if len(returns) >= 60 else vol_20

    # Trend slope (linear regression of last 60 days)
    recent_close = close.tail(60).values
    x = np.arange(len(recent_close))
    slope = np.polyfit(x, recent_close, 1)[0]
    slope_pct = (slope / current) * 100

    # Scoring
    bull_score = 0
    bear_score = 0
    reasons = []

    # MA alignment
    if current > ma20 > ma50 > ma100:
        bull_score += 3
        reasons.append("MA: Perfect bullish alignment (Price > MA20 > MA50 > MA100)")
    elif current > ma50 > ma100:
        bull_score += 2
        reasons.append("MA: Bullish (Price > MA50 > MA100)")
    elif current < ma20 < ma50 < ma100:
        bear_score += 3
        reasons.append("MA: Perfect bearish alignment (Price < MA20 < MA50 < MA100)")
    elif current < ma50 < ma100:
        bear_score += 2
        reasons.append("MA: Bearish (Price < MA50 < MA100)")
    else:
        reasons.append("MA: Mixed alignment")

    # ADX
    if adx > 25:
        if plus_di > minus_di:
            bull_score += 2
            reasons.append(f"ADX: Strong uptrend (ADX={adx:.1f}, +DI > -DI)")
        else:
            bear_score += 2
            reasons.append(f"ADX: Strong downtrend (ADX={adx:.1f}, -DI > +DI)")
    else:
        reasons.append(f"ADX: Weak trend (ADX={adx:.1f} < 25)")

    # Slope
    if slope_pct > 0.05:
        bull_score += 1
        reasons.append(f"Slope: Positive trend ({slope_pct:+.2f}%/day)")
    elif slope_pct < -0.05:
        bear_score += 1
        reasons.append(f"Slope: Negative trend ({slope_pct:+.2f}%/day)")
    else:
        reasons.append(f"Slope: Flat ({slope_pct:+.2f}%/day)")

    # Volatility expansion/contraction
    if vol_20 > vol_60 * 1.3:
        if bear_score > bull_score:
            bear_score += 1
            reasons.append("Volatility: Expanding (panic selling)")
        else:
            reasons.append("Volatility: Expanding (possible breakout)")
    elif vol_20 < vol_60 * 0.7:
        reasons.append("Volatility: Contracting (consolidation)")

    # VIX check
    if "VIX" in market_data and not market_data["VIX"].empty:
        vix = market_data["VIX"]["Close"].iloc[-1]
        if vix > 30:
            bear_score += 2
            reasons.append(f"VIX: High ({vix:.1f} > 30) - Fear")
        elif vix < 15:
            bull_score += 1
            reasons.append(f"VIX: Low ({vix:.1f} < 15) - Complacency/Greed")

    # Decision
    if bull_score > bear_score + 2:
        regime = "BULL"
        confidence = min(100, int((bull_score / (bull_score + bear_score)) * 100))
        strategy = "Trend following: Beli di pullback, trail stop loss. Fokus saham beta tinggi."
    elif bear_score > bull_score + 2:
        regime = "BEAR"
        confidence = min(100, int((bear_score / (bull_score + bear_score)) * 100))
        strategy = "Defensive: Cash/obligasi, hedge, short opportunity. Hindari averaging down."
    else:
        regime = "SIDEWAYS"
        confidence = 100 - abs(bull_score - bear_score) * 20
        strategy = "Range trading: Beli di support, jual di resistance. Trailing stop ketat."

    return {
        "regime": regime,
        "confidence": max(0, confidence),
        "bull_score": bull_score,
        "bear_score": bear_score,
        "current_price": round(current, 2),
        "ma20": round(ma20, 2),
        "ma50": round(ma50, 2),
        "ma100": round(ma100, 2),
        "ma200": round(ma200, 2) if len(close) >= 200 else None,
        "adx": round(adx, 1),
        "plus_di": round(plus_di, 1),
        "minus_di": round(minus_di, 1),
        "volatility_annual": round(vol_20, 2),
        "slope_pct": round(slope_pct, 3),
        "reasons": reasons,
        "strategy": strategy,
    }
