import requests
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from .config import (
    TELEGRAM_BOT_TOKEN,
    TELEGRAM_CHAT_ID,
    SMTP_SERVER,
    SMTP_PORT,
    SMTP_USERNAME,
    SMTP_PASSWORD,
    EMAIL_TO,
)
from .database import log_aktivitas, simpan_notifikasi


def send_in_app(kategori: str, judul: str, pesan: str, level: str = "info"):
    """Kirim notifikasi ke in-app notification center (SQLite). Selalu tersedia."""
    try:
        simpan_notifikasi(kategori=kategori, judul=judul, pesan=pesan, level=level)
        print(f"[OK] Notifikasi in-app tersimpan: {judul}")
    except Exception as e:
        print(f"[ERROR] Gagal simpan notifikasi in-app: {e}")


def send_telegram(message: str) -> bool:
    if not TELEGRAM_BOT_TOKEN or not TELEGRAM_CHAT_ID:
        print("[SKIP] Telegram belum dikonfigurasi")
        return False

    url = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"
    payload = {
        "chat_id": TELEGRAM_CHAT_ID,
        "text": message,
        "parse_mode": "Markdown",
    }

    try:
        resp = requests.post(url, json=payload, timeout=30)
        if resp.status_code == 200:
            print("[OK] Notifikasi Telegram terkirim")
            log_aktivitas("NOTIFIKASI_TELEGRAM", "Terkirim")
            return True
        else:
            print(f"[ERROR] Telegram error: {resp.status_code} - {resp.text}")
            return False
    except Exception as e:
        print(f"[ERROR] Gagal kirim Telegram: {e}")
        return False


def send_email(subject: str, body: str) -> bool:
    if not SMTP_USERNAME or not SMTP_PASSWORD or not EMAIL_TO:
        print("[SKIP] Email belum dikonfigurasi")
        return False

    msg = MIMEMultipart()
    msg["From"] = SMTP_USERNAME
    msg["To"] = EMAIL_TO
    msg["Subject"] = subject
    msg.attach(MIMEText(body, "plain"))

    try:
        server = smtplib.SMTP(SMTP_SERVER, SMTP_PORT)
        server.starttls()
        server.login(SMTP_USERNAME, SMTP_PASSWORD)
        server.sendmail(SMTP_USERNAME, EMAIL_TO, msg.as_string())
        server.quit()
        print("[OK] Notifikasi Email terkirim")
        log_aktivitas("NOTIFIKASI_EMAIL", f"Subject: {subject}")
        return True
    except Exception as e:
        print(f"[ERROR] Gagal kirim email: {e}")
        return False


def format_prediction_message(result: dict) -> str:
    ticker = result.get("ticker", "N/A")
    sinyal = result.get("sinyal", "N/A")
    confidence = result.get("confidence", 0)
    current_price = result.get("current_price", 0)
    predicted_price = result.get("predicted_price", 0)
    arah = result.get("arah_prediksi", "N/A")
    rules = result.get("rules", "")

    votes = result.get("predictions", {})
    votes_str = ", ".join([f"{k}: {'BUY' if v == 1 else 'SELL'}" for k, v in votes.items()])

    emoji = {"BUY": "🟢", "SELL": "🔴", "HOLD": "🟡"}

    message = f"""
*HASIL ANALISIS PASAR SAHAM*
{'=' * 30}
*Ticker:* `{ticker}`
*Sinyal:* {emoji.get(sinyal, '⚪')} *{sinyal}*
*Confidence:* {confidence:.2%}
*Harga Saat Ini:* {current_price:,.2f}
*Harga Prediksi:* {predicted_price:,.2f}
*Arah:* {arah}
*Model Votes:* {votes_str}
*Rules:* {rules}
{'=' * 30}
_Disclaimer: Prediksi bukan saran investasi. Terapkan manajemen risiko._
"""
    return message


def notify_prediction(result: dict):
    message = format_prediction_message(result)
    sinyal = result.get("sinyal", "HOLD")
    level = "success" if sinyal == "BUY" else "warning" if sinyal == "SELL" else "info"
    ticker = result.get("ticker", "N/A")
    confidence = result.get("confidence", 0)

    # In-app notification (selalu tersedia, tidak butuh API)
    send_in_app(
        kategori="PREDIKSI",
        judul=f"{sinyal} {ticker} — Confidence {confidence:.1%}",
        pesan=message.replace("*", "").replace("`", "").strip(),
        level=level,
    )

    # Telegram (opsional, jika dikonfigurasi)
    send_telegram(message)

    # Email (opsional, jika dikonfigurasi)
    send_email(
        subject=f"[Sinyal Saham] {ticker} - {sinyal}",
        body=message.replace("*", "").replace("`", ""),
    )
