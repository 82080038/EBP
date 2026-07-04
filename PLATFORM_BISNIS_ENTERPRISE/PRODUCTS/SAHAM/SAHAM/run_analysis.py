#!/usr/bin/env python3
"""
Thin wrapper untuk CLI analisis harian.
Actual code: src/run_analysis.py

Usage:
    python run_analysis.py              # = python -m src.run_analysis
    python run_analysis.py --predict
    python run_analysis.py --verify
    python run_analysis.py --backtest
    python run_analysis.py --all
"""

import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from src.run_analysis import main

if __name__ == "__main__":
    main()
