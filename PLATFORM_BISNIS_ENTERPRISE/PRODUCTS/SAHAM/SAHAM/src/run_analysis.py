#!/usr/bin/env python3
"""
Script utama untuk menjalankan analisis harian.
Dapat dijalankan manual atau via cron job / GitHub Actions.

Usage:
    python -m src.run_analysis                      # Jalankan prediksi + verifikasi
    python -m src.run_analysis --predict            # Hanya prediksi
    python -m src.run_analysis --predict --ticker BBCA.JK  # Prediksi ticker tertentu
    python -m src.run_analysis --verify             # Hanya verifikasi prediksi sebelumnya
    python -m src.run_analysis --backtest           # Jalankan backtesting
    python -m src.run_analysis --screener           # Jalankan stock screener
    python -m src.run_analysis --status             # Cek status database & model
    python -m src.run_analysis --notify             # Kirim notifikasi setelah prediksi
    python -m src.run_analysis --retrain            # Check & trigger model retraining
    python -m src.run_analysis --all                # Jalankan semua: predict + verify + notify

Tips untuk komputer RAM terbatas (8GB):
    - Tutup browser/Discord/IDE berat sebelum run
    - Gunakan --predict saja (bukan --all) untuk hemat RAM
    - --status paling ringan, hanya cek database
"""

import argparse
import os
import sys
from datetime import datetime

# Memory optimization: limit BLAS threads to avoid RAM spike
os.environ.setdefault("OMP_NUM_THREADS", "4")
os.environ.setdefault("OPENBLAS_NUM_THREADS", "4")

from .config import TARGET_TICKER, TICKERS, BLUE_CHIPS_ID
from .database import init_db, update_aktual, get_all_prediksi, log_aktivitas, get_akurasi_metrics
from .data_fetcher import fetch_all_data, get_current_price
from .predictor import run_prediction
from .notifier import notify_prediction


def cmd_status():
    """Cek status database, model, dan sistem — paling ringan, tidak butuh internet."""
    print("\n" + "#" * 60)
    print("# STATUS SISTEM")
    print(f"# {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("#" * 60)

    # Database
    try:
        from .database import get_connection
        conn = get_connection()
        cursor = conn.cursor()

        tables = ["prediksi", "harga_harian", "harga_intraday", "notifikasi",
                   "log_aktivitas", "fundamental_data", "technical_indicators",
                   "financial_ratios", "alerts"]
        print("\n[DATABASE]")
        for t in tables:
            try:
                cursor.execute(f"SELECT COUNT(*) FROM {t}")
                count = cursor.fetchone()[0]
                print(f"  {t}: {count} rows")
            except Exception:
                print(f"  {t}: table not found")

        # Check indexes
        cursor.execute(
            "SELECT COUNT(*) FROM sqlite_master WHERE type='index' AND sql IS NOT NULL"
        )
        idx_count = cursor.fetchone()[0]
        print(f"  indexes: {idx_count}")

        cursor.execute("PRAGMA journal_mode")
        journal = cursor.fetchone()[0]
        print(f"  journal_mode: {journal}")

        conn.close()
    except Exception as e:
        print(f"  [ERROR] Database: {e}")

    # Model files
    print("\n[MODELS]")
    try:
        from .config import MODEL_PATH
        model_dir = os.path.dirname(MODEL_PATH) if hasattr(__import__('src.config', fromlist=['MODEL_PATH']), 'MODEL_PATH') else "src/models"
        if os.path.exists(model_dir):
            for f in os.listdir(model_dir):
                if f.endswith((".pkl", ".json")):
                    size = os.path.getsize(os.path.join(model_dir, f))
                    print(f"  {f}: {size:,} bytes")
        else:
            print("  No model directory found")
    except Exception:
        print("  (model check skipped)")

    # Accuracy metrics
    print("\n[ACCURACY]")
    try:
        metrics = get_akurasi_metrics()
        print(f"  Directional Accuracy: {metrics['directional_accuracy']}%")
        print(f"  MAPE: {metrics['mape']}%")
        print(f"  Total Verified: {metrics['total']}")
        print(f"  Benar: {metrics['benar']}")
    except Exception as e:
        print(f"  (no metrics yet: {e})")

    # Memory info
    print("\n[SYSTEM]")
    try:
        import psutil
        mem = psutil.virtual_memory()
        print(f"  RAM: {mem.total / 1e9:.1f} GB total, {mem.available / 1e9:.1f} GB available ({mem.percent}% used)")
        cpu = psutil.cpu_percent(interval=0.5)
        print(f"  CPU: {cpu}% used")
    except ImportError:
        print("  (psutil not installed)")

    # Import time
    print("\n[LAZY IMPORTS]")
    try:
        from .lazy_imports import get_import_times
        times = get_import_times()
        for mod, status in times.items():
            print(f"  {mod}: {status}")
    except Exception:
        print("  (lazy_imports not available)")


def cmd_screener(top_n: int = 10):
    """Jalankan stock screener untuk blue chips Indonesia."""
    print("\n" + "#" * 60)
    print("# STOCK SCREENER")
    print(f"# {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("#" * 60)

    from .data_fetcher import fetch_all_market_data
    from .screener import run_screener, format_screener_results

    market_data = fetch_all_market_data(period="6mo")
    if not market_data:
        print("[ERROR] Tidak ada data market")
        return

    results = run_screener(market_data=market_data, tickers=BLUE_CHIPS_ID, top_n=top_n)
    df = format_screener_results(results)

    if df is None or df.empty:
        print("[INFO] Tidak ada saham yang lolos screener")
        return

    print(f"\nTop {len(df)} saham:")
    print(df.to_string(index=False))
    return df


def cmd_predict(notify: bool = False, ticker: str = None):
    print("\n" + "#" * 60)
    print("# MENJALANKAN PREDIKSI HARIAN")
    print(f"# Tanggal: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    if ticker:
        print(f"# Ticker: {ticker}")
    print("#" * 60)

    target = ticker or TARGET_TICKER

    data = fetch_all_data(period="2y")
    if not data["market"]:
        print("[ERROR] Tidak ada data market yang berhasil di-fetch")
        return

    # Check if model needs retraining (lazy import)
    try:
        from .retrain_scheduler import RetrainingScheduler
        from .preprocessor import prepare_features, get_feature_columns

        scheduler = RetrainingScheduler()
        df = prepare_features(data["market"], data["fred"], target)
        if not df.empty:
            feat_cols = get_feature_columns(df)
            retrain_check = scheduler.check_retrain_needed(df, feat_cols)
            if retrain_check.get("needs_retrain"):
                print(f"  [DRIFT] Retrain needed: {', '.join(retrain_check.get('reasons', []))}")
                log_aktivitas("RETRAIN_TRIGGERED", "; ".join(retrain_check.get("reasons", [])))
    except Exception as e:
        print(f"  [WARN] Retrain check skipped: {e}")

    from .models import HybridEnsemble
    ensemble = HybridEnsemble()
    result = run_prediction(
        market_data=data["market"],
        fred_data=data["fred"],
        target_ticker=target,
        ensemble=ensemble,
    )

    if "error" in result:
        print(f"[ERROR] {result['error']}")
        return

    if notify:
        notify_prediction(result)

    return result


def cmd_verify():
    print("\n" + "#" * 60)
    print("# VERIFIKASI PREDIKSI SEBELUMNYA")
    print(f"# Tanggal: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("#" * 60)

    datetime.now().strftime("%Y-%m-%d")
    df = get_all_prediksi()

    if df.empty:
        print("[INFO] Tidak ada prediksi untuk diverifikasi")
        return

    unverified = df[df["harga_aktual"].isna()]
    if unverified.empty:
        print("[INFO] Semua prediksi sudah terverifikasi")
        return

    print(f"[INFO] {len(unverified)} prediksi belum terverifikasi")

    for _, row in unverified.iterrows():
        ticker = row["ticker"]
        target_date = row["tanggal_target"]

        target_dt = datetime.strptime(target_date, "%Y-%m-%d")
        if target_dt.date() > datetime.now().date():
            continue

        current_price = get_current_price(ticker)
        if current_price is None:
            print(f"[SKIP] Tidak bisa get harga untuk {ticker} pada {target_date}")
            continue

        pred_price = row["harga_prediksi"]
        change = ((current_price - pred_price) / pred_price) * 100

        updated = update_aktual(ticker, target_date, current_price)
        if updated > 0:
            print(f"[OK] {ticker}: Prediksi={pred_price:,.2f}, Aktual={current_price:,.2f}, Selisih={change:+.2f}%")
            log_aktivitas("VERIFIKASI", f"{ticker}: aktual={current_price}, selisih={change:+.2f}%")

    metrics = get_akurasi_metrics()
    print(f"\n{'=' * 40}")
    print("METRIK AKURASI KESELURUHAN")
    print(f"{'=' * 40}")
    print(f"Directional Accuracy: {metrics['directional_accuracy']}%")
    print(f"MAPE: {metrics['mape']}%")
    print(f"Total Prediksi Terverifikasi: {metrics['total']}")
    print(f"Benar: {metrics['benar']}")
    print(f"{'=' * 40}")


def cmd_backtest():
    print("\n" + "#" * 60)
    print("# BACKTESTING MODEL")
    print("#" * 60)

    from .backtesting import run_backtest, simulate_trading

    data = fetch_all_data(period="2y")
    if not data["market"]:
        print("[ERROR] Tidak ada data market")
        return

    results = run_backtest(
        market_data=data["market"],
        fred_data=data["fred"],
    )

    print("\n--- SIMULASI TRADING ---")
    sim_results = simulate_trading(
        market_data=data["market"],
        fred_data=data["fred"],
    )

    return {"backtest": results, "simulation": sim_results}


def main():
    parser = argparse.ArgumentParser(
        description="Aplikasi Proyeksi Pasar Saham Global",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Contoh:
  python -m src.run_analysis --status           # Cek sistem (paling ringan)
  python -m src.run_analysis --predict          # Prediksi IHSG
  python -m src.run_analysis --predict --ticker BBCA.JK  # Prediksi saham tertentu
  python -m src.run_analysis --screener         # Cari saham terbaik
  python -m src.run_analysis --all              # Full run (predict + verify + notify)
        """,
    )
    parser.add_argument("--predict", action="store_true", help="Jalankan prediksi")
    parser.add_argument("--ticker", type=str, default=None, help="Ticker target (default: ^JKSE)")
    parser.add_argument("--verify", action="store_true", help="Verifikasi prediksi sebelumnya")
    parser.add_argument("--backtest", action="store_true", help="Jalankan backtesting")
    parser.add_argument("--screener", action="store_true", help="Jalankan stock screener")
    parser.add_argument("--status", action="store_true", help="Cek status database & sistem")
    parser.add_argument("--notify", action="store_true", help="Kirim notifikasi setelah prediksi")
    parser.add_argument("--retrain", action="store_true", help="Check & trigger model retraining")
    parser.add_argument("--all", action="store_true", help="Jalankan semua: predict + verify + notify")

    args = parser.parse_args()

    init_db()

    if args.status:
        cmd_status()
        return

    if args.screener:
        cmd_screener()
        return

    if args.all or (not args.predict and not args.verify and not args.backtest and not args.retrain):
        cmd_verify()
        result = cmd_predict(notify=True, ticker=args.ticker)
        if result and "error" not in result:
            print("\n[OK] Analisis harian selesai dengan notifikasi")
    else:
        if args.verify:
            cmd_verify()
        if args.retrain:
            from .retrain_scheduler import RetrainingScheduler
            from .preprocessor import prepare_features, get_feature_columns
            from .models import HybridEnsemble
            scheduler = RetrainingScheduler()
            data = fetch_all_data(period="2y")
            df = prepare_features(data.get("market", {}), data.get("fred"), TARGET_TICKER)
            if not df.empty:
                feat_cols = get_feature_columns(df)
                check = scheduler.check_retrain_needed(df, feat_cols)
                if check.get("needs_retrain"):
                    print(f"\n[RETRAIN] Reasons: {', '.join(check.get('reasons', []))}")
                    ensemble = HybridEnsemble()
                    run_prediction(market_data=data["market"], fred_data=data["fred"], ensemble=ensemble)
                    scheduler.record_training(accuracy=0.0, baseline_df=df, feature_cols=feat_cols)
                    print("[OK] Model retrained")
                else:
                    print("[OK] No retrain needed")
        if args.predict:
            cmd_predict(notify=args.notify, ticker=args.ticker)
        if args.backtest:
            cmd_backtest()


if __name__ == "__main__":
    main()
