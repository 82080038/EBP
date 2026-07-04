"""
Tests for src/model_registry.py — lightweight model versioning layer.
"""

import os
import tempfile
import pytest
import pandas as pd

from src import model_registry
from src.model_registry import save_model, load_model, list_models, get_latest_model, verify_model, ModelMetadata


@pytest.fixture
def temp_registry(monkeypatch):
    with tempfile.TemporaryDirectory() as tmpdir:
        monkeypatch.setattr(model_registry, "REGISTRY_DIR", tmpdir)
        yield tmpdir


class FakeModel:
    def __init__(self, coef):
        self.coef = coef

    def predict(self, x):
        return x * self.coef


class TestModelRegistry:
    def test_save_and_load(self, temp_registry):
        model = FakeModel(coef=2.0)
        path, meta = save_model(
            model,
            ticker="IHSG",
            model_name="hybrid_ensemble",
            metrics={"accuracy": 0.55},
            tags=["test"],
            notes="test model",
        )
        assert os.path.exists(path)
        assert meta.ticker == "IHSG"
        assert meta.model_name == "hybrid_ensemble"
        assert meta.metrics["accuracy"] == 0.55

        loaded = load_model("IHSG", "hybrid_ensemble")
        assert loaded.coef == 2.0

    def test_list_models(self, temp_registry):
        save_model(FakeModel(1.0), ticker="IHSG", model_name="m1")
        save_model(FakeModel(2.0), ticker="IHSG", model_name="m2")
        save_model(FakeModel(3.0), ticker="BBCA", model_name="m1")

        all_models = list_models()
        assert len(all_models) == 3

        ihsg_models = list_models(ticker="IHSG")
        assert len(ihsg_models) == 2

    def test_get_latest_model(self, temp_registry):
        save_model(FakeModel(1.0), ticker="IHSG", model_name="hybrid_ensemble", version="v1")
        save_model(FakeModel(2.0), ticker="IHSG", model_name="hybrid_ensemble", version="v2")

        latest = get_latest_model("IHSG", "hybrid_ensemble")
        assert latest is not None
        path, meta = latest
        assert meta.version == "v2"
        loaded = load_model("IHSG", "hybrid_ensemble")
        assert loaded.coef == 2.0

    def test_verify_model(self, temp_registry):
        save_model(FakeModel(1.0), ticker="IHSG", model_name="hybrid_ensemble", version="v1")
        assert verify_model("IHSG", "hybrid_ensemble", version="v1") is True

    def test_load_missing(self, temp_registry):
        with pytest.raises(FileNotFoundError):
            load_model("IHSG", "missing_model")


class TestModelMetadata:
    def test_roundtrip(self):
        meta = ModelMetadata(
            ticker="IHSG",
            model_name="hybrid_ensemble",
            version="v1",
            created_at="2026-06-25T00:00:00+00:00",
            file_hash="abc",
            file_size=123,
            metrics={"accuracy": 0.55},
        )
        data = meta.to_dict()
        restored = ModelMetadata.from_dict(data)
        assert restored.ticker == "IHSG"
        assert restored.metrics["accuracy"] == 0.55
