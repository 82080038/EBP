"""
System Requirements Checker — evaluates hardware compatibility for running
the Saham prediction application.

Checks: CPU cores, RAM, disk space, GPU (optional), Python version,
key packages, and estimates compatibility percentage.
"""
from __future__ import annotations

import os
import sys
import platform
import psutil
import shutil
from dataclasses import dataclass, field
from typing import List, Tuple


@dataclass
class RequirementCheck:
    """Single requirement check result."""
    name: str = ""
    category: str = ""  # cpu, ram, disk, gpu, python, package
    current_value: str = ""
    required_value: str = ""
    recommended_value: str = ""
    passed: bool = False
    weight: float = 1.0  # importance weight
    score: float = 0.0  # 0-1
    notes: str = ""


@dataclass
class SystemReport:
    """Full system compatibility report."""
    os_name: str = ""
    os_version: str = ""
    python_version: str = ""
    cpu_name: str = ""
    cpu_cores: int = 0
    cpu_threads: int = 0
    cpu_freq_ghz: float = 0.0
    total_ram_gb: float = 0.0
    available_ram_gb: float = 0.0
    disk_total_gb: float = 0.0
    disk_free_gb: float = 0.0
    has_gpu: bool = False
    gpu_name: str = ""
    gpu_vram_gb: float = 0.0
    checks: List[RequirementCheck] = field(default_factory=list)
    overall_score: float = 0.0
    compatibility_pct: float = 0.0
    verdict: str = ""
    recommendations: List[str] = field(default_factory=list)


# Application requirements
REQUIREMENTS = {
    "cpu_cores_min": 4,
    "cpu_cores_recommended": 8,
    "cpu_freq_min": 2.0,
    "cpu_freq_recommended": 3.0,
    "ram_min_gb": 8,
    "ram_recommended_gb": 16,
    "disk_free_min_gb": 5,
    "disk_free_recommended_gb": 20,
    "python_min": (3, 10),
    "python_recommended": (3, 12),
}

# Weight per category (total = 1.0)
CATEGORY_WEIGHTS = {
    "cpu": 0.20,
    "ram": 0.30,
    "disk": 0.10,
    "gpu": 0.10,
    "python": 0.10,
    "package": 0.20,
}


def _get_gpu_info() -> Tuple[bool, str, float]:
    """Try to detect GPU via nvidia-smi or torch."""
    # Try nvidia-smi
    nvidia_smi = shutil.which("nvidia-smi")
    if nvidia_smi:
        try:
            import subprocess
            result = subprocess.run(
                [nvidia_smi, "--query-gpu=name,memory.total", "--format=csv,noheader,nounits"],
                capture_output=True, text=True, timeout=5,
            )
            if result.returncode == 0 and result.stdout.strip():
                parts = result.stdout.strip().split(", ")
                name = parts[0].strip()
                vram_mb = float(parts[1].strip()) if len(parts) > 1 else 0
                return True, name, vram_mb / 1024
        except Exception:
            pass

    # Try torch
    try:
        import torch
        if torch.cuda.is_available():
            name = torch.cuda.get_device_name(0)
            vram_gb = torch.cuda.get_device_properties(0).total_memory / (1024**3)
            return True, name, vram_gb
    except Exception:
        pass

    return False, "", 0.0


def _check_packages() -> List[RequirementCheck]:
    """Check if key packages are installed."""
    key_packages = [
        ("numpy", "NumPy", "Core numerical computing"),
        ("pandas", "Pandas", "Data manipulation"),
        ("sklearn", "Scikit-learn", "ML models (RF, etc.)"),
        ("xgboost", "XGBoost", "Gradient boosting"),
        ("lightgbm", "LightGBM", "Gradient boosting"),
        ("streamlit", "Streamlit", "Web UI framework"),
        ("plotly", "Plotly", "Interactive charts"),
        ("yfinance", "yfinance", "Market data fetcher"),
        ("scipy", "SciPy", "Scientific computing"),
        ("joblib", "Joblib", "Model persistence"),
        ("torch", "PyTorch", "Transformer models (PatchTST/TFT) + GPU acceleration"),
        ("shap", "SHAP", "Model explainability"),
    ]

    checks = []
    for import_name, display_name, desc in key_packages:
        try:
            __import__(import_name)
            checks.append(RequirementCheck(
                name=display_name,
                category="package",
                current_value="Installed",
                required_value="Installed",
                recommended_value="Latest",
                passed=True,
                weight=0.1,
                score=1.0,
                notes=desc,
            ))
        except ImportError:
            checks.append(RequirementCheck(
                name=display_name,
                category="package",
                current_value="Missing",
                required_value="Installed",
                recommended_value="Latest",
                passed=False,
                weight=0.1,
                score=0.0,
                notes=f"Missing: {desc}",
            ))

    return checks


def check_system() -> SystemReport:
    """
    Run full system compatibility check.

    Returns SystemReport with all checks and overall score.
    """
    report = SystemReport()

    # OS info
    report.os_name = platform.system()
    report.os_version = platform.version()

    # Python version
    report.python_version = f"{sys.version_info.major}.{sys.version_info.minor}.{sys.version_info.micro}"

    # CPU info
    report.cpu_name = platform.processor() or "Unknown"
    report.cpu_cores = psutil.cpu_count(logical=False) or 1
    report.cpu_threads = psutil.cpu_count(logical=True) or 1
    freq = psutil.cpu_freq()
    report.cpu_freq_ghz = round(freq.max / 1000, 2) if freq else 0.0

    # RAM
    mem = psutil.virtual_memory()
    report.total_ram_gb = round(mem.total / (1024**3), 1)
    report.available_ram_gb = round(mem.available / (1024**3), 1)

    # Disk
    disk = psutil.disk_usage(os.getcwd())
    report.disk_total_gb = round(disk.total / (1024**3), 1)
    report.disk_free_gb = round(disk.free / (1024**3), 1)

    # GPU
    report.has_gpu, report.gpu_name, report.gpu_vram_gb = _get_gpu_info()

    checks = []

    # === CPU checks ===
    # Cores
    cores_score = min(report.cpu_cores / REQUIREMENTS["cpu_cores_recommended"], 1.0)
    checks.append(RequirementCheck(
        name="CPU Cores",
        category="cpu",
        current_value=f"{report.cpu_cores} cores ({report.cpu_threads} threads)",
        required_value=f">= {REQUIREMENTS['cpu_cores_min']}",
        recommended_value=f">= {REQUIREMENTS['cpu_cores_recommended']}",
        passed=report.cpu_cores >= REQUIREMENTS["cpu_cores_min"],
        weight=0.10,
        score=cores_score,
        notes="More cores = faster ML training & parallel feature engineering",
    ))

    # Frequency
    freq_score = min(report.cpu_freq_ghz / REQUIREMENTS["cpu_freq_recommended"], 1.0) if report.cpu_freq_ghz > 0 else 0.5
    checks.append(RequirementCheck(
        name="CPU Frequency",
        category="cpu",
        current_value=f"{report.cpu_freq_ghz} GHz",
        required_value=f">= {REQUIREMENTS['cpu_freq_min']} GHz",
        recommended_value=f">= {REQUIREMENTS['cpu_freq_recommended']} GHz",
        passed=report.cpu_freq_ghz >= REQUIREMENTS["cpu_freq_min"] or report.cpu_freq_ghz == 0,
        weight=0.10,
        score=freq_score,
        notes="Higher clock speed = faster single-threaded operations",
    ))

    # === RAM checks ===
    ram_score = min(report.total_ram_gb / REQUIREMENTS["ram_recommended_gb"], 1.0)
    checks.append(RequirementCheck(
        name="Total RAM",
        category="ram",
        current_value=f"{report.total_ram_gb} GB",
        required_value=f">= {REQUIREMENTS['ram_min_gb']} GB",
        recommended_value=f">= {REQUIREMENTS['ram_recommended_gb']} GB",
        passed=report.total_ram_gb >= REQUIREMENTS["ram_min_gb"],
        weight=0.15,
        score=ram_score,
        notes="ML training & feature engineering on 201 features requires significant RAM",
    ))

    avail_score = min(report.available_ram_gb / (REQUIREMENTS["ram_recommended_gb"] * 0.5), 1.0)
    checks.append(RequirementCheck(
        name="Available RAM",
        category="ram",
        current_value=f"{report.available_ram_gb} GB free",
        required_value=f">= {REQUIREMENTS['ram_min_gb'] * 0.5} GB free",
        recommended_value=f">= {REQUIREMENTS['ram_recommended_gb'] * 0.5} GB free",
        passed=report.available_ram_gb >= REQUIREMENTS["ram_min_gb"] * 0.5,
        weight=0.15,
        score=avail_score,
        notes="Free RAM available for the application to use right now",
    ))

    # === Disk checks ===
    disk_score = min(report.disk_free_gb / REQUIREMENTS["disk_free_recommended_gb"], 1.0)
    checks.append(RequirementCheck(
        name="Free Disk Space",
        category="disk",
        current_value=f"{report.disk_free_gb} GB free",
        required_value=f">= {REQUIREMENTS['disk_free_min_gb']} GB",
        recommended_value=f">= {REQUIREMENTS['disk_free_recommended_gb']} GB",
        passed=report.disk_free_gb >= REQUIREMENTS["disk_free_min_gb"],
        weight=0.10,
        score=disk_score,
        notes="For database, model artifacts, logs, and MLflow runs",
    ))

    # === GPU checks ===
    if report.has_gpu:
        gpu_score = min(report.gpu_vram_gb / 8.0, 1.0)
        checks.append(RequirementCheck(
            name="GPU",
            category="gpu",
            current_value=f"{report.gpu_name} ({report.gpu_vram_gb:.1f} GB VRAM)",
            required_value="Optional (CPU works)",
            recommended_value="NVIDIA GPU with >= 8 GB VRAM",
            passed=True,
            weight=0.10,
            score=gpu_score,
            notes="GPU accelerates transformer models & deep learning (optional but recommended)",
        ))
    else:
        checks.append(RequirementCheck(
            name="GPU",
            category="gpu",
            current_value="Not detected",
            required_value="Optional (CPU works)",
            recommended_value="NVIDIA GPU with >= 8 GB VRAM",
            passed=True,  # Optional
            weight=0.10,
            score=0.3,  # Partial credit — app works but slower for DL
            notes="No GPU detected. App runs on CPU but transformer models will be slower",
        ))

    # === Python version ===
    py_ver = (sys.version_info.major, sys.version_info.minor)
    py_score = min(py_ver[1] / REQUIREMENTS["python_recommended"][1], 1.0)
    checks.append(RequirementCheck(
        name="Python Version",
        category="python",
        current_value=report.python_version,
        required_value=f">= {REQUIREMENTS['python_min'][0]}.{REQUIREMENTS['python_min'][1]}",
        recommended_value=f">= {REQUIREMENTS['python_recommended'][0]}.{REQUIREMENTS['python_recommended'][1]}",
        passed=py_ver >= REQUIREMENTS["python_min"],
        weight=0.10,
        score=py_score,
        notes="Python version compatibility for all packages",
    ))

    # === Packages ===
    checks.extend(_check_packages())

    report.checks = checks

    # Calculate weighted overall score
    total_weight = sum(c.weight for c in checks)
    weighted_sum = sum(c.score * c.weight for c in checks)
    report.overall_score = round(weighted_sum / total_weight if total_weight > 0 else 0, 3)
    report.compatibility_pct = round(report.overall_score * 100, 1)

    # Verdict
    if report.compatibility_pct >= 85:
        report.verdict = "Sangat Cocok — Komputer ideal untuk menjalankan aplikasi ini"
    elif report.compatibility_pct >= 70:
        report.verdict = "Cocok — Komputer memenuhi syarat dengan baik"
    elif report.compatibility_pct >= 50:
        report.verdict = "Cukup Cocok — Beberapa fitur mungkin lambat"
    else:
        report.verdict = "Kurang Cocok — Upgrade hardware direkomendasikan"

    # Recommendations
    for c in checks:
        if not c.passed:
            report.recommendations.append(f"⚠️ {c.name}: {c.notes}")
        elif c.score < 0.6 and c.category in ("cpu", "ram", "gpu"):
            report.recommendations.append(f"💡 {c.name}: Upgrade untuk performa optimal ({c.current_value} → {c.recommended_value})")

    if not report.recommendations:
        report.recommendations.append("✅ Semua syarat terpenuhi. Komputer siap menjalankan aplikasi.")

    return report
