import pandas as pd
import numpy as np
from typing import Dict, Optional
from .config import MODEL_CONFIG, TARGET_TICKER, TICKERS
from .models import HybridEnsemble
from .preprocessor import prepare_features, get_feature_columns, train_test_split_time


def directional_accuracy(y_true: pd.Series, y_pred: np.ndarray) -> float:
    correct = (y_true.values == y_pred).sum()
    return (correct / len(y_true)) * 100


def mape(y_true: pd.Series, y_pred: pd.Series) -> float:
    valid = y_true != 0
    return (abs(y_true[valid] - y_pred[valid]) / y_true[valid]).mean() * 100


def run_backtest(
    market_data: dict,
    fred_data: Optional[dict] = None,
    target_ticker: str = TARGET_TICKER,
    ensemble: Optional[HybridEnsemble] = None,
) -> Dict:
    print("\n" + "=" * 60)
    print("BACKTESTING MODEL")
    print("=" * 60)

    df = prepare_features(market_data, fred_data, target_ticker)
    if df.empty:
        return {"error": "Data kosong"}

    target_name = next((name for name, t in TICKERS.items() if t == target_ticker), "TARGET")

    feature_cols = get_feature_columns(df)
    df_clean = df.dropna(subset=feature_cols + ["Target_Next_Return"])
    df_clean["Target_Direction"] = (df_clean["Target_Next_Return"] > 0).astype(int)

    if len(df_clean) < 100:
        return {"error": f"Data terlalu sedikit: {len(df_clean)} baris"}

    train, test = train_test_split_time(df_clean, MODEL_CONFIG["test_size"])
    X_train = train[feature_cols]
    y_train = train["Target_Direction"]
    X_test = test[feature_cols]
    y_test = test["Target_Direction"]

    if ensemble is None:
        ensemble = HybridEnsemble()

    if not ensemble.trained:
        ensemble.train(X_train, y_train)

    all_preds, all_probas = ensemble.predict_batch(X_test)

    results = {}
    for model_name, preds in all_preds.items():
        if len(preds) == len(y_test):
            da = directional_accuracy(y_test, preds)
            results[model_name] = {
                "directional_accuracy": round(da, 2),
                "predictions": preds,
            }
            print(f"  {model_name}: DA = {da:.2f}%")

    # Ensemble voting
    min_len = min(len(p) for p in all_preds.values()) if all_preds else 0
    if min_len > 0:
        ensemble_preds = []
        for i in range(min_len):
            votes = sum(1 for preds in all_preds.values() if i < len(preds) and preds[i] == 1)
            total = len(all_preds)
            ensemble_preds.append(1 if votes > total / 2 else 0)

        ensemble_preds = np.array(ensemble_preds)
        y_test_aligned = y_test.iloc[:min_len]
        ensemble_da = directional_accuracy(y_test_aligned, ensemble_preds)
        results["Ensemble"] = {
            "directional_accuracy": round(ensemble_da, 2),
            "predictions": ensemble_preds,
        }
        print(f"  Ensemble (Voting): DA = {ensemble_da:.2f}%")

    # Price prediction MAPE — use model predictions, not actual returns
    target_close_col = f"{target_name}_Close"
    if target_close_col in test.columns and min_len > 0:
        actual_prices = test[target_close_col].iloc[:min_len]
        current_prices = test[target_close_col].shift(1).iloc[:min_len]

        # Estimate predicted returns from ensemble votes + historical avg magnitude
        target_returns = df_clean["Target_Returns"].dropna()
        avg_up = target_returns[target_returns > 0].mean()
        avg_down = target_returns[target_returns < 0].mean()
        if np.isnan(avg_up):
            avg_up = 0.0
        if np.isnan(avg_down):
            avg_down = 0.0

        predicted_returns = np.where(ensemble_preds == 1, avg_up, avg_down)
        predicted_prices = current_prices * (1 + predicted_returns)

        valid_mask = (actual_prices.notna()) & (predicted_prices.notna()) & (actual_prices != 0)
        if valid_mask.any():
            mape_val = mape(actual_prices[valid_mask], predicted_prices[valid_mask])
            results["MAPE"] = round(mape_val, 2)
            print(f"  MAPE: {mape_val:.2f}%")

    print("\n[OK] Backtesting selesai")

    return results


def simulate_trading(
    market_data: dict,
    fred_data: Optional[dict] = None,
    target_ticker: str = TARGET_TICKER,
    initial_capital: float = 100_000_000,
) -> Dict:
    print("\n" + "=" * 60)
    print("SIMULASI TRADING (PAPER TRADING)")
    print("=" * 60)

    df = prepare_features(market_data, fred_data, target_ticker)
    if df.empty:
        return {"error": "Data kosong"}

    target_name = next((name for name, t in TICKERS.items() if t == target_ticker), "TARGET")

    feature_cols = get_feature_columns(df)
    df_clean = df.dropna(subset=feature_cols + ["Target_Next_Return"])
    df_clean["Target_Direction"] = (df_clean["Target_Next_Return"] > 0).astype(int)

    if len(df_clean) < 100:
        return {"error": "Data terlalu sedikit"}

    train, test = train_test_split_time(df_clean, MODEL_CONFIG["test_size"])
    X_train = train[feature_cols]
    y_train = train["Target_Direction"]
    X_test = test[feature_cols]

    ensemble = HybridEnsemble()
    ensemble.train(X_train, y_train)

    all_preds, _ = ensemble.predict_batch(X_test)

    if not all_preds:
        return {"error": "Tidak ada prediksi"}

    min_len = min(len(p) for p in all_preds.values())
    ensemble_preds = []
    for i in range(min_len):
        votes = sum(1 for preds in all_preds.values() if i < len(preds) and preds[i] == 1)
        total = len(all_preds)
        ensemble_preds.append(1 if votes > total / 2 else 0)

    target_close_col = f"{target_name}_Close"
    test_prices = test[target_close_col].iloc[:min_len]

    capital = initial_capital
    position = 0
    shares = 0
    trades = 0
    portfolio_values = []

    for i in range(min_len):
        signal = ensemble_preds[i]

        if signal == 1 and position == 0:
            shares = capital / test_prices.iloc[i]
            position = 1
            trades += 1
        elif signal == 0 and position == 1:
            capital = shares * test_prices.iloc[i]
            shares = 0
            position = 0
            trades += 1

        current_value = capital if position == 0 else shares * test_prices.iloc[i]
        portfolio_values.append(current_value)

    if position == 1 and min_len > 0:
        capital = shares * test_prices.iloc[-1]
        position = 0
        trades += 1

    total_return = ((capital - initial_capital) / initial_capital) * 100

    # Buy & hold benchmark
    if min_len > 0:
        bh_shares = initial_capital / test_prices.iloc[0]
        bh_final = bh_shares * test_prices.iloc[-1]
        bh_return = ((bh_final - initial_capital) / initial_capital) * 100
    else:
        bh_return = 0

    max_value = max(portfolio_values) if portfolio_values else initial_capital
    min_value = min(portfolio_values) if portfolio_values else initial_capital
    max_drawdown = ((min_value - max_value) / max_value) * 100 if max_value > 0 else 0

    print(f"  Modal Awal: Rp {initial_capital:,.0f}")
    print(f"  Modal Akhir: Rp {capital:,.0f}")
    print(f"  Total Return: {total_return:.2f}%")
    print(f"  Buy & Hold Return: {bh_return:.2f}%")
    print(f"  Jumlah Trade: {trades}")
    print(f"  Max Drawdown: {max_drawdown:.2f}%")

    return {
        "initial_capital": initial_capital,
        "final_capital": round(capital, 2),
        "total_return": round(total_return, 2),
        "buy_hold_return": round(bh_return, 2),
        "trades": trades,
        "max_drawdown": round(max_drawdown, 2),
        "portfolio_values": portfolio_values,
    }
