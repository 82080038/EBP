"""
Structured Logging Framework.

Centralized logging configuration with:
- Standard format: timestamp, level, module, message
- File rotation (10MB per file, 5 backups)
- Console + file handlers
- Configurable log level via environment variable

Usage:
    # In app.py or any entry point:
    from src.logging_config import setup_logging
    setup_logging()

    # In any module:
    import logging
    logger = logging.getLogger(__name__)
    logger.info("Message")
    logger.warning("Warning")
    logger.error("Error")
"""
from __future__ import annotations

import logging
import os
import sys
from logging.handlers import RotatingFileHandler
from pathlib import Path

LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO").upper()
LOG_DIR = os.getenv("LOG_DIR", os.path.join(os.path.dirname(__file__), "data", "logs"))
LOG_FORMAT = "%(asctime)s | %(levelname)-8s | %(name)-25s | %(message)s"
DATE_FORMAT = "%Y-%m-%d %H:%M:%S"

_configured = False


def setup_logging(level: str = None, log_to_file: bool = True) -> logging.Logger:
    """
    Configure structured logging for the entire application.

    Args:
        level: Log level (DEBUG, INFO, WARNING, ERROR, CRITICAL)
        log_to_file: Also log to rotating file

    Returns:
        Root logger
    """
    global _configured

    if _configured:
        return logging.getLogger()

    log_level = level or LOG_LEVEL
    numeric_level = getattr(logging, log_level, logging.INFO)

    # Create log directory
    Path(LOG_DIR).mkdir(parents=True, exist_ok=True)

    # Root logger
    root_logger = logging.getLogger()
    root_logger.setLevel(numeric_level)

    # Remove existing handlers
    root_logger.handlers.clear()

    # Console handler
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setLevel(numeric_level)
    console_handler.setFormatter(logging.Formatter(LOG_FORMAT, DATE_FORMAT))
    root_logger.addHandler(console_handler)

    # File handler with rotation
    if log_to_file:
        log_file = os.path.join(LOG_DIR, "saham_app.log")
        file_handler = RotatingFileHandler(
            log_file,
            maxBytes=10 * 1024 * 1024,  # 10MB
            backupCount=5,
            encoding="utf-8",
        )
        file_handler.setLevel(numeric_level)
        file_handler.setFormatter(logging.Formatter(LOG_FORMAT, DATE_FORMAT))
        root_logger.addHandler(file_handler)

    # Reduce noise from third-party libraries
    logging.getLogger("yfinance").setLevel(logging.CRITICAL)
    logging.getLogger("urllib3").setLevel(logging.WARNING)
    logging.getLogger("requests").setLevel(logging.WARNING)
    logging.getLogger("matplotlib").setLevel(logging.WARNING)
    logging.getLogger("PIL").setLevel(logging.WARNING)

    _configured = True
    root_logger.info(f"Logging configured: level={log_level}, file={log_to_file}")
    return root_logger


def get_logger(name: str) -> logging.Logger:
    """Get a logger with the given name. Ensures logging is configured."""
    if not _configured:
        setup_logging()
    return logging.getLogger(name)
