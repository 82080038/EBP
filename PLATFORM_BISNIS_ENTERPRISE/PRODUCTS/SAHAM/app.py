#!/usr/bin/env python3
"""
Thin wrapper untuk Streamlit dashboard.
Actual code: src/app.py

Run: streamlit run app.py --server.port 8501
     streamlit run src/app.py --server.port 8501
"""

import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from src.app import *  # noqa: F401,F403
