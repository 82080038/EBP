import numpy as np
import joblib
import os
import warnings
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import MinMaxScaler
from .config import MODEL_CONFIG, MODELS_DIR

# Suppress sklearn UserWarning about feature names — this is a cosmetic issue
# that occurs because MinMaxScaler output (numpy) is passed to LightGBM
# which was fitted with feature names. The predictions are still correct.
warnings.filterwarnings("ignore", message="X does not have valid feature names", category=UserWarning)

# Detect GPU availability for LightGBM
# LightGBM uses GPU 0 by default. With 2x GTX 1050 Ti, we keep LightGBM on GPU 0
# and PyTorch transformers on GPU 1 for parallel training.
_LGBM_GPU = False
try:
    import lightgbm as lgb
    import numpy as _np
    _test_X = _np.random.rand(10, 5).astype(_np.float32)
    _test_y = _np.random.randint(0, 2, 10)
    _test_m = lgb.LGBMClassifier(device="gpu", n_estimators=2, verbose=-1, max_bin=63)
    _test_m.fit(_test_X, _test_y)
    _LGBM_GPU = True
    print("[OK] LightGBM GPU acceleration enabled (GPU 0)")
except Exception:
    _LGBM_GPU = False


class LightGBMModel:
    """LightGBM model — faster & often more accurate than XGBoost."""

    def __init__(self):
        self.name = "LightGBM"
        self.model = None
        self.scaler = MinMaxScaler()
        self._init_model()

    def _init_model(self):
        try:
            from lightgbm import LGBMClassifier
            kwargs = dict(
                n_estimators=MODEL_CONFIG.get("lgbm_n_estimators", 200),
                max_depth=MODEL_CONFIG.get("lgbm_max_depth", 6),
                learning_rate=MODEL_CONFIG.get("lgbm_learning_rate", 0.1),
                random_state=MODEL_CONFIG["random_state"],
                n_jobs=-1,
                verbose=-1,
            )
            if _LGBM_GPU:
                kwargs["device"] = "gpu"
                kwargs["max_bin"] = 63  # GPU requires max_bin <= 63
            self.model = LGBMClassifier(**kwargs)
        except ImportError:
            self.model = None

    def train(self, X_train, y_train):
        if self.model is None:
            self._init_model()
        if self.model is None:
            return
        X_train_scaled = np.asarray(self.scaler.fit_transform(X_train))
        self.model.fit(X_train_scaled, np.asarray(y_train))
        self.save()

    def predict_proba(self, X):
        if self.model is None:
            return np.array([[0.5, 0.5]])
        X_scaled = self.scaler.transform(X)
        return self.model.predict_proba(np.asarray(X_scaled))

    def predict(self, X):
        if self.model is None:
            return np.array([0])
        X_scaled = self.scaler.transform(X)
        return self.model.predict(np.asarray(X_scaled))

    def save(self):
        if self.model:
            path = os.path.join(MODELS_DIR, "lgbm_model.pkl")
            joblib.dump({"model": self.model, "scaler": self.scaler}, path)

    def load(self):
        path = os.path.join(MODELS_DIR, "lgbm_model.pkl")
        if os.path.exists(path):
            data = joblib.load(path)
            self.model = data["model"]
            self.scaler = data["scaler"]
            return True
        return False


class RandomForestModel:
    def __init__(self):
        self.model = RandomForestClassifier(
            n_estimators=MODEL_CONFIG["rf_n_estimators"],
            max_depth=MODEL_CONFIG["rf_max_depth"],
            random_state=MODEL_CONFIG["random_state"],
            n_jobs=-1,
        )
        self.name = "RandomForest"
        self.scaler = MinMaxScaler()

    def train(self, X_train, y_train):
        X_train_scaled = self.scaler.fit_transform(X_train)
        self.model.fit(X_train_scaled, y_train)
        self.save()

    def predict_proba(self, X):
        X_scaled = self.scaler.transform(X)
        return self.model.predict_proba(X_scaled)

    def predict(self, X):
        X_scaled = self.scaler.transform(X)
        return self.model.predict(X_scaled)

    def save(self):
        path = os.path.join(MODELS_DIR, "rf_model.pkl")
        joblib.dump({"model": self.model, "scaler": self.scaler}, path)

    def load(self):
        path = os.path.join(MODELS_DIR, "rf_model.pkl")
        if os.path.exists(path):
            data = joblib.load(path)
            self.model = data["model"]
            self.scaler = data["scaler"]
            return True
        return False


class XGBoostModel:
    def __init__(self):
        from xgboost import XGBClassifier

        self.model = XGBClassifier(
            n_estimators=MODEL_CONFIG["xgb_n_estimators"],
            max_depth=MODEL_CONFIG["xgb_max_depth"],
            learning_rate=MODEL_CONFIG["xgb_learning_rate"],
            random_state=MODEL_CONFIG["random_state"],
            eval_metric="logloss",
            n_jobs=-1,
        )
        self.name = "XGBoost"
        self.scaler = MinMaxScaler()

    def train(self, X_train, y_train):
        X_train_scaled = self.scaler.fit_transform(X_train)
        self.model.fit(X_train_scaled, y_train)
        self.save()

    def predict_proba(self, X):
        X_scaled = self.scaler.transform(X)
        return self.model.predict_proba(X_scaled)

    def predict(self, X):
        X_scaled = self.scaler.transform(X)
        return self.model.predict(X_scaled)

    def save(self):
        path = os.path.join(MODELS_DIR, "xgb_model.pkl")
        joblib.dump({"model": self.model, "scaler": self.scaler}, path)

    def load(self):
        path = os.path.join(MODELS_DIR, "xgb_model.pkl")
        if os.path.exists(path):
            data = joblib.load(path)
            self.model = data["model"]
            self.scaler = data["scaler"]
            return True
        return False


class LSTMModel:
    def __init__(self):
        self.name = "LSTM"
        self.model = None
        self.scaler = MinMaxScaler()
        self.lookback = MODEL_CONFIG["lookback_days"]
        self.use_attention = MODEL_CONFIG.get("lstm_attention", True)

    def _build_model(self, input_shape):
        from tensorflow.keras.models import Model
        from tensorflow.keras.layers import (
            LSTM, Dense, Dropout, Input, Permute, Multiply,
            Lambda,
        )
        from tensorflow.keras import backend as K

        units = MODEL_CONFIG["lstm_units"]
        inp = Input(shape=input_shape)

        # First LSTM layer (return sequences for attention)
        lstm1 = LSTM(units, return_sequences=True)(inp)
        drop1 = Dropout(0.2)(lstm1)

        # Second LSTM layer
        lstm2 = LSTM(units, return_sequences=True)(drop1)
        drop2 = Dropout(0.2)(lstm2)

        if self.use_attention:
            # Attention mechanism: learn which timesteps matter most
            # Compute attention weights via dense + softmax
            attention_probs = Dense(units, activation="tanh", name="attention_dense")(drop2)
            attention_probs = Dense(1, activation="softmax", name="attention_weights")(attention_probs)
            # Permute to apply attention across timesteps
            attention_probs = Permute((2, 1))(attention_probs)
            attention_output = Multiply()([drop2, attention_probs])
            # Sum across timesteps (weighted average)
            attention_output = Lambda(lambda x: K.sum(x, axis=1), name="attention_context")(attention_output)
            dense1 = Dense(25, activation="relu")(attention_output)
        else:
            # Fallback: standard LSTM (return_sequences=False)
            lstm_out = LSTM(units, return_sequences=False)(drop2)
            dense1 = Dense(25, activation="relu")(lstm_out)

        output = Dense(1, activation="sigmoid")(dense1)

        self.model = Model(inputs=inp, outputs=output)
        self.model.compile(optimizer="adam", loss="binary_crossentropy", metrics=["accuracy"])
        print(f"[OK] LSTM model built {'with attention' if self.use_attention else 'standard'}")

    def _create_sequences(self, X, y, lookback):
        X_seq, y_seq = [], []
        for i in range(lookback, len(X)):
            X_seq.append(X[i - lookback:i])
            y_seq.append(y[i])
        return np.array(X_seq), np.array(y_seq)

    def train(self, X_train, y_train):
        X_scaled = self.scaler.fit_transform(X_train)
        X_seq, y_seq = self._create_sequences(X_scaled, y_train.values, self.lookback)
        self._build_model((X_seq.shape[1], X_seq.shape[2]))
        self.model.fit(
            X_seq,
            y_seq,
            epochs=MODEL_CONFIG["lstm_epochs"],
            batch_size=MODEL_CONFIG["lstm_batch_size"],
            verbose=0,
        )
        self.save()

    def predict_proba(self, X):
        X_scaled = self.scaler.transform(X)
        X_seq = np.array([X_scaled[-self.lookback:]])
        if X_seq.shape[1] < self.lookback:
            padding = np.zeros((1, self.lookback - X_seq.shape[1], X_seq.shape[2]))
            X_seq = np.concatenate([padding, X_seq], axis=1)
        pred = self.model.predict(X_seq, verbose=0)
        prob_down = 1 - pred[0][0]
        prob_up = pred[0][0]
        return np.array([[prob_down, prob_up]])

    def predict(self, X):
        proba = self.predict_proba(X)
        return (proba[:, 1] > 0.5).astype(int)

    def save(self):
        if self.model:
            path = os.path.join(MODELS_DIR, "lstm_model.keras")
            self.model.save(path)
            joblib.dump(self.scaler, os.path.join(MODELS_DIR, "lstm_scaler.pkl"))

    def load(self):
        from tensorflow.keras.models import load_model

        model_path = os.path.join(MODELS_DIR, "lstm_model.keras")
        scaler_path = os.path.join(MODELS_DIR, "lstm_scaler.pkl")
        if os.path.exists(model_path):
            self.model = load_model(model_path)
            self.scaler = joblib.load(scaler_path)
            return True
        return False


class HybridEnsemble:
    def __init__(self, use_lstm=None, use_lightgbm=True):
        self.models = [RandomForestModel(), XGBoostModel()]

        if use_lightgbm:
            lgbm = LightGBMModel()
            if lgbm.model is not None:
                self.models.append(lgbm)
                print("[OK] LightGBM model diaktifkan")

        if use_lstm is None:
            use_lstm = MODEL_CONFIG.get("use_lstm", False)

        if use_lstm:
            try:
                pass

                self.models.append(LSTMModel())
                print("[OK] LSTM model diaktifkan")
            except ImportError:
                print("[SKIP] TensorFlow tidak terinstall, LSTM dilewati")

        self.trained = False

    def train(self, X_train, y_train):
        print("\n" + "=" * 60)
        print("TRAINING HYBRID ENSEMBLE MODEL")
        print("=" * 60)

        for model in self.models:
            print(f"\n--- Training {model.name} ---")
            model.train(X_train, y_train)
            print(f"[OK] {model.name} selesai training")

        self.trained = True
        print(f"\n[OK] Ensemble training selesai dengan {len(self.models)} model")

    def predict_ensemble(self, X):
        if not self.trained:
            for model in self.models:
                model.load()

        predictions = {}
        probabilities = {}

        for model in self.models:
            try:
                proba = model.predict_proba(X[-1:])
                pred = int(proba[0][1] > 0.5)

                predictions[model.name] = pred
                probabilities[model.name] = float(proba[0][1])
            except Exception as e:
                print(f"[WARNING] {model.name} predict error: {e}")

        return predictions, probabilities

    def predict_batch(self, X):
        if not self.trained:
            for model in self.models:
                model.load()

        all_preds = {}
        all_probas = {}

        for model in self.models:
            try:
                proba = model.predict_proba(X)
                preds = (proba[:, 1] > 0.5).astype(int) if proba.ndim > 1 else (proba > 0.5).astype(int)
                all_preds[model.name] = preds
                if proba.ndim > 1:
                    all_probas[model.name] = proba[:, 1]
                else:
                    all_probas[model.name] = proba
            except Exception as e:
                print(f"[WARNING] {model.name} batch predict error: {e}")

        return all_preds, all_probas

    def load_models(self):
        loaded = 0
        for model in self.models:
            if model.load():
                loaded += 1
                print(f"[OK] {model.name} loaded dari disk")
            else:
                print(f"[INFO] {model.name} belum ada model tersimpan")
        self.trained = loaded > 0
        return loaded > 0
