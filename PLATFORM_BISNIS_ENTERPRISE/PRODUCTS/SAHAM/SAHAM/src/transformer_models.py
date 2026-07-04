"""
Transformer models for time-series forecasting: PatchTST and TFT.

Implements patch-based tokenization (PatchTST) and a simplified
Temporal Fusion Transformer (TFT) with fallback to sklearn-based
models when PyTorch is not available.

References:
- PatchTST: "A Time Series is Worth 64 Words" (arXiv:2211.14730)
- TFT: "Temporal Fusion Transformers for Interpretable Multi-horizon Time Series Forecasting"
- LPatchTST: LSTM + PatchTST hybrid (Sharpe ratio 2.31)
"""
from __future__ import annotations

import logging
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import StandardScaler

logger = logging.getLogger(__name__)

TRY_TORCH = True
try:
    import torch
    import torch.nn as nn
    import torch.optim as optim
    from torch.utils.data import Dataset, DataLoader

    HAS_TORCH = True
    if torch.cuda.is_available():
        _gpu_count = torch.cuda.device_count()

        # Use GPU 1 for transformers if available (leave GPU 0 for LightGBM)
        _target_gpu = 1 if _gpu_count > 1 else 0
        TORCH_DEVICE = torch.device(f"cuda:{_target_gpu}")
        _gpu_name = torch.cuda.get_device_name(_target_gpu)
        _gpu_vram = torch.cuda.get_device_properties(_target_gpu).total_memory / (1024**3)
        logger.info(
            f"PyTorch CUDA available — {_gpu_count} GPU(s) detected. "
            f"Using GPU {_target_gpu}: {_gpu_name} ({_gpu_vram:.1f} GB) for transformer models"
        )

        # Enable cuDNN benchmark for faster training with fixed input sizes
        torch.backends.cudnn.benchmark = True
        # Enable TF32 for faster matmul on Ampere+ (no-op on Pascal, but safe)
        torch.backends.cuda.matmul.allow_tf32 = True
        torch.backends.cudnn.allow_tf32 = True
    else:
        TORCH_DEVICE = torch.device("cpu")
        logger.info("PyTorch available — using CPU (no CUDA detected)")
except ImportError:
    HAS_TORCH = False
    TORCH_DEVICE = None
    logger.warning("PyTorch not available — using sklearn fallback for transformer models")


# =============================================================================
# DATA CLASSES
# =============================================================================


@dataclass
class TransformerConfig:
    """Configuration for transformer models."""
    # PatchTST
    patch_len: int = 16
    stride: int = 8
    d_model: int = 64
    n_heads: int = 4
    n_layers: int = 2
    dropout: float = 0.1
    # TFT
    hidden_size: int = 64
    lstm_layers: int = 1
    num_heads: int = 4
    # Training
    epochs: int = 50
    batch_size: int = 32
    learning_rate: float = 1e-3
    weight_decay: float = 1e-4
    patience: int = 10
    # Data
    seq_len: int = 64
    pred_len: int = 1
    features: List[str] = field(default_factory=lambda: ["Close", "Volume", "RSI", "MACD"])
    target: str = "Close"


@dataclass
class TransformerResult:
    """Result from transformer model training/prediction."""
    model_type: str = ""
    predictions: np.ndarray = None
    actual: np.ndarray = None
    train_loss: List[float] = field(default_factory=list)
    val_loss: List[float] = field(default_factory=list)
    feature_importance: Dict[str, float] = field(default_factory=dict)
    attention_weights: Optional[np.ndarray] = None
    metrics: Dict[str, float] = field(default_factory=dict)
    trained: bool = False
    error: str = ""


# =============================================================================
# PYTORCH MODELS (only if torch available)
# =============================================================================

if HAS_TORCH:

    class PatchTSTModel(nn.Module):
        """PatchTST: Patch Tokenization for Time Series Forecasting."""

        def __init__(
            self,
            seq_len: int = 64,
            pred_len: int = 1,
            patch_len: int = 16,
            stride: int = 8,
            d_model: int = 64,
            n_heads: int = 4,
            n_layers: int = 2,
            dropout: float = 0.1,
            n_features: int = 1,
        ):
            super().__init__()
            self.seq_len = seq_len
            self.pred_len = pred_len
            self.patch_len = patch_len
            self.stride = stride
            self.n_features = n_features

            # Calculate number of patches
            self.n_patches = (seq_len - patch_len) // stride + 1

            # Patch embedding: linear projection of each patch
            self.patch_embedding = nn.Linear(patch_len, d_model)

            # Positional encoding
            self.pos_encoding = nn.Parameter(torch.randn(1, self.n_patches * n_features, d_model))

            # Transformer encoder
            encoder_layer = nn.TransformerEncoderLayer(
                d_model=d_model,
                nhead=n_heads,
                dim_feedforward=d_model * 4,
                dropout=dropout,
                batch_first=True,
            )
            self.transformer = nn.TransformerEncoder(encoder_layer, n_layers)

            # Output head
            self.flatten = nn.Flatten(start_dim=1)
            self.head = nn.Sequential(
                nn.Linear(self.n_patches * n_features * d_model, d_model),
                nn.ReLU(),
                nn.Dropout(dropout),
                nn.Linear(d_model, pred_len),
            )

        def forward(self, x: torch.Tensor) -> torch.Tensor:
            """
            x: (batch, seq_len, n_features)
            returns: (batch, pred_len)
            """
            x.shape[0]
            patches_list = []
            for f in range(self.n_features):
                x_f = x[:, :, f]  # (batch, seq_len)
                patches = []
                for i in range(self.n_patches):
                    start = i * self.stride
                    patch = x_f[:, start: start + self.patch_len]
                    patches.append(patch)
                patches = torch.stack(patches, dim=1)  # (batch, n_patches, patch_len)
                patches = self.patch_embedding(patches)  # (batch, n_patches, d_model)
                patches_list.append(patches)

            # Concatenate all features' patches
            all_patches = torch.cat(patches_list, dim=1)  # (batch, n_patches*n_features, d_model)
            all_patches = all_patches + self.pos_encoding

            # Transformer
            out = self.transformer(all_patches)
            out = self.flatten(out)
            out = self.head(out)
            return out

    class TFTModel(nn.Module):
        """Simplified Temporal Fusion Transformer."""

        def __init__(
            self,
            seq_len: int = 64,
            pred_len: int = 1,
            hidden_size: int = 64,
            lstm_layers: int = 1,
            num_heads: int = 4,
            dropout: float = 0.1,
            n_features: int = 1,
        ):
            super().__init__()
            self.seq_len = seq_len
            self.pred_len = pred_len
            self.hidden_size = hidden_size
            self.n_features = n_features

            # Input projection
            self.input_proj = nn.Linear(n_features, hidden_size)

            # LSTM for local processing
            self.lstm = nn.LSTM(
                input_size=hidden_size,
                hidden_size=hidden_size,
                num_layers=lstm_layers,
                batch_first=True,
                dropout=dropout if lstm_layers > 1 else 0,
            )

            # Self-attention (interpretable)
            self.attention = nn.MultiheadAttention(
                embed_dim=hidden_size,
                num_heads=num_heads,
                dropout=dropout,
                batch_first=True,
            )

            # Gated residual network components
            self.gate = nn.Sequential(
                nn.Linear(hidden_size, hidden_size),
                nn.Sigmoid(),
            )
            self.fc = nn.Sequential(
                nn.Linear(hidden_size, hidden_size),
                nn.ReLU(),
                nn.Dropout(dropout),
                nn.Linear(hidden_size, hidden_size),
            )
            self.layer_norm = nn.LayerNorm(hidden_size)

            # Output
            self.output_proj = nn.Sequential(
                nn.Linear(hidden_size * seq_len, hidden_size),
                nn.ReLU(),
                nn.Dropout(dropout),
                nn.Linear(hidden_size, pred_len),
            )

        def forward(self, x: torch.Tensor) -> Tuple[torch.Tensor, Optional[torch.Tensor]]:
            """
            x: (batch, seq_len, n_features)
            returns: (prediction, attention_weights)
            """
            # Input projection
            h = self.input_proj(x)  # (batch, seq_len, hidden)

            # LSTM
            h_lstm, _ = self.lstm(h)  # (batch, seq_len, hidden)

            # Self-attention
            attn_out, attn_weights = self.attention(h_lstm, h_lstm, h_lstm)

            # Gated residual connection
            gate = self.gate(attn_out)
            transformed = self.fc(attn_out)
            h_out = self.layer_norm(h_lstm + gate * transformed)

            # Output
            h_flat = h_out.flatten(1)
            pred = self.output_proj(h_flat)
            return pred, attn_weights

    class TimeSeriesDataset(Dataset):
        """Sliding window dataset for time-series."""

        def __init__(self, data: np.ndarray, seq_len: int, pred_len: int):
            self.data = data
            self.seq_len = seq_len
            self.pred_len = pred_len

        def __len__(self):
            return max(0, len(self.data) - self.seq_len - self.pred_len + 1)

        def __getitem__(self, idx):
            x = self.data[idx: idx + self.seq_len]
            y = self.data[idx + self.seq_len: idx + self.seq_len + self.pred_len, 0]  # target = first feature
            return torch.FloatTensor(x), torch.FloatTensor(y)


# =============================================================================
# SKLEARN FALLBACK MODELS
# =============================================================================


class SklearnFallback:
    """Sklearn-based fallback when PyTorch is not available."""

    def __init__(self, config: TransformerConfig):
        self.config = config
        self.scaler = StandardScaler()
        self.model = RandomForestRegressor(
            n_estimators=100,
            max_depth=10,
            random_state=42,
        )
        self.feature_names: List[str] = []

    def _create_features(self, df: pd.DataFrame) -> pd.DataFrame:
        """Create lagged features from time series."""
        features = pd.DataFrame(index=df.index)
        target = self.config.target

        # Lagged target
        for lag in [1, 2, 3, 5, 10, 20]:
            if target in df.columns:
                features[f"{target}_lag_{lag}"] = df[target].shift(lag)

        # Rolling statistics
        if target in df.columns:
            for window in [5, 10, 20]:
                features[f"{target}_ma_{window}"] = df[target].rolling(window).mean()
                features[f"{target}_std_{window}"] = df[target].rolling(window).std()
                features[f"{target}_min_{window}"] = df[target].rolling(window).min()
                features[f"{target}_max_{window}"] = df[target].rolling(window).max()

        # Returns
        if target in df.columns:
            features["return_1d"] = df[target].pct_change(1)
            features["return_5d"] = df[target].pct_change(5)
            features["return_10d"] = df[target].pct_change(10)

        # RSI
        if target in df.columns:
            delta = df[target].diff()
            gain = delta.clip(lower=0).rolling(14).mean()
            loss = (-delta.clip(upper=0)).rolling(14).mean()
            rs = gain / (loss + 1e-10)
            features["RSI"] = 100 - (100 / (1 + rs))

        # MACD
        if target in df.columns:
            ema12 = df[target].ewm(span=12).mean()
            ema26 = df[target].ewm(span=26).mean()
            features["MACD"] = ema12 - ema26
            features["MACD_signal"] = features["MACD"].ewm(span=9).mean()

        # Volume features
        if "Volume" in df.columns:
            features["Volume_ma_5"] = df["Volume"].rolling(5).mean()
            features["Volume_ratio"] = df["Volume"] / (features["Volume_ma_5"] + 1e-10)

        return features

    def fit(self, df: pd.DataFrame) -> Dict:
        features = self._create_features(df)
        target = self.config.target

        # Shift target for prediction
        y = df[target].shift(-self.config.pred_len)

        # Align
        data = pd.concat([features, y.rename("target")], axis=1).dropna()
        if len(data) < 50:
            return {"train_loss": [0], "val_loss": [0], "error": "Insufficient data"}

        X = data.drop(columns=["target"]).values
        y_arr = data["target"].values

        # Split
        split = int(len(X) * 0.8)
        X_train, X_val = X[:split], X[split:]
        y_train, y_val = y_arr[:split], y_arr[split:]

        X_train = self.scaler.fit_transform(X_train)
        X_val = self.scaler.transform(X_val)

        self.model.fit(X_train, y_train)
        self.feature_names = list(data.drop(columns=["target"]).columns)

        # Metrics
        train_pred = self.model.predict(X_train)
        val_pred = self.model.predict(X_val)

        train_mse = float(np.mean((train_pred - y_train) ** 2))
        val_mse = float(np.mean((val_pred - y_val) ** 2))

        return {
            "train_loss": [train_mse],
            "val_loss": [val_mse],
            "feature_importance": dict(zip(self.feature_names, self.model.feature_importances_)),
        }

    def predict(self, df: pd.DataFrame) -> np.ndarray:
        features = self._create_features(df)
        X = features.iloc[-1:].values
        X = self.scaler.transform(X)
        return self.model.predict(X)


# =============================================================================
# MAIN API
# =============================================================================


def train_patchtst(
    df: pd.DataFrame,
    config: Optional[TransformerConfig] = None,
) -> TransformerResult:
    """
    Train PatchTST model on time-series data.

    Args:
        df: DataFrame with OHLCV + indicator columns
        config: Model configuration

    Returns:
        TransformerResult with predictions and metrics
    """
    if config is None:
        config = TransformerConfig()

    result = TransformerResult(model_type="PatchTST")

    # Prepare data
    feature_cols = [c for c in config.features if c in df.columns]
    if not feature_cols:
        feature_cols = [c for c in ["Close", "Volume"] if c in df.columns]
    if not feature_cols:
        result.error = "No valid feature columns found"
        return result

    data = df[feature_cols].dropna().values
    if len(data) < config.seq_len + config.pred_len + 20:
        result.error = f"Insufficient data: {len(data)} rows, need at least {config.seq_len + config.pred_len + 20}"
        return result

    # Normalize
    scaler = StandardScaler()
    data_scaled = scaler.fit_transform(data)

    if HAS_TORCH:
        try:
            result = _train_torch_model(
                data_scaled, config, result, model_type="patchtst",
                scaler=scaler, feature_cols=feature_cols,
            )
        except Exception as e:
            logger.warning(f"PatchTST PyTorch training failed: {e}, falling back to sklearn")
            result = _train_sklearn_fallback(df, config, result, "PatchTST")
    else:
        result = _train_sklearn_fallback(df, config, result, "PatchTST")

    return result


def train_tft(
    df: pd.DataFrame,
    config: Optional[TransformerConfig] = None,
) -> TransformerResult:
    """
    Train Temporal Fusion Transformer model.

    Args:
        df: DataFrame with OHLCV + indicator columns
        config: Model configuration

    Returns:
        TransformerResult with predictions, attention weights, and metrics
    """
    if config is None:
        config = TransformerConfig()

    result = TransformerResult(model_type="TFT")

    feature_cols = [c for c in config.features if c in df.columns]
    if not feature_cols:
        feature_cols = [c for c in ["Close", "Volume"] if c in df.columns]
    if not feature_cols:
        result.error = "No valid feature columns found"
        return result

    data = df[feature_cols].dropna().values
    if len(data) < config.seq_len + config.pred_len + 20:
        result.error = f"Insufficient data: {len(data)} rows"
        return result

    scaler = StandardScaler()
    data_scaled = scaler.fit_transform(data)

    if HAS_TORCH:
        try:
            result = _train_torch_model(
                data_scaled, config, result, model_type="tft",
                scaler=scaler, feature_cols=feature_cols,
            )
        except Exception as e:
            logger.warning(f"TFT PyTorch training failed: {e}, falling back to sklearn")
            result = _train_sklearn_fallback(df, config, result, "TFT")
    else:
        result = _train_sklearn_fallback(df, config, result, "TFT")

    return result


def train_lstm_patchtst_hybrid(
    df: pd.DataFrame,
    config: Optional[TransformerConfig] = None,
) -> TransformerResult:
    """
    Train LPatchTST: LSTM + PatchTST hybrid model.
    Reference: Sharpe ratio 2.31 vs 0.76 PatchTST pure.

    Args:
        df: DataFrame with OHLCV + indicator columns
        config: Model configuration

    Returns:
        TransformerResult
    """
    if config is None:
        config = TransformerConfig()

    result = TransformerResult(model_type="LPatchTST")

    feature_cols = [c for c in config.features if c in df.columns]
    if not feature_cols:
        feature_cols = [c for c in ["Close", "Volume"] if c in df.columns]
    if not feature_cols:
        result.error = "No valid feature columns found"
        return result

    data = df[feature_cols].dropna().values
    if len(data) < config.seq_len + config.pred_len + 20:
        result.error = f"Insufficient data: {len(data)} rows"
        return result

    scaler = StandardScaler()
    data_scaled = scaler.fit_transform(data)

    if HAS_TORCH:
        try:
            # LPatchTST = LSTM preprocessing + PatchTST transformer
            result = _train_torch_model(
                data_scaled, config, result, model_type="lpatchtst",
                scaler=scaler, feature_cols=feature_cols,
            )
        except Exception as e:
            logger.warning(f"LPatchTST training failed: {e}, falling back to sklearn")
            result = _train_sklearn_fallback(df, config, result, "LPatchTST")
    else:
        result = _train_sklearn_fallback(df, config, result, "LPatchTST")

    return result


# =============================================================================
# INTERNAL TRAINING FUNCTIONS
# =============================================================================


def _train_torch_model(
    data: np.ndarray,
    config: TransformerConfig,
    result: TransformerResult,
    model_type: str,
    scaler: StandardScaler,
    feature_cols: List[str],
) -> TransformerResult:
    """Train a PyTorch transformer model."""
    n_features = data.shape[1]

    # Create dataset
    dataset = TimeSeriesDataset(data, config.seq_len, config.pred_len)
    if len(dataset) < 20:
        result.error = "Insufficient samples after windowing"
        return result

    # Train/val split
    split = int(len(dataset) * 0.8)
    train_ds = torch.utils.data.Subset(dataset, range(split))
    val_ds = torch.utils.data.Subset(dataset, range(split, len(dataset)))

    train_loader = DataLoader(train_ds, batch_size=config.batch_size, shuffle=True)
    val_loader = DataLoader(val_ds, batch_size=config.batch_size, shuffle=False)

    # Create model
    if model_type == "patchtst":
        model = PatchTSTModel(
            seq_len=config.seq_len,
            pred_len=config.pred_len,
            patch_len=config.patch_len,
            stride=config.stride,
            d_model=config.d_model,
            n_heads=config.n_heads,
            n_layers=config.n_layers,
            dropout=config.dropout,
            n_features=n_features,
        )
    elif model_type == "tft":
        model = TFTModel(
            seq_len=config.seq_len,
            pred_len=config.pred_len,
            hidden_size=config.hidden_size,
            lstm_layers=config.lstm_layers,
            num_heads=config.num_heads,
            dropout=config.dropout,
            n_features=n_features,
        )
    elif model_type == "lpatchtst":
        # Hybrid: LSTM + PatchTST
        class LPatchTSTHybrid(nn.Module):
            def __init__(self, seq_len, pred_len, patch_len, stride, d_model,
                         n_heads, n_layers, dropout, n_features, hidden_size):
                super().__init__()
                self.lstm = nn.LSTM(n_features, hidden_size, batch_first=True, num_layers=1)
                self.patchtst = PatchTSTModel(
                    seq_len=seq_len, pred_len=pred_len, patch_len=patch_len,
                    stride=stride, d_model=d_model, n_heads=n_heads,
                    n_layers=n_layers, dropout=dropout, n_features=hidden_size,
                )
            def forward(self, x):
                h, _ = self.lstm(x)
                return self.patchtst(h)

        model = LPatchTSTHybrid(
            seq_len=config.seq_len, pred_len=config.pred_len,
            patch_len=config.patch_len, stride=config.stride,
            d_model=config.d_model, n_heads=config.n_heads,
            n_layers=config.n_layers, dropout=config.dropout,
            n_features=n_features, hidden_size=config.hidden_size,
        )
    else:
        result.error = f"Unknown model type: {model_type}"
        return result

    # Training
    model = model.to(TORCH_DEVICE)
    optimizer = optim.Adam(model.parameters(), lr=config.learning_rate, weight_decay=config.weight_decay)
    criterion = nn.MSELoss()

    # Mixed precision training (AMP) — ~2x faster on GPU with minimal accuracy loss
    _use_amp = HAS_TORCH and TORCH_DEVICE.type == "cuda"
    _scaler = torch.amp.GradScaler() if _use_amp else None

    # Pin memory for faster CPU→GPU transfer
    for loader in [train_loader, val_loader]:
        if hasattr(loader, 'pin_memory'):
            loader.pin_memory = True if _use_amp else False

    train_losses = []
    val_losses = []
    best_val_loss = float("inf")
    patience_counter = 0
    best_state = None

    for epoch in range(config.epochs):
        # Train
        model.train()
        epoch_loss = 0
        for batch_x, batch_y in train_loader:
            batch_x = batch_x.to(TORCH_DEVICE, non_blocking=True)
            batch_y = batch_y.to(TORCH_DEVICE, non_blocking=True)
            optimizer.zero_grad()
            if _use_amp:
                with torch.amp.autocast('cuda'):
                    pred = model(batch_x)
                    if isinstance(pred, tuple):
                        pred = pred[0]
                    loss = criterion(pred.squeeze(), batch_y)
                _scaler.scale(loss).backward()
                _scaler.step(optimizer)
                _scaler.update()
            else:
                pred = model(batch_x)
                if isinstance(pred, tuple):
                    pred = pred[0]
                loss = criterion(pred.squeeze(), batch_y)
                loss.backward()
                optimizer.step()
            epoch_loss += loss.item()
        train_losses.append(epoch_loss / len(train_loader))

        # Validate
        model.eval()
        val_loss = 0
        all_preds = []
        all_actuals = []
        attn_weights_list = []
        with torch.no_grad():
            for batch_x, batch_y in val_loader:
                batch_x = batch_x.to(TORCH_DEVICE, non_blocking=True)
                batch_y = batch_y.to(TORCH_DEVICE, non_blocking=True)
                if _use_amp:
                    with torch.amp.autocast('cuda'):
                        output = model(batch_x)
                else:
                    output = model(batch_x)
                if isinstance(output, tuple):
                    pred, attn = output
                    if attn is not None:
                        attn_weights_list.append(attn.cpu().numpy())
                else:
                    pred = output
                loss = criterion(pred.squeeze(), batch_y)
                val_loss += loss.item()
                all_preds.append(pred.squeeze().cpu().numpy())
                all_actuals.append(batch_y.cpu().numpy())

        val_loss /= max(len(val_loader), 1)
        val_losses.append(val_loss)

        # Early stopping
        if val_loss < best_val_loss:
            best_val_loss = val_loss
            patience_counter = 0
            best_state = {k: v.clone() for k, v in model.state_dict().items()}
        else:
            patience_counter += 1
            if patience_counter >= config.patience:
                logger.info(f"Early stopping at epoch {epoch+1}")
                break

    # Load best model
    if best_state is not None:
        model.load_state_dict(best_state)

    # Final predictions
    model.eval()
    all_preds = []
    all_actuals = []
    with torch.no_grad():
        for batch_x, batch_y in val_loader:
            batch_x = batch_x.to(TORCH_DEVICE)
            batch_y = batch_y.to(TORCH_DEVICE)
            output = model(batch_x)
            if isinstance(output, tuple):
                pred = output[0]
            else:
                pred = output
            all_preds.append(pred.squeeze().cpu().numpy())
            all_actuals.append(batch_y.cpu().numpy())

    predictions = np.concatenate(all_preds) if all_preds else np.array([])
    actuals = np.concatenate(all_actuals) if all_actuals else np.array([])

    # Metrics
    metrics = {}
    if len(predictions) > 0 and len(actuals) > 0:
        metrics["mse"] = float(np.mean((predictions - actuals) ** 2))
        metrics["mae"] = float(np.mean(np.abs(predictions - actuals)))
        if np.std(actuals) > 0:
            metrics["r2"] = float(1 - np.sum((actuals - predictions) ** 2) / np.sum((actuals - actuals.mean()) ** 2))
        # Directional accuracy
        if len(predictions) > 1:
            pred_dir = np.diff(predictions) > 0
            actual_dir = np.diff(actuals) > 0
            metrics["directional_accuracy"] = float(np.mean(pred_dir == actual_dir))

    # Attention weights for TFT
    attn_weights = None
    if model_type == "tft" and attn_weights_list:
        attn_weights = attn_weights_list[-1]

    result.predictions = predictions
    result.actual = actuals
    result.train_loss = train_losses
    result.val_loss = val_losses
    result.metrics = metrics
    result.attention_weights = attn_weights
    result.trained = True

    return result


def _train_sklearn_fallback(
    df: pd.DataFrame,
    config: TransformerConfig,
    result: TransformerResult,
    model_type: str,
) -> TransformerResult:
    """Train using sklearn fallback."""
    fallback = SklearnFallback(config)
    fit_result = fallback.fit(df)

    if "error" in fit_result:
        result.error = fit_result["error"]
        return result

    # Predict on last sample
    pred = fallback.predict(df)

    result.predictions = pred
    result.train_loss = fit_result.get("train_loss", [0])
    result.val_loss = fit_result.get("val_loss", [0])
    result.feature_importance = fit_result.get("feature_importance", {})
    result.metrics = {
        "mse": fit_result["train_loss"][0] if fit_result["train_loss"] else 0,
        "mae": 0,
        "r2": 0,
        "directional_accuracy": 0,
        "fallback": 1,
    }
    result.trained = True

    return result


def get_transformer_ensemble_prediction(
    df: pd.DataFrame,
    config: Optional[TransformerConfig] = None,
) -> Dict:
    """
    Run all three transformer models and ensemble their predictions.

    Returns:
        Dict with ensemble prediction, individual model results, and confidence.
    """
    if config is None:
        config = TransformerConfig()

    results = {}
    predictions = []

    for name, train_fn in [
        ("patchtst", train_patchtst),
        ("tft", train_tft),
        ("lpatchtst", train_lstm_patchtst_hybrid),
    ]:
        try:
            r = train_fn(df, config)
            results[name] = r
            if r.trained and r.predictions is not None and len(r.predictions) > 0:
                predictions.append(r.predictions[-1])
        except Exception as e:
            logger.warning(f"{name} failed: {e}")
            results[name] = TransformerResult(model_type=name, error=str(e))

    if predictions:
        ensemble_pred = float(np.mean(predictions))
        pred_std = float(np.std(predictions)) if len(predictions) > 1 else 0
        confidence = max(0, 1 - pred_std / (abs(ensemble_pred) + 1e-10))
    else:
        ensemble_pred = 0
        confidence = 0

    return {
        "ensemble_prediction": ensemble_pred,
        "confidence": confidence,
        "individual_models": {
            name: {
                "trained": r.trained,
                "metrics": r.metrics,
                "error": r.error,
                "model_type": r.model_type,
            }
            for name, r in results.items()
        },
        "n_models_trained": sum(1 for r in results.values() if r.trained),
    }


def get_transformer_confidence_adjustment(ensemble_result: Dict) -> Tuple[float, str]:
    """Get confidence adjustment from transformer ensemble."""
    n_trained = ensemble_result.get("n_models_trained", 0)
    confidence = ensemble_result.get("confidence", 0)

    if n_trained == 0:
        return 0.0, "Transformer: no models trained"

    if n_trained >= 3 and confidence > 0.7:
        return 0.08, "Transformer: strong ensemble agreement"
    elif n_trained >= 2 and confidence > 0.5:
        return 0.04, "Transformer: moderate ensemble agreement"
    elif n_trained >= 1:
        return 0.0, "Transformer: single model only"
    else:
        return 0.0, "Transformer: insufficient models"
