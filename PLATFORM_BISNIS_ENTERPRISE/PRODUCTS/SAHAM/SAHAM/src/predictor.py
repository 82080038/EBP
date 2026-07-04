import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from typing import Tuple, Dict, Optional
from .config import (
    MODEL_CONFIG,
    BUSINESS_RULES,
    TARGET_TICKER,
    TICKERS,
)
from .lazy_imports import LazyAttr

# --- All heavy imports deferred to first call ---
prepare_features = LazyAttr("src.preprocessor", "prepare_features")
get_feature_columns = LazyAttr("src.preprocessor", "get_feature_columns")
train_test_split_time = LazyAttr("src.preprocessor", "train_test_split_time")
persist_technical_indicators = LazyAttr("src.preprocessor", "persist_technical_indicators")
simpan_prediksi = LazyAttr("src.database", "simpan_prediksi")
log_aktivitas = LazyAttr("src.database", "log_aktivitas")
calc_composite_ai_score = LazyAttr("src.scoring", "calc_composite_ai_score")
correlation_filter = LazyAttr("src.feature_selection", "correlation_filter")
variance_threshold_filter = LazyAttr("src.feature_selection", "variance_threshold_filter")
load_best_params = LazyAttr("src.hyperopt", "load_best_params")
apply_best_params = LazyAttr("src.hyperopt", "apply_best_params")

# --- Lazy imports (heavy modules loaded on first access) ---
HybridEnsemble = LazyAttr("src.models", "HybridEnsemble")
DriftMonitor = LazyAttr("src.drift_monitor", "DriftMonitor")
save_model = LazyAttr("src.model_registry", "save_model")
ModelMetadata = LazyAttr("src.model_registry", "ModelMetadata")
calc_entry_target_stop = LazyAttr("src.quant_finance", "calc_entry_target_stop")
calc_round_trip_cost = LazyAttr("src.quant_finance", "calc_round_trip_cost")
calc_bei_total_cost = LazyAttr("src.quant_finance", "calc_bei_total_cost")
detect_market_regime = LazyAttr("src.regime", "detect_market_regime")
RiskGovernance = LazyAttr("src.pro_risk", "RiskGovernance")
Order = LazyAttr("src.pro_risk", "Order")
detect_wyckoff_phase = LazyAttr("src.wyckoff", "detect_wyckoff_phase")
detect_elliott_wave = LazyAttr("src.elliott_wave", "detect_elliott_wave")
analyze_behavioral = LazyAttr("src.behavioral", "analyze_behavioral")
detect_economic_phase = LazyAttr("src.sector_rotation", "detect_economic_phase")
run_capm_regression = LazyAttr("src.factor_model", "run_capm_regression")
calc_factor_score = LazyAttr("src.factor_model", "calc_factor_score")
run_event_driven_analysis = LazyAttr("src.event_driven", "run_event_driven_analysis")
run_mtf_analysis = LazyAttr("src.mtf", "run_mtf_analysis")
get_mtf_confidence_adjustment = LazyAttr("src.mtf", "get_mtf_confidence_adjustment")
run_smc_analysis = LazyAttr("src.smc", "run_smc_analysis")
get_smc_confidence_adjustment = LazyAttr("src.smc", "get_smc_confidence_adjustment")
predict_drl_action = LazyAttr("src.drl_trading", "predict_drl_action")
get_drl_confidence_adjustment = LazyAttr("src.drl_trading", "get_drl_confidence_adjustment")
get_kronos_prediction = LazyAttr("src.kronos_integration", "get_kronos_prediction")
get_kronos_confidence_adjustment = LazyAttr("src.kronos_integration", "get_kronos_confidence_adjustment")
explain_prediction = LazyAttr("src.explainability", "explain_prediction")
generate_explanation_text = LazyAttr("src.explainability", "generate_explanation_text")


def _find_target_data(market_data: dict, target_ticker: str, target_name: str):
    """Find target DataFrame from market_data dict."""
    for name, ticker in TICKERS.items():
        if ticker == target_ticker:
            return market_data.get(name)
    for name, data in market_data.items():
        if target_ticker in name or name == target_name:
            return data
    return None


def _run_regime_and_adjust(df_clean: pd.DataFrame, target_name: str, sinyal: str):
    """Detect market regime and adjust signal accordingly."""
    regime = detect_market_regime(df_clean, close_col=f"{target_name}_Close")
    original_signal = sinyal
    if not regime.should_trade:
        sinyal = "HOLD"
    elif regime.current_regime == "bear" and sinyal == "BUY":
        sinyal = "HOLD"

    # In-app notification for regime alerts
    if regime.current_regime in ("crisis", "bear") or sinyal != original_signal:
        try:
            from .notifier import send_in_app
            level = "error" if regime.current_regime == "crisis" else "warning"
            judul = f"🎯 Regime: {regime.current_regime.upper()}"
            if sinyal != original_signal:
                judul += f" — Signal {original_signal}→{sinyal}"
            pesan = (
                f"Regime: {regime.current_regime} (score: {regime.regime_score})\n"
                f"Volatilitas: {regime.volatility_regime}\n"
                f"Trend strength (ADX): {regime.trend_strength}\n"
                f"Should trade: {regime.should_trade}\n"
                f"Position multiplier: {regime.position_size_multiplier}\n"
            )
            if regime.recommendations:
                pesan += "\nRekomendasi:\n" + "\n".join(f"• {r}" for r in regime.recommendations[:5])
            send_in_app(kategori="REGIME", judul=judul, pesan=pesan, level=level)
        except Exception:
            pass

    return regime, original_signal, sinyal


def _calc_entry_and_costs(sinyal, current_price, df_clean):
    """Calculate entry/target/stop levels and BEI transaction costs."""
    atr_col = "Target_ATR"
    atr_val = float(df_clean[atr_col].iloc[-1]) if atr_col in df_clean.columns else 0.0
    ets = calc_entry_target_stop(sinyal, current_price, atr=atr_val)

    trade_value = ets.position_size_shares * ets.entry if ets.position_size_shares > 0 else 0
    if trade_value > 0:
        round_trip = calc_round_trip_cost(trade_value)
        buy_cost = calc_bei_total_cost(trade_value, "buy")
        sell_cost = calc_bei_total_cost(trade_value, "sell")
        break_even_pct = round_trip["break_even_return_pct"]
        expected_return_pct = ((ets.target_1 - ets.entry) / ets.entry * 100) if ets.entry > 0 else 0
        net_expected_return = expected_return_pct - break_even_pct
        cost_feasible = net_expected_return > 0
    else:
        round_trip = buy_cost = sell_cost = None
        break_even_pct = expected_return_pct = net_expected_return = 0
        cost_feasible = False

    return ets, trade_value, round_trip, buy_cost, sell_cost, break_even_pct, expected_return_pct, net_expected_return, cost_feasible


def _run_advanced_analyses(market_data, fred_data, df_clean, target_data, target_ticker, target_name, sinyal, confidence):
    """Run all advanced analyses (Wyckoff, Elliott, Behavioral, Sector, Factor, Event, MTF)."""
    results = {}

    # Wyckoff
    results["wyckoff_result"] = None
    if target_data is not None and not target_data.empty:
        results["wyckoff_result"] = detect_wyckoff_phase(target_data)
        wy = results["wyckoff_result"]
        if wy.phase == "markdown" and wy.phase_score < -50 and sinyal == "BUY":
            sinyal = "HOLD"
        elif wy.phase == "distribution" and wy.phase_score < -40 and sinyal == "BUY":
            sinyal = "HOLD"
        elif wy.phase == "accumulation" and wy.spring_detected and sinyal == "HOLD":
            sinyal = "BUY"
    results["sinyal"] = sinyal

    # Elliott Wave
    results["elliott_result"] = None
    if target_data is not None and not target_data.empty:
        results["elliott_result"] = detect_elliott_wave(target_data)

    # Behavioral
    results["behavioral_result"] = None
    rsi_col = "Target_RSI" if "Target_RSI" in df_clean.columns else None
    if target_data is not None and not target_data.empty:
        results["behavioral_result"] = analyze_behavioral(target_data, rsi_col=rsi_col)
        br = results["behavioral_result"]
        if br.contrarian_signal == "buy" and sinyal == "HOLD":
            confidence = min(1.0, confidence + 0.05)
        elif br.contrarian_signal == "sell" and sinyal == "BUY":
            confidence = max(0.0, confidence - 0.05)
    results["confidence"] = confidence

    # Sector Rotation
    results["sector_result"] = None
    if market_data and fred_data:
        results["sector_result"] = detect_economic_phase(market_data, fred_data)

    # Factor Model (CAPM)
    results["factor_result"] = None
    results["factor_score_result"] = None
    try:
        target_returns = df_clean["Target_Returns"].dropna()
        market_proxy = None
        for mname in ["S&P500", "^GSPC"]:
            if mname in market_data:
                market_proxy = market_data[mname]
                break
        if market_proxy is not None and not market_proxy.empty:
            market_returns = market_proxy["Close"].pct_change().dropna()
            common = target_returns.index.intersection(market_returns.index)
            if len(common) > 30:
                results["factor_result"] = run_capm_regression(
                    target_returns.loc[common], market_returns.loc[common]
                )
                results["factor_score_result"] = calc_factor_score(results["factor_result"])
    except Exception:
        pass

    # Event-Driven
    try:
        event_result = run_event_driven_analysis(
            market_data, target_ticker=target_ticker,
            include_news=True, include_economic=True,
        )
        if event_result.event_risk_score > 60 and sinyal == "BUY":
            sinyal = "HOLD"
            results["sinyal"] = sinyal
        if event_result.news_sentiment and not event_result.news_sentiment.error:
            if event_result.news_sentiment.sentiment_score < -40 and sinyal == "BUY":
                confidence = max(0.0, confidence - 0.10)
                results["confidence"] = confidence
        results["event_result"] = event_result
    except Exception:
        results["event_result"] = None

    # Multi-Timeframe Analysis
    results["mtf_result"] = None
    results["mtf_adjustment"] = 1.0
    results["mtf_reason"] = ""
    if target_data is not None and not target_data.empty and len(target_data) >= 60:
        try:
            mtf_result = run_mtf_analysis(target_data)
            mtf_adjustment, mtf_reason = get_mtf_confidence_adjustment(mtf_result)
            original_confidence = confidence
            confidence = float(np.clip(confidence * mtf_adjustment, 0, 1))
            if mtf_result.confluence_strength == "Strong" and mtf_result.confluence_signal == "SELL":
                if sinyal == "BUY":
                    sinyal = "HOLD"
                    results["sinyal"] = sinyal
            elif mtf_result.confluence_strength == "Mixed":
                if sinyal != "HOLD":
                    sinyal = "HOLD"
                    results["sinyal"] = sinyal
            print(f"MTF: {mtf_result.summary}")
            print(f"MTF Confidence: {original_confidence:.2%} → {confidence:.2%} ({mtf_reason})")
            results["mtf_result"] = mtf_result
            results["mtf_adjustment"] = mtf_adjustment
            results["mtf_reason"] = mtf_reason
            results["confidence"] = confidence
        except Exception as e:
            print(f"MTF analysis skipped: {e}")

    # Smart Money Concepts (SMC / ICT)
    results["smc_result"] = None
    results["smc_adjustment"] = 0.0
    results["smc_reason"] = ""
    if target_data is not None and not target_data.empty and len(target_data) >= 60:
        try:
            smc_result = run_smc_analysis(target_data)
            smc_adj, smc_reason = get_smc_confidence_adjustment(smc_result)
            confidence = float(np.clip(confidence + smc_adj, 0, 1))
            if smc_result.signal == "SELL" and smc_result.confidence > 75 and sinyal == "BUY":
                sinyal = "HOLD"
                results["sinyal"] = sinyal
            elif smc_result.signal == "BUY" and smc_result.confidence > 75 and sinyal == "HOLD":
                sinyal = "BUY"
                results["sinyal"] = sinyal
            print(f"SMC: {smc_result.recommendation} (conf: {smc_result.confidence:.0f})")
            results["smc_result"] = smc_result
            results["smc_adjustment"] = smc_adj
            results["smc_reason"] = smc_reason
            results["confidence"] = confidence
        except Exception as e:
            print(f"SMC analysis skipped: {e}")

    # DRL Trading Agent
    results["drl_result"] = None
    results["drl_adjustment"] = 0.0
    results["drl_reason"] = ""
    if target_data is not None and not target_data.empty and len(target_data) >= 60:
        try:
            drl_pred = predict_drl_action(target_data)
            drl_adj, drl_reason = get_drl_confidence_adjustment(drl_pred)
            confidence = float(np.clip(confidence + drl_adj, 0, 1))
            print(f"DRL: {drl_pred.action_name} (conf: {drl_pred.confidence:.0%})")
            results["drl_result"] = drl_pred
            results["drl_adjustment"] = drl_adj
            results["drl_reason"] = drl_reason
            results["confidence"] = confidence
        except Exception as e:
            print(f"DRL prediction skipped: {e}")

    # --- Phase 2+ Advanced Analyses ---

    # Transformer Ensemble (PatchTST / TFT / LPatchTST)
    results["transformer_result"] = None
    if target_data is not None and not target_data.empty and len(target_data) >= 80:
        try:
            from .transformer_models import get_transformer_ensemble_prediction, get_transformer_confidence_adjustment, TransformerConfig
            config = TransformerConfig(seq_len=min(64, len(target_data) // 3), epochs=20)
            tf_result = get_transformer_ensemble_prediction(target_data, config)
            tf_adj, tf_reason = get_transformer_confidence_adjustment(tf_result)
            confidence = float(np.clip(confidence + tf_adj, 0, 1))
            results["transformer_result"] = tf_result
            results["transformer_adjustment"] = tf_adj
            results["transformer_reason"] = tf_reason
            results["confidence"] = confidence
            print(f"Transformer: {tf_reason}")
        except Exception as e:
            print(f"Transformer analysis skipped: {e}")

    # Regime-Aware Model
    results["regime_model_result"] = None
    if target_data is not None and not target_data.empty and len(target_data) >= 120:
        try:
            from .regime_models import run_regime_aware_prediction, get_regime_confidence_adjustment
            regime_result = run_regime_aware_prediction(target_data)
            reg_adj, reg_reason = get_regime_confidence_adjustment(regime_result)
            confidence = float(np.clip(confidence + reg_adj, 0, 1))
            results["regime_model_result"] = regime_result
            results["regime_adjustment"] = reg_adj
            results["regime_reason"] = reg_reason
            results["confidence"] = confidence
            print(f"Regime: {reg_reason}")
        except Exception as e:
            print(f"Regime model skipped: {e}")

    # Bull vs Bear Debate
    results["debate_result"] = None
    if target_data is not None and not target_data.empty and len(target_data) >= 50:
        try:
            from .bull_bear_debate import run_bull_bear_debate, get_debate_confidence_adjustment
            debate = run_bull_bear_debate(target_data, indicators=results.get("smc_result") and {"smc_signal": results["smc_result"].signal} or {})
            debate_adj, debate_reason = get_debate_confidence_adjustment(debate)
            confidence = float(np.clip(confidence + debate_adj, 0, 1))
            if debate.verdict == "SELL" and debate.confidence > 0.7 and sinyal == "BUY":
                sinyal = "HOLD"
                results["sinyal"] = sinyal
            elif debate.verdict == "BUY" and debate.confidence > 0.7 and sinyal == "HOLD":
                sinyal = "BUY"
                results["sinyal"] = sinyal
            results["debate_result"] = debate
            results["debate_adjustment"] = debate_adj
            results["debate_reason"] = debate_reason
            results["confidence"] = confidence
            print(f"Debate: {debate.verdict} ({debate.confidence:.0%})")
        except Exception as e:
            print(f"Bull-Bear debate skipped: {e}")

    # Multi-Horizon Prediction
    results["multi_horizon_result"] = None
    if target_data is not None and not target_data.empty and len(target_data) >= 80:
        try:
            from .multi_horizon import run_multi_horizon_prediction, get_multi_horizon_confidence_adjustment
            mh_result = run_multi_horizon_prediction(target_data, target_ticker)
            mh_adj, mh_reason = get_multi_horizon_confidence_adjustment(mh_result)
            confidence = float(np.clip(confidence + mh_adj, 0, 1))
            results["multi_horizon_result"] = mh_result
            results["multi_horizon_adjustment"] = mh_adj
            results["multi_horizon_reason"] = mh_reason
            results["confidence"] = confidence
            print(f"Multi-Horizon: {mh_reason}")
        except Exception as e:
            print(f"Multi-horizon prediction skipped: {e}")

    # ReAct Agent
    results["react_result"] = None
    if target_data is not None and not target_data.empty and len(target_data) >= 50:
        try:
            from .react_agent import run_react_agent, get_react_confidence_adjustment
            react_result = run_react_agent(target_data, target_ticker)
            react_adj, react_reason = get_react_confidence_adjustment(react_result)
            confidence = float(np.clip(confidence + react_adj, 0, 1))
            results["react_result"] = react_result
            results["react_adjustment"] = react_adj
            results["react_reason"] = react_reason
            results["confidence"] = confidence
            print(f"ReAct: {react_result.get('recommendation', 'N/A')} ({react_result.get('confidence', 0):.0%})")
        except Exception as e:
            print(f"ReAct agent skipped: {e}")

    # Social Sentiment
    results["social_sentiment_result"] = None
    try:
        from .social_sentiment import run_social_sentiment, get_social_sentiment_adjustment
        ss_result = run_social_sentiment(target_ticker, use_mock=True)
        ss_adj, ss_reason = get_social_sentiment_adjustment(
            type("SS", (), {"overall_sentiment": ss_result.get("overall_sentiment", 0),
                            "n_posts": ss_result.get("n_posts", 0),
                            "trending": ss_result.get("trending", False),
                            "error": ss_result.get("error", "")})()
        )
        confidence = float(np.clip(confidence + ss_adj, 0, 1))
        results["social_sentiment_result"] = ss_result
        results["social_sentiment_adjustment"] = ss_adj
        results["social_sentiment_reason"] = ss_reason
        results["confidence"] = confidence
        print(f"Social Sentiment: {ss_reason}")
    except Exception as e:
        print(f"Social sentiment skipped: {e}")

    # Kronos Foundation Model
    results["kronos_result"] = None
    results["kronos_adjustment"] = 0.0
    results["kronos_reason"] = ""
    if target_data is not None and not target_data.empty and len(target_data) >= 30:
        try:
            kronos_result = get_kronos_prediction(target_data, forecast_steps=5)
            kronos_adj, kronos_reason = get_kronos_confidence_adjustment(kronos_result)
            confidence = float(np.clip(confidence + kronos_adj, 0, 1))
            results["kronos_result"] = kronos_result
            results["kronos_adjustment"] = kronos_adj
            results["kronos_reason"] = kronos_reason
            results["confidence"] = confidence
            print(f"Kronos: {kronos_result.summary()}")
        except Exception as e:
            print(f"Kronos skipped: {e}")

    return results


def apply_business_rules(
    df: pd.DataFrame,
    predictions: dict,
    probabilities: dict,
    target_name: str = "IHSG",
) -> Tuple[str, float, str]:
    prefix = f"{target_name}_"
    rsi_col = "Target_RSI"

    latest = df.iloc[-1]

    rsi = latest.get(rsi_col, 50)
    ma5 = latest.get(f"{prefix}MA{MODEL_CONFIG['ma_short']}", 0)
    ma10 = latest.get(f"{prefix}MA{MODEL_CONFIG['ma_medium']}", 0)
    ma20 = latest.get(f"{prefix}MA{MODEL_CONFIG['ma_long']}", 0)

    vix_name = "VIX"
    vix_prefix = f"{vix_name}_"
    vix_close = latest.get(f"{vix_prefix}Close", 0)

    votes_buy = sum(1 for p in predictions.values() if p == 1)
    votes_sell = sum(1 for p in predictions.values() if p == 0)

    avg_confidence = np.mean(list(probabilities.values())) if probabilities else 0.5

    rules_triggered = []

    # Rule 1: Trend Follower (MA5 > MA10 > MA20 = Bullish)
    trend_bullish = False
    trend_bearish = False
    if BUSINESS_RULES["trend_follower_enabled"]:
        if ma5 > ma10 > ma20:
            trend_bullish = True
            rules_triggered.append("Trend Bullish (MA5>MA10>MA20)")
        elif ma5 < ma10 < ma20:
            trend_bearish = True
            rules_triggered.append("Trend Bearish (MA5<MA10<MA20)")

    # Rule 2: Anti-FOMO (RSI > 70 = overbought, jangan beli)
    overbought = rsi > BUSINESS_RULES["anti_fomo_rsi_threshold"]
    if overbought:
        rules_triggered.append(f"Anti-FOMO: RSI={rsi:.1f} > {BUSINESS_RULES['anti_fomo_rsi_threshold']}")

    # Rule 3: VIX Panic (VIX > 30 = pasar panik)
    vix_panic = vix_close > BUSINESS_RULES["vix_panic_threshold"]
    if vix_panic:
        rules_triggered.append(f"VIX Panic: VIX={vix_close:.1f} > {BUSINESS_RULES['vix_panic_threshold']}")

    # Rule 4: Oversold (RSI < 30 = potential buy)
    oversold = rsi < 30
    if oversold:
        rules_triggered.append(f"Oversold: RSI={rsi:.1f} < 30")

    # === DECISION LOGIC ===
    sinyal = "HOLD"
    confidence = avg_confidence

    if votes_buy > votes_sell and votes_buy >= BUSINESS_RULES["min_votes"]:
        sinyal = "BUY"
        if overbought:
            sinyal = "HOLD"
            rules_triggered.append("Sinyal BUY diturunkan ke HOLD karena overbought")
        if vix_panic:
            sinyal = "HOLD"
            rules_triggered.append("Sinyal BUY diturunkan ke HOLD karena VIX panic")
    elif votes_sell > votes_buy and votes_sell >= BUSINESS_RULES["min_votes"]:
        sinyal = "SELL"
        if oversold and trend_bullish:
            sinyal = "HOLD"
            rules_triggered.append("Sinyal SELL diturunkan ke HOLD karena oversold + trend bullish")

    if trend_bullish and sinyal == "HOLD" and votes_buy >= 1:
        if not overbought and not vix_panic:
            sinyal = "BUY"
            rules_triggered.append("Upgrade ke BUY: Trend bullish + tidak overbought")

    if trend_bearish and sinyal == "HOLD" and votes_sell >= 1:
        sinyal = "SELL"
        rules_triggered.append("Upgrade ke SELL: Trend bearish")

    if confidence < BUSINESS_RULES["min_confidence"]:
        sinyal = "HOLD"
        rules_triggered.append(
            f"Confidence {confidence:.2f} < minimum {BUSINESS_RULES['min_confidence']}"
        )

    rules_text = " | ".join(rules_triggered) if rules_triggered else "Tidak ada rule khusus ter-trigger"

    return sinyal, confidence, rules_text


def _calc_multi_day_forecast(
    predicted_return: float, current_price: float, confidence: float,
    df_clean: pd.DataFrame, target_name: str,
) -> Dict:
    """
    Calculate multi-day price forecasts (3d, 5d, 10d, 20d).

    Uses historical volatility and predicted direction to project
    cumulative price paths with confidence-weighted adjustments.
    """
    horizons = [3, 5, 10, 20]
    forecast = {}

    # Historical daily return stats
    close_col = f"{target_name}_Close" if f"{target_name}_Close" in df_clean.columns else "Close"
    if close_col not in df_clean.columns and "Close" in df_clean.columns:
        close_col = "Close"

    if close_col in df_clean.columns and len(df_clean) > 20:
        daily_returns = df_clean[close_col].pct_change().dropna()
        daily_returns.mean()
        daily_vol = daily_returns.std()
    else:
        daily_vol = 0.01

    for h in horizons:
        # Project cumulative return: compound daily predicted return
        # Scale by confidence (lower confidence = more conservative projection)
        daily_pred = predicted_return / 1  # daily predicted return
        cum_return = daily_pred * h * (0.5 + 0.5 * confidence)

        # Add volatility-based confidence interval
        vol_h = daily_vol * np.sqrt(h)
        upper = cum_return + 1.0 * vol_h
        lower = cum_return - 1.0 * vol_h

        forecast[f"{h}d"] = {
            "horizon_days": h,
            "projected_price": round(current_price * (1 + cum_return), 2),
            "projected_return_pct": round(cum_return * 100, 2),
            "upper_bound": round(current_price * (1 + upper), 2),
            "lower_bound": round(current_price * (1 + lower), 2),
            "confidence": round(confidence * 100, 1),
            "volatility_band_pct": round(vol_h * 100, 2),
        }

    return forecast


def run_prediction(
    market_data: dict,
    fred_data: Optional[dict] = None,
    target_ticker: str = TARGET_TICKER,
    ensemble: Optional[HybridEnsemble] = None,
) -> dict:
    print("\n" + "=" * 60)
    print("MENJALANKAN PREDIKSI")
    print("=" * 60)

    df = prepare_features(market_data, fred_data, target_ticker)
    if df.empty:
        return {"error": "Data kosong setelah preprocessing"}

    try:
        persist_technical_indicators(target_ticker, df, prefix="Target_")
    except Exception as e:
        print(f"[WARNING] Gagal persist technical indicators: {e}")

    target_name = None
    for name, ticker in TICKERS.items():
        if ticker == target_ticker:
            target_name = name
            break
    if target_name is None:
        target_name = "TARGET"

    feature_cols = get_feature_columns(df)
    df_clean = df.dropna(subset=feature_cols + ["Target_Next_Return"])

    if len(df_clean) < 100:
        return {"error": f"Data terlalu sedikit: {len(df_clean)} baris (minimum 100)"}

    df_clean["Target_Direction"] = (df_clean["Target_Next_Return"] > 0).astype(int)

    # Apply saved hyperopt params if available
    best_params = load_best_params()
    if best_params and "best_params" in best_params:
        apply_best_params(best_params["best_params"])

    # Feature selection: remove low-variance and highly-correlated features
    kept_after_var, dropped_var = variance_threshold_filter(df_clean, feature_cols)
    feature_cols, dropped_corr = correlation_filter(df_clean, kept_after_var, threshold=0.95)
    if dropped_var or dropped_corr:
        total_dropped = len(dropped_var) + len(dropped_corr)
        print(f"  Feature selection: {len(feature_cols)} kept, {total_dropped} removed")

    X = df_clean[feature_cols]
    df_clean["Target_Direction"]

    train, test = train_test_split_time(df_clean, MODEL_CONFIG["test_size"])
    X_train = train[feature_cols]
    y_train = train["Target_Direction"]

    if ensemble is None:
        ensemble = HybridEnsemble()

    if not ensemble.trained:
        ensemble.train(X_train, y_train)

    latest_features = X.iloc[-1:]
    predictions, probabilities = ensemble.predict_ensemble(latest_features)

    # === SHAP Explainability ===
    shap_explanations = {}
    try:
        shap_explanations = explain_prediction(ensemble, latest_features, feature_cols, top_k=10)
        shap_text = generate_explanation_text(shap_explanations)
        print(f"[SHAP] Explanation:\n{shap_text}")
    except Exception as e:
        print(f"[WARNING] SHAP explanation skipped: {e}")

    target_close_col = f"{target_name}_Close"
    if target_close_col not in df.columns:
        for col in df.columns:
            if col.endswith("_Close") and target_name in col:
                target_close_col = col
                break

    current_price = float(df[target_close_col].iloc[-1])

    # Determine direction from model votes (not from actual data)
    votes_buy = sum(1 for p in predictions.values() if p == 1)
    votes_sell = sum(1 for p in predictions.values() if p == 0)
    arah_prediksi = "UP" if votes_buy > votes_sell else "DOWN"

    # Estimate predicted return using probability-weighted historical returns
    target_returns = df_clean["Target_Returns"].dropna()
    pos_returns = target_returns[target_returns > 0]
    neg_returns = target_returns[target_returns < 0]
    avg_pos = pos_returns.mean() if len(pos_returns) > 0 else 0.0
    avg_neg = neg_returns.mean() if len(neg_returns) > 0 else 0.0

    # Weight by model consensus strength
    consensus_strength = abs(votes_buy - votes_sell) / max(len(predictions), 1)
    avg_proba = np.mean(list(probabilities.values())) if probabilities else 0.5

    if arah_prediksi == "UP":
        base_move = avg_pos
        predicted_return = base_move * (0.5 + 0.5 * consensus_strength) * (0.5 + 0.5 * avg_proba)
    else:
        base_move = avg_neg
        predicted_return = base_move * (0.5 + 0.5 * consensus_strength) * (1.5 - 0.5 * avg_proba)

    if np.isnan(predicted_return):
        predicted_return = 0.0
    predicted_price = current_price * (1 + predicted_return)

    sinyal, confidence, rules_text = apply_business_rules(
        df_clean, predictions, probabilities, target_name
    )

    today = datetime.now().strftime("%Y-%m-%d")
    tomorrow = (datetime.now() + timedelta(days=1)).strftime("%Y-%m-%d")

    votes_str = ", ".join([f"{k}: {'BUY' if v == 1 else 'SELL'}" for k, v in predictions.items()])
    proba_str = ", ".join([f"{k}: {v:.2%}" for k, v in probabilities.items()])

    print(f"\n{'=' * 60}")
    print(f"HASIL PREDIKSI UNTUK {target_ticker}")
    print(f"{'=' * 60}")
    print(f"Harga Saat Ini: {current_price:,.2f}")
    print(f"Harga Prediksi: {predicted_price:,.2f}")
    print(f"Arah Prediksi: {arah_prediksi}")
    print(f"Sinyal: {sinyal}")
    print(f"Confidence: {confidence:.2%}")
    print(f"Model Votes: {votes_str}")
    print(f"Probabilities: {proba_str}")
    print(f"Business Rules: {rules_text}")
    print(f"{'=' * 60}")

    simpan_prediksi(
        ticker=target_ticker,
        tanggal_prediksi=today,
        tanggal_target=tomorrow,
        harga_prediksi=predicted_price,
        arah_prediksi=arah_prediksi,
        sinyal=sinyal,
        confidence=confidence,
        model_votes=f"{votes_str} | {proba_str}",
        harga_saat_ini=current_price,
    )

    log_aktivitas(
        "PREDIKSI",
        f"{target_ticker}: Sinyal={sinyal}, Confidence={confidence:.2%}, Rules={rules_text}",
    )

    # === AI Composite Score ===
    ai_score_result = calc_composite_ai_score(predictions, probabilities, confidence)

    # === Market Regime Detection ===
    regime, original_signal, sinyal = _run_regime_and_adjust(df_clean, target_name, sinyal)

    # === Entry/Target/Stop Levels + Transaction Costs ===
    ets, trade_value, round_trip, buy_cost, sell_cost, break_even_pct, expected_return_pct, net_expected_return, cost_feasible = _calc_entry_and_costs(sinyal, current_price, df_clean)

    # === Find target data for advanced analyses ===
    target_data = _find_target_data(market_data, target_ticker, target_name)

    # === Run all advanced analyses ===
    adv = _run_advanced_analyses(market_data, fred_data, df_clean, target_data, target_ticker, target_name, sinyal, confidence)
    sinyal = adv["sinyal"]
    confidence = adv["confidence"]
    wyckoff_result = adv["wyckoff_result"]
    elliott_result = adv["elliott_result"]
    behavioral_result = adv["behavioral_result"]
    sector_result = adv["sector_result"]
    factor_result = adv["factor_result"]
    factor_score_result = adv["factor_score_result"]
    event_result = adv["event_result"]
    mtf_result = adv["mtf_result"]
    mtf_adjustment = adv["mtf_adjustment"]
    mtf_reason = adv["mtf_reason"]
    smc_result = adv["smc_result"]
    smc_adjustment = adv["smc_adjustment"]
    smc_reason = adv["smc_reason"]
    drl_result = adv["drl_result"]
    drl_adjustment = adv["drl_adjustment"]
    drl_reason = adv["drl_reason"]

    # === Professional Risk Governance ===
    risk_gov = RiskGovernance()
    if sinyal != "HOLD" and ets.position_size_shares > 0:
        order = Order(
            symbol=target_ticker,
            side="BUY" if sinyal == "BUY" else "SELL",
            quantity=ets.position_size_shares,
        )
        risk_eval = risk_gov.evaluate_trade(
            order, current_price=ets.entry,
            signal=sinyal, confidence=confidence,
            ai_score=ai_score_result["ai_score"],
            regime=regime.current_regime,
        )
        if not risk_eval["approved"]:
            sinyal = "HOLD"
    else:
        risk_eval = {
            "approved": False,
            "reason": "No trade signal or zero position",
            "final_position_size_multiplier": 0,
        }

    result = {
        "ticker": target_ticker,
        "current_price": current_price,
        "predicted_price": predicted_price,
        "arah_prediksi": arah_prediksi,
        "sinyal": sinyal,
        "confidence": confidence,
        "predictions": predictions,
        "probabilities": probabilities,
        "rules": rules_text,
        "ai_score": ai_score_result["ai_score"],
        "composite_score": ai_score_result["composite_score"],
        "signal_strength": ai_score_result["signal_strength"],
        "entry": ets.entry,
        "stop_loss": ets.stop_loss,
        "target_1": ets.target_1,
        "target_2": ets.target_2,
        "target_3": ets.target_3,
        "risk_reward": ets.risk_reward_ratio,
        "position_shares": ets.position_size_shares,
        "trade_value": round(trade_value, 0),
        "buy_cost": buy_cost,
        "sell_cost": sell_cost,
        "round_trip_cost": round_trip,
        "break_even_pct": break_even_pct,
        "expected_return_pct": round(expected_return_pct, 4),
        "net_expected_return_pct": round(net_expected_return, 4),
        "cost_feasible": cost_feasible,
        "cost_warning": "Profit tidak cukup untuk menutup biaya transaksi" if not cost_feasible and trade_value > 0 else "",
        "market_regime": regime.current_regime,
        "regime_score": regime.regime_score,
        "regime_adjusted_signal": sinyal if sinyal != original_signal else None,
        "regime_ma_alignment": regime.ma_alignment,
        "regime_volatility": regime.volatility_regime,
        "regime_trend_strength": regime.trend_strength,
        "regime_drawdown": regime.drawdown_status,
        "regime_momentum": regime.momentum,
        "regime_should_trade": regime.should_trade,
        "position_size_multiplier": regime.position_size_multiplier,
        "regime_recommendations": regime.recommendations,
        "risk_approved": risk_eval["approved"],
        "risk_reason": risk_eval.get("reason", ""),
        "risk_position_multiplier": risk_eval.get("final_position_size_multiplier", 0),
        "risk_adjusted_quantity": risk_eval.get("adjusted_quantity", 0),
        "kill_switch_active": risk_eval.get("kill_switch", {}).get("trading_enabled", True) is False,
        "drawdown_control": risk_eval.get("drawdown_control", {}),
        "volatility_targeting": risk_eval.get("volatility_targeting", {}),
        # === Wyckoff Method ===
        "wyckoff_phase": wyckoff_result.phase if wyckoff_result else "N/A",
        "wyckoff_score": wyckoff_result.phase_score if wyckoff_result else 0,
        "wyckoff_spring": wyckoff_result.spring_detected if wyckoff_result else False,
        "wyckoff_utad": wyckoff_result.utad_detected if wyckoff_result else False,
        "wyckoff_sos": wyckoff_result.sos_detected if wyckoff_result else False,
        "wyckoff_sow": wyckoff_result.sow_detected if wyckoff_result else False,
        "wyckoff_cause": wyckoff_result.cause_built if wyckoff_result else 0,
        "wyckoff_recommendation": wyckoff_result.recommendation if wyckoff_result else "",
        # === Elliott Wave ===
        "elliott_pattern": elliott_result.pattern_type if elliott_result else "N/A",
        "elliott_current_wave": elliott_result.current_wave if elliott_result else "unknown",
        "elliott_confidence": elliott_result.confidence if elliott_result else 0,
        "elliott_rules_valid": elliott_result.rules_valid if elliott_result else False,
        "elliott_fib_ratios": elliott_result.fib_ratios if elliott_result else {},
        "elliott_next_target": elliott_result.next_target if elliott_result else None,
        "elliott_invalidation": elliott_result.invalidation_level if elliott_result else None,
        "elliott_recommendation": elliott_result.recommendation if elliott_result else "",
        # === Behavioral Finance ===
        "behavioral_sentiment": behavioral_result.overall_sentiment if behavioral_result else "N/A",
        "behavioral_score": behavioral_result.sentiment_score if behavioral_result else 0,
        "behavioral_contrarian": behavioral_result.contrarian_signal if behavioral_result else "neutral",
        "behavioral_fomo": behavioral_result.fomo_score if behavioral_result else 0,
        "behavioral_panic": behavioral_result.panic_score if behavioral_result else 0,
        "behavioral_herding": behavioral_result.herding_score if behavioral_result else 0,
        "behavioral_recommendations": behavioral_result.recommendations if behavioral_result else [],
        # === Sector Rotation ===
        "sector_phase": sector_result.current_phase if sector_result else "N/A",
        "sector_confidence": sector_result.phase_confidence if sector_result else 0,
        "sector_favored": sector_result.favored_sectors if sector_result else [],
        "sector_avoided": sector_result.avoided_sectors if sector_result else [],
        "sector_rotation_signal": sector_result.rotation_signal if sector_result else "",
        "sector_recommendations": sector_result.recommendations if sector_result else [],
        # === Factor Model ===
        "factor_alpha": factor_result.alpha if factor_result else 0,
        "factor_beta": factor_result.beta_market if factor_result else 0,
        "factor_r_squared": factor_result.r_squared if factor_result else 0,
        "factor_score": factor_score_result["score"] if factor_score_result else 0,
        "factor_rating": factor_score_result["rating"] if factor_score_result else "N/A",
        "factor_interpretation": factor_result.interpretation if factor_result else "",
        # === Event-Driven ===
        "event_risk_score": event_result.event_risk_score if event_result else 0,
        "event_upcoming_count": event_result.upcoming_events_count if event_result else 0,
        "event_summary": event_result.summary if event_result else "",
        "event_earnings_count": len(event_result.earnings_events) if event_result else 0,
        "event_actions_count": len(event_result.corporate_actions) if event_result else 0,
        "event_analyst_count": len(event_result.analyst_recs) if event_result else 0,
        "event_economic_count": len(event_result.economic_events) if event_result else 0,
        "event_news_score": event_result.news_sentiment.sentiment_score if event_result and event_result.news_sentiment and not event_result.news_sentiment.error else None,
        "event_news_fear_greed": event_result.news_sentiment.fear_greed if event_result and event_result.news_sentiment and not event_result.news_sentiment.error else None,
        "event_news_articles": event_result.news_sentiment.total_articles if event_result and event_result.news_sentiment and not event_result.news_sentiment.error else 0,
        "event_foreign_flow": event_result.foreign_flow_proxy if event_result else {},
        "event_recommendations": event_result.recommendations if event_result else [],
        # === Multi-Timeframe Analysis ===
        "mtf_confluence_score": mtf_result.confluence_score if mtf_result else 50.0,
        "mtf_confluence_signal": mtf_result.confluence_signal if mtf_result else "HOLD",
        "mtf_confluence_strength": mtf_result.confluence_strength if mtf_result else "N/A",
        "mtf_trend_hierarchy": mtf_result.trend_hierarchy if mtf_result else "Mixed",
        "mtf_higher_tf_trend": mtf_result.higher_tf_trend if mtf_result else "Sideways",
        "mtf_entry_timing": mtf_result.entry_timing if mtf_result else "OK",
        "mtf_aligned_bullish": mtf_result.aligned_bullish if mtf_result else 0,
        "mtf_aligned_bearish": mtf_result.aligned_bearish if mtf_result else 0,
        "mtf_total_timeframes": mtf_result.total_timeframes if mtf_result else 0,
        "mtf_confidence_adjustment": mtf_adjustment,
        "mtf_reason": mtf_reason,
        "mtf_summary": mtf_result.summary if mtf_result else "",
        "mtf_timeframe_signals": [
            {
                "timeframe": sig.timeframe,
                "signal": sig.signal,
                "score": sig.score,
                "trend": sig.trend,
                "rsi": sig.rsi,
                "macd": sig.macd_signal,
                "ma_alignment": sig.ma_alignment,
                "detail": sig.detail,
            }
            for sig in (mtf_result.timeframe_signals if mtf_result else [])
        ],
        # === Smart Money Concepts (SMC / ICT) ===
        "smc_signal": smc_result.signal if smc_result else "HOLD",
        "smc_confidence": smc_result.confidence if smc_result else 50.0,
        "smc_market_structure": smc_result.market_structure if smc_result else "unclear",
        "smc_premium_discount": smc_result.premium_discount if smc_result else "equilibrium",
        "smc_equilibrium": smc_result.equilibrium if smc_result else 0,
        "smc_swing_high": smc_result.swing_high if smc_result else 0,
        "smc_swing_low": smc_result.swing_low if smc_result else 0,
        "smc_po3_phase": smc_result.po3_phase if smc_result else "",
        "smc_order_blocks": len(smc_result.order_blocks) if smc_result else 0,
        "smc_fvgs": len(smc_result.fair_value_gaps) if smc_result else 0,
        "smc_liquidity_sweeps": len(smc_result.liquidity_sweeps) if smc_result else 0,
        "smc_structure_breaks": len(smc_result.structure_breaks) if smc_result else 0,
        "smc_confidence_adjustment": smc_adjustment,
        "smc_reason": smc_reason,
        "smc_recommendation": smc_result.recommendation if smc_result else "",
        # === DRL Trading Agent ===
        "drl_action": drl_result.action_name if drl_result else "HOLD",
        "drl_confidence": drl_result.confidence if drl_result else 0.5,
        "drl_position_rec": drl_result.position_recommendation if drl_result else 0.0,
        "drl_confidence_adjustment": drl_adjustment,
        "drl_reason": drl_reason,
        # === Phase 2+: Transformer Ensemble ===
        "transformer_ensemble_pred": adv.get("transformer_result", {}).get("ensemble_prediction", 0) if adv.get("transformer_result") else 0,
        "transformer_confidence": adv.get("transformer_result", {}).get("confidence", 0) if adv.get("transformer_result") else 0,
        "transformer_n_models": adv.get("transformer_result", {}).get("n_models_trained", 0) if adv.get("transformer_result") else 0,
        "transformer_adjustment": adv.get("transformer_adjustment", 0),
        "transformer_reason": adv.get("transformer_reason", ""),
        # === Phase 2+: Regime-Aware Model ===
        "regime_model_signal": adv.get("regime_model_result", {}).get("signal", "HOLD") if adv.get("regime_model_result") else "HOLD",
        "regime_model_confidence": adv.get("regime_model_result", {}).get("confidence", 0) if adv.get("regime_model_result") else 0,
        "regime_model_current": adv.get("regime_model_result", {}).get("current_regime", "") if adv.get("regime_model_result") else "",
        "regime_model_adjustment": adv.get("regime_adjustment", 0),
        "regime_model_reason": adv.get("regime_reason", ""),
        # === Phase 2+: Bull vs Bear Debate ===
        "debate_verdict": adv.get("debate_result").verdict if adv.get("debate_result") else "HOLD",
        "debate_confidence": adv.get("debate_result").confidence if adv.get("debate_result") else 0,
        "debate_bull_score": adv.get("debate_result").bull_score if adv.get("debate_result") else 0,
        "debate_bear_score": adv.get("debate_result").bear_score if adv.get("debate_result") else 0,
        "debate_summary": adv.get("debate_result").summary if adv.get("debate_result") else "",
        "debate_adjustment": adv.get("debate_adjustment", 0),
        "debate_reason": adv.get("debate_reason", ""),
        # === Phase 2+: Multi-Horizon Prediction ===
        "multi_horizon_consensus": adv.get("multi_horizon_result").consensus_signal if adv.get("multi_horizon_result") else "HOLD",
        "multi_horizon_confidence": adv.get("multi_horizon_result").consensus_confidence if adv.get("multi_horizon_result") else 0,
        "multi_horizon_agreement": adv.get("multi_horizon_result").horizon_agreement if adv.get("multi_horizon_result") else 0,
        "multi_horizon_adjustment": adv.get("multi_horizon_adjustment", 0),
        "multi_horizon_reason": adv.get("multi_horizon_reason", ""),
        # === Phase 2+: ReAct Agent ===
        "react_recommendation": adv.get("react_result", {}).get("recommendation", "HOLD") if adv.get("react_result") else "HOLD",
        "react_confidence": adv.get("react_result", {}).get("confidence", 0) if adv.get("react_result") else 0,
        "react_n_steps": adv.get("react_result", {}).get("n_steps", 0) if adv.get("react_result") else 0,
        "react_adjustment": adv.get("react_adjustment", 0),
        "react_reason": adv.get("react_reason", ""),
        # === Phase 2+: Social Sentiment ===
        "social_sentiment_score": adv.get("social_sentiment_result", {}).get("overall_sentiment", 0) if adv.get("social_sentiment_result") else 0,
        "social_sentiment_label": adv.get("social_sentiment_result", {}).get("sentiment_label", "neutral") if adv.get("social_sentiment_result") else "neutral",
        "social_sentiment_n_posts": adv.get("social_sentiment_result", {}).get("n_posts", 0) if adv.get("social_sentiment_result") else 0,
        "social_sentiment_trending": adv.get("social_sentiment_result", {}).get("trending", False) if adv.get("social_sentiment_result") else False,
        "social_sentiment_adjustment": adv.get("social_sentiment_adjustment", 0),
        "social_sentiment_reason": adv.get("social_sentiment_reason", ""),
        # === Kronos Foundation Model ===
        "kronos_direction": adv.get("kronos_result").predicted_direction if adv.get("kronos_result") and adv.get("kronos_result").available else "N/A",
        "kronos_confidence": adv.get("kronos_result").confidence if adv.get("kronos_result") and adv.get("kronos_result").available else 0,
        "kronos_forecast_prices": [round(float(p), 2) for p in adv.get("kronos_result").forecast_prices] if adv.get("kronos_result") and adv.get("kronos_result").available else [],
        "kronos_adjustment": adv.get("kronos_adjustment", 0),
        "kronos_reason": adv.get("kronos_reason", ""),
        # === SHAP Explainability ===
        "shap_explanations": {
            name: {
                "method": exp.method,
                "top_features": [(n, round(v, 5)) for n, v in exp.top_features[:5]],
                "summary": exp.text_summary,
            }
            for name, exp in shap_explanations.items()
        } if shap_explanations else {},
        "tanggal_prediksi": today,
        "tanggal_target": tomorrow,
        # === Multi-Day Horizon Forecast ===
        "multi_day_forecast": _calc_multi_day_forecast(
            predicted_return, current_price, confidence, df_clean, target_name
        ),
    }

    # === MLflow Tracking ===
    try:
        from .mlflow_tracking import log_model_training
        log_model_training(
            model_name=f"Prediction_{target_ticker}",
            params={
                "ticker": target_ticker,
                "n_features": len(feature_cols),
                "n_train": len(X_train),
                "signal": result["sinyal"],
                "direction": result["arah_prediksi"],
            },
            metrics={
                "confidence": result["confidence"],
                "ai_score": result.get("ai_score", 0),
                "predicted_return": predicted_return,
            },
            tags={"target_ticker": target_ticker, "date": today},
        )
    except Exception as e:
        print(f"[MLflow] Logging skipped: {e}")

    # === Drift Monitoring + Model Registry ===
    try:
        monitor = DriftMonitor()
        monitor.set_reference(X_train)
        drift_report = monitor.check(latest_features, feature_cols=feature_cols)
        if drift_report.has_significant_drift:
            print(f"[DRIFT] {drift_report.summary()}")
            log_aktivitas(
                jenis="DRIFT_ALERT",
                pesan=f"Drift detected for {target_ticker}: overall PSI={drift_report.overall_psi:.3f}",
                level="WARNING",
            )
            try:
                from .notifier import send_in_app
                send_in_app(
                    kategori="DRIFT",
                    judul="Model Drift Detected",
                    pesan=drift_report.summary(),
                    level="error",
                )
            except Exception:
                pass
        else:
            print(f"[DRIFT] Data stable for {target_ticker}: overall PSI={drift_report.overall_psi:.3f}")

        result["drift_report"] = {
            "overall_psi": drift_report.overall_psi,
            "has_significant_drift": drift_report.has_significant_drift,
            "significant_features": drift_report.significant_features,
            "recommendation": drift_report.recommendation,
        }
    except Exception as e:
        print(f"[Drift] Monitoring skipped: {e}")

    # === Model Registry: save trained model + metadata ===
    try:
        drift_psi = result.get("drift_report", {}).get("overall_psi", 0.0)
        model_metrics = {
            "overall_psi": drift_psi,
            "n_features": len(feature_cols),
            "n_train": len(X_train),
            "confidence": result["confidence"],
        }
        artifact_path, meta = save_model(
            ensemble,
            ticker=target_ticker,
            model_name="hybrid_ensemble",
            metrics=model_metrics,
            tags=["daily_prediction", "hybrid_ensemble"],
            notes=f"Trained on {len(X_train)} rows; drift PSI={drift_psi:.3f}",
        )
        print(f"[MODEL REGISTRY] Model saved to {artifact_path}")
    except Exception as e:
        print(f"[WARNING] Model registry save failed: {e}")

    return result
