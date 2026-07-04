"""
Lazy import utilities to reduce module load time.

Heavy modules (torch, shap, sklearn, xgboost, lightgbm) are only loaded
when first accessed, not at import time. This cuts predictor.py import
from ~18s to ~2s when only prediction is needed (no training/explainability).
"""

import importlib
import sys
from typing import Any, Optional


class LazyModule:
    """Proxy that defers actual import until first attribute access."""

    __slots__ = ("_module_name", "_module", "_local_name")

    def __init__(self, module_name: str, local_name: Optional[str] = None):
        object.__setattr__(self, "_module_name", module_name)
        object.__setattr__(self, "_module", None)
        object.__setattr__(self, "_local_name", local_name or module_name)

    def _load(self):
        if object.__getattribute__(self, "_module") is None:
            name = object.__getattribute__(self, "_module_name")
            mod = importlib.import_module(name)
            object.__setattr__(self, "_module", mod)
        return object.__getattribute__(self, "_module")

    def __getattr__(self, name: str) -> Any:
        mod = self._load()
        return getattr(mod, name)

    def __setattr__(self, name: str, value: Any):
        mod = self._load()
        setattr(mod, name, value)

    def __repr__(self):
        loaded = object.__getattribute__(self, "_module")
        if loaded is not None:
            return f"<LazyModule '{self._module_name}' (loaded)>"
        return f"<LazyModule '{self._module_name}' (lazy)>"

    def __bool__(self):
        try:
            self._load()
            return True
        except ImportError:
            return False


class LazyAttr:
    """Proxy that defers importing a specific attribute from a module."""

    __slots__ = ("_module_name", "_attr_name", "_value", "_loaded")

    def __init__(self, module_name: str, attr_name: str):
        object.__setattr__(self, "_module_name", module_name)
        object.__setattr__(self, "_attr_name", attr_name)
        object.__setattr__(self, "_value", None)
        object.__setattr__(self, "_loaded", False)

    def _load(self):
        if not object.__getattribute__(self, "_loaded"):
            mod = importlib.import_module(object.__getattribute__(self, "_module_name"))
            val = getattr(mod, object.__getattribute__(self, "_attr_name"))
            object.__setattr__(self, "_value", val)
            object.__setattr__(self, "_loaded", True)
        return object.__getattribute__(self, "_value")

    def __call__(self, *args, **kwargs):
        return self._load()(*args, **kwargs)

    def __getattr__(self, name: str) -> Any:
        return getattr(self._load(), name)

    def __repr__(self):
        loaded = object.__getattribute__(self, "_loaded")
        if loaded:
            return f"<LazyAttr '{self._module_name}.{self._attr_name}' (loaded)>"
        return f"<LazyAttr '{self._module_name}.{self._attr_name}' (lazy)>"


def is_loaded(module_name: str) -> bool:
    """Check if a module has been imported (exists in sys.modules)."""
    return module_name in sys.modules


def get_import_times() -> dict:
    """Return dict of module_name -> import time for heavy modules."""
    heavy = [
        "numpy", "pandas", "sklearn", "scipy",
        "xgboost", "lightgbm", "torch", "shap",
        "matplotlib", "statsmodels", "optuna",
    ]
    return {m: "loaded" if m in sys.modules else "lazy" for m in heavy}
