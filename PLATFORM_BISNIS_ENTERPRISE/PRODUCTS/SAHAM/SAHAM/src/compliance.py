"""
Compliance & Audit Trail Module.

Implementasi:
- OJK disclaimers (Otoritas Jasa Keuangan)
- Audit trail untuk semua prediksi dan keputusan
- Risk disclosure statements
- Data provenance tracking

Referensi:
- OJK Regulation: Penyelenggaraan Teknologi Finansial
- POJK 10/2022: Layanan Uang Elektronik & Inovasi Keuangan
- Best practices dari KSEI/BEI compliance guidelines
"""

import json
from datetime import datetime
from typing import Dict, List, Optional
from dataclasses import dataclass, field
import sqlite3

from .config import DB_PATH


# =============================================================================
# OJK DISCLAIMERS
# =============================================================================

OJK_DISCLAIMER = (
    "⚠️ PERINGATAN RISIKO: Aplikasi ini adalah alat bantu analisis berbasis "
    "machine learning untuk tujuan edukasi dan riset. Bukan merupakan nasihat "
    "investasi (investment advice). Investasi saham memiliki risiko kerugian. "
    "Keputusan investasi adalah tanggung jawab Anda sendiri. "
    "Pastikan Anda memahami risiko investasi sebelum bertransaksi. "
    "Otoritas Jasa Keuangan (OJK) tidak mengatur atau mengawasi aplikasi ini."
)

RISK_DISCLOSURE = (
    "📋 DISKLAIMER RISIKO:\n"
    "1. Prediksi dihasilkan oleh model ML dan tidak menjamin akurasi 100%.\n"
    "2. Kinerja masa lalu tidak menjamin hasil masa depan.\n"
    "3. Pasar saham dapat bergerak diluar prediksi akibat faktor eksternal.\n"
    "4. Selakukan due diligence mandiri sebelum mengambil keputusan investasi.\n"
    "5. Konsultasi dengan penasihat keuangan berlisensi OJK untuk keputusan investasi."
)

DATA_DISCLOSURE = (
    "📊 SUMBER DATA:\n"
    "- Yahoo Finance (data harga real-time)\n"
    "- FRED API (data makro ekonomi US)\n"
    "- Data publik BEI/IDX (sebagaimana tersedia)\n"
    "- Model ML: Random Forest, XGBoost, LightGBM (ensemble voting)"
)


# =============================================================================
# AUDIT TRAIL
# =============================================================================

@dataclass
class AuditEntry:
    timestamp: str
    action: str  # "PREDICT", "BACKTEST", "TRADE_SIGNAL", "CONFIG_CHANGE", "MODEL_TRAIN"
    actor: str  # "system", "user", "api"
    details: str
    metadata: Dict = field(default_factory=dict)
    risk_level: str = "low"  # "low", "medium", "high"

    def to_dict(self) -> dict:
        return {
            "timestamp": self.timestamp,
            "action": self.action,
            "actor": self.actor,
            "details": self.details,
            "metadata": self.metadata,
            "risk_level": self.risk_level,
        }


class AuditTrail:
    """
    Audit trail untuk semua keputusan dan prediksi.

    Stores audit entries in SQLite database for persistence.
    """

    def __init__(self, db_path: Optional[str] = None):
        self.db_path = db_path or DB_PATH
        self._init_audit_table()

    def _init_audit_table(self):
        conn = sqlite3.connect(self.db_path)
        conn.execute("""
            CREATE TABLE IF NOT EXISTS audit_trail (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timestamp TEXT NOT NULL,
                action TEXT NOT NULL,
                actor TEXT NOT NULL,
                details TEXT,
                metadata TEXT,
                risk_level TEXT DEFAULT 'low'
            )
        """)
        conn.commit()
        conn.close()

    def log(
        self,
        action: str,
        actor: str = "system",
        details: str = "",
        metadata: Optional[Dict] = None,
        risk_level: str = "low",
    ):
        """Log an audit entry."""
        entry = AuditEntry(
            timestamp=datetime.now().isoformat(),
            action=action,
            actor=actor,
            details=details,
            metadata=metadata or {},
            risk_level=risk_level,
        )

        conn = sqlite3.connect(self.db_path)
        conn.execute(
            "INSERT INTO audit_trail (timestamp, action, actor, details, metadata, risk_level) VALUES (?, ?, ?, ?, ?, ?)",
            (entry.timestamp, entry.action, entry.actor, entry.details, json.dumps(entry.metadata), entry.risk_level),
        )
        conn.commit()
        conn.close()

        return entry

    def get_entries(
        self,
        action: Optional[str] = None,
        limit: int = 100,
        since: Optional[str] = None,
    ) -> List[dict]:
        """Retrieve audit entries."""
        conn = sqlite3.connect(self.db_path)

        query = "SELECT * FROM audit_trail"
        params = []
        conditions = []

        if action:
            conditions.append("action = ?")
            params.append(action)
        if since:
            conditions.append("timestamp >= ?")
            params.append(since)

        if conditions:
            query += " WHERE " + " AND ".join(conditions)

        query += " ORDER BY timestamp DESC LIMIT ?"
        params.append(limit)

        cursor = conn.execute(query, params)
        rows = cursor.fetchall()
        conn.close()

        columns = ["id", "timestamp", "action", "actor", "details", "metadata", "risk_level"]
        return [dict(zip(columns, row)) for row in rows]

    def get_risk_events(self, limit: int = 50) -> List[dict]:
        """Get high-risk audit events."""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.execute(
            "SELECT * FROM audit_trail WHERE risk_level = 'high' ORDER BY timestamp DESC LIMIT ?",
            (limit,),
        )
        rows = cursor.fetchall()
        conn.close()

        columns = ["id", "timestamp", "action", "actor", "details", "metadata", "risk_level"]
        return [dict(zip(columns, row)) for row in rows]


# =============================================================================
# COMPLIANCE HELPERS
# =============================================================================

def get_compliance_disclaimer() -> str:
    """Get full compliance disclaimer text for display."""
    return f"{OJK_DISCLAIMER}\n\n{RISK_DISCLOSURE}\n\n{DATA_DISCLOSURE}"


def log_prediction_audit(
    ticker: str,
    signal: str,
    confidence: float,
    model_votes: Dict,
    rules: str,
    audit: Optional[AuditTrail] = None,
):
    """Log a prediction event to audit trail."""
    if audit is None:
        audit = AuditTrail()

    risk_level = "high" if confidence < 0.55 else ("medium" if confidence < 0.70 else "low")

    audit.log(
        action="PREDICT",
        actor="system",
        details=f"Ticker={ticker}, Signal={signal}, Confidence={confidence:.2%}, Rules={rules}",
        metadata={
            "ticker": ticker,
            "signal": signal,
            "confidence": confidence,
            "model_votes": model_votes,
        },
        risk_level=risk_level,
    )


def log_backtest_audit(
    strategy: str,
    total_return: float,
    max_drawdown: float,
    audit: Optional[AuditTrail] = None,
):
    """Log a backtest event to audit trail."""
    if audit is None:
        audit = AuditTrail()

    risk_level = "high" if max_drawdown < -20 else ("medium" if max_drawdown < -10 else "low")

    audit.log(
        action="BACKTEST",
        actor="system",
        details=f"Strategy={strategy}, Return={total_return:.2f}%, MaxDD={max_drawdown:.2f}%",
        metadata={
            "strategy": strategy,
            "total_return": total_return,
            "max_drawdown": max_drawdown,
        },
        risk_level=risk_level,
    )
