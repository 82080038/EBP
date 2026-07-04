#!/usr/bin/env python3
"""
AI Trading System - Sistem Trading Berbasis Kecerdasan Buatan
Tujuan: Menganalisis pasar menggunakan AI yang belajar otomatis untuk keuntungan maksimal
"""

import pandas as pd
import numpy as np
import mysql.connector
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.preprocessing import StandardScaler
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score
import tensorflow as tf
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense, Dropout, BatchNormalization
from tensorflow.keras.optimizers import Adam
import joblib
import warnings
warnings.filterwarnings('ignore')

class AITradingSystem:
    def __init__(self, db_config):
        """
        Initialize AI Trading System
        
        Args:
            db_config (dict): Database configuration
        """
        self.db_config = db_config
        self.models = {}
        self.scalers = {}
        self.feature_importance = {}
        self.connection = None
        
    def connect_database(self):
        """Connect to MySQL database"""
        try:
            self.connection = mysql.connector.connect(**self.db_config)
            print("✅ Database connected successfully")
            return True
        except Exception as e:
            print(f"❌ Database connection failed: {e}")
            return False
    
    def get_stock_data(self, symbol, days=252):
        """
        Get historical stock data for training
        
        Args:
            symbol (str): Stock symbol
            days (int): Number of days to retrieve
            
        Returns:
            pd.DataFrame: Historical stock data
        """
        query = """
        SELECT 
            s.symbol,
            s.current_price,
            s.previous_close,
            s.volume,
            s.market_cap,
            ph.date,
            ph.open_price,
            ph.high_price,
            ph.low_price,
            ph.close_price,
            ph.volume as daily_volume,
            ti.rsi_14,
            ti.macd,
            ti.bollinger_upper,
            ti.bollinger_lower,
            ti.sma_20,
            ti.ema_12,
            ti.ema_26,
            fr.pe_ratio,
            fr.pb_ratio,
            fr.roe,
            fr.debt_to_equity,
            sa.sentiment_score,
            sa.sentiment_label
        FROM stocks s
        LEFT JOIN price_history ph ON s.id = ph.stock_id
        LEFT JOIN technical_indicators ti ON s.id = ti.stock_id AND ph.date = ti.date
        LEFT JOIN financial_ratios fr ON s.id = fr.stock_id
        LEFT JOIN sentiment_analysis sa ON s.id = sa.stock_id AND ph.date = sa.analysis_date
        WHERE s.symbol = %s
        ORDER BY ph.date DESC
        LIMIT %s
        """
        
        try:
            df = pd.read_sql(query, self.connection, params=[symbol, days])
            df['date'] = pd.to_datetime(df['date'])
            df = df.sort_values('date').reset_index(drop=True)
            return df
        except Exception as e:
            print(f"❌ Error getting data for {symbol}: {e}")
            return None
    
    def create_features(self, df):
        """
        Create features for machine learning
        
        Args:
            df (pd.DataFrame): Stock data
            
        Returns:
            pd.DataFrame: Features dataframe
        """
        features_df = df.copy()
        
        # Price-based features
        features_df['price_change'] = features_df['close_price'].pct_change()
        features_df['price_change_2'] = features_df['close_price'].pct_change(2)
        features_df['price_change_5'] = features_df['close_price'].pct_change(5)
        features_df['price_change_10'] = features_df['close_price'].pct_change(10)
        
        # Volume features
        features_df['volume_change'] = features_df['daily_volume'].pct_change()
        features_df['volume_sma_5'] = features_df['daily_volume'].rolling(5).mean()
        features_df['volume_ratio'] = features_df['daily_volume'] / features_df['volume_sma_5']
        
        # Volatility features
        features_df['volatility_5'] = features_df['price_change'].rolling(5).std()
        features_df['volatility_10'] = features_df['price_change'].rolling(10).std()
        features_df['volatility_20'] = features_df['price_change'].rolling(20).std()
        
        # Technical indicators
        features_df['rsi_14'] = features_df['rsi_14'].fillna(50)
        features_df['macd'] = features_df['macd'].fillna(0)
        features_df['bollinger_position'] = (features_df['close_price'] - features_df['bollinger_lower']) / (features_df['bollinger_upper'] - features_df['bollinger_lower'])
        features_df['sma_20_position'] = features_df['close_price'] / features_df['sma_20']
        
        # Financial ratios
        features_df['pe_ratio'] = features_df['pe_ratio'].fillna(features_df['pe_ratio'].median())
        features_df['pb_ratio'] = features_df['pb_ratio'].fillna(features_df['pb_ratio'].median())
        features_df['roe'] = features_df['roe'].fillna(features_df['roe'].median())
        
        # Sentiment features
        features_df['sentiment_score'] = features_df['sentiment_score'].fillna(0)
        features_df['sentiment_positive'] = (features_df['sentiment_label'] == 'Positive').astype(int)
        features_df['sentiment_negative'] = (features_df['sentiment_label'] == 'Negative').astype(int)
        
        # Time features
        features_df['day_of_week'] = features_df['date'].dt.dayofweek
        features_df['month'] = features_df['date'].dt.month
        features_df['quarter'] = features_df['date'].dt.quarter
        
        # Target variable (next day return)
        features_df['target_return'] = features_df['price_change'].shift(-1)
        features_df['target_direction'] = (features_df['target_return'] > 0).astype(int)
        
        return features_df
    
    def prepare_training_data(self, symbols, days=252):
        """
        Prepare training data for multiple stocks
        
        Args:
            symbols (list): List of stock symbols
            days (int): Number of days per stock
            
        Returns:
            tuple: (X, y, feature_names)
        """
        all_data = []
        
        for symbol in symbols:
            print(f"📊 Processing {symbol}...")
            df = self.get_stock_data(symbol, days)
            if df is not None and len(df) > 50:
                features_df = self.create_features(df)
                all_data.append(features_df)
        
        if not all_data:
            print("❌ No data available for training")
            return None, None, None
        
        # Combine all data
        combined_df = pd.concat(all_data, ignore_index=True)
        
        # Remove rows with NaN values
        combined_df = combined_df.dropna()
        
        # Select features
        feature_columns = [
            'price_change', 'price_change_2', 'price_change_5', 'price_change_10',
            'volume_change', 'volume_ratio',
            'volatility_5', 'volatility_10', 'volatility_20',
            'rsi_14', 'macd', 'bollinger_position', 'sma_20_position',
            'pe_ratio', 'pb_ratio', 'roe',
            'sentiment_score', 'sentiment_positive', 'sentiment_negative',
            'day_of_week', 'month', 'quarter'
        ]
        
        X = combined_df[feature_columns].values
        y = combined_df['target_return'].values
        
        print(f"✅ Training data prepared: {X.shape[0]} samples, {X.shape[1]} features")
        return X, y, feature_columns
    
    def train_models(self, X, y, feature_names):
        """
        Train multiple AI models
        
        Args:
            X (np.array): Features
            y (np.array): Target values
            feature_names (list): Feature names
        """
        print("🤖 Training AI models...")
        
        # Split data
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        # Scale features
        scaler = StandardScaler()
        X_train_scaled = scaler.fit_transform(X_train)
        X_test_scaled = scaler.transform(X_test)
        
        self.scalers['main'] = scaler
        
        # 1. Random Forest
        print("🌲 Training Random Forest...")
        rf_model = RandomForestRegressor(
            n_estimators=100,
            max_depth=10,
            random_state=42,
            n_jobs=-1
        )
        rf_model.fit(X_train_scaled, y_train)
        self.models['random_forest'] = rf_model
        
        # 2. Gradient Boosting
        print("📈 Training Gradient Boosting...")
        gb_model = GradientBoostingRegressor(
            n_estimators=100,
            learning_rate=0.1,
            max_depth=6,
            random_state=42
        )
        gb_model.fit(X_train_scaled, y_train)
        self.models['gradient_boosting'] = gb_model
        
        # 3. LSTM Neural Network
        print("🧠 Training LSTM...")
        lstm_model = self.create_lstm_model(X_train_scaled.shape[1])
        lstm_model.fit(
            X_train_scaled.reshape(X_train_scaled.shape[0], 1, X_train_scaled.shape[1]),
            y_train,
            epochs=50,
            batch_size=32,
            validation_split=0.2,
            verbose=0
        )
        self.models['lstm'] = lstm_model
        
        # Evaluate models
        self.evaluate_models(X_test_scaled, y_test)
        
        # Feature importance
        self.feature_importance = dict(zip(feature_names, rf_model.feature_importances_))
        
        print("✅ All models trained successfully!")
    
    def create_lstm_model(self, input_dim):
        """Create LSTM model architecture"""
        model = Sequential([
            LSTM(50, return_sequences=True, input_shape=(1, input_dim)),
            Dropout(0.2),
            LSTM(50, return_sequences=False),
            Dropout(0.2),
            Dense(25),
            BatchNormalization(),
            Dense(1)
        ])
        
        model.compile(
            optimizer=Adam(learning_rate=0.001),
            loss='mse',
            metrics=['mae']
        )
        
        return model
    
    def evaluate_models(self, X_test, y_test):
        """Evaluate model performance"""
        print("\n📊 Model Performance:")
        print("-" * 50)
        
        for name, model in self.models.items():
            if name == 'lstm':
                y_pred = model.predict(X_test.reshape(X_test.shape[0], 1, X_test.shape[1])).flatten()
            else:
                y_pred = model.predict(X_test)
            
            mse = mean_squared_error(y_test, y_pred)
            r2 = r2_score(y_test, y_pred)
            
            print(f"{name.upper():20} | MSE: {mse:.6f} | R²: {r2:.4f}")
    
    def predict(self, symbol, days_ahead=1):
        """
        Make prediction for a stock
        
        Args:
            symbol (str): Stock symbol
            days_ahead (int): Days to predict ahead
            
        Returns:
            dict: Prediction results
        """
        df = self.get_stock_data(symbol, 100)
        if df is None or len(df) < 50:
            return None
        
        features_df = self.create_features(df)
        latest_features = features_df.iloc[-1]
        
        # Prepare features for prediction
        feature_columns = [
            'price_change', 'price_change_2', 'price_change_5', 'price_change_10',
            'volume_change', 'volume_ratio',
            'volatility_5', 'volatility_10', 'volatility_20',
            'rsi_14', 'macd', 'bollinger_position', 'sma_20_position',
            'pe_ratio', 'pb_ratio', 'roe',
            'sentiment_score', 'sentiment_positive', 'sentiment_negative',
            'day_of_week', 'month', 'quarter'
        ]
        
        X = latest_features[feature_columns].values.reshape(1, -1)
        X_scaled = self.scalers['main'].transform(X)
        
        predictions = {}
        confidences = {}
        
        # Get predictions from all models
        for name, model in self.models.items():
            if name == 'lstm':
                pred = model.predict(X_scaled.reshape(1, 1, X_scaled.shape[1])).flatten()[0]
            else:
                pred = model.predict(X_scaled)[0]
            
            predictions[name] = pred
            
            # Calculate confidence based on prediction consistency
            if name == 'random_forest':
                # Use prediction variance as confidence measure
                tree_predictions = [tree.predict(X_scaled)[0] for tree in model.estimators_]
                confidence = 1 - np.std(tree_predictions) / (np.mean(tree_predictions) + 1e-8)
                confidences[name] = max(0, min(1, confidence))
            else:
                confidences[name] = 0.7  # Default confidence
        
        # Ensemble prediction
        ensemble_pred = np.mean(list(predictions.values()))
        ensemble_confidence = np.mean(list(confidences.values()))
        
        # Generate trading signal
        signal = self.generate_trading_signal(ensemble_pred, ensemble_confidence)
        
        return {
            'symbol': symbol,
            'predicted_return': ensemble_pred,
            'confidence': ensemble_confidence,
            'signal': signal,
            'individual_predictions': predictions,
            'current_price': latest_features['close_price'],
            'predicted_price': latest_features['close_price'] * (1 + ensemble_pred)
        }
    
    def generate_trading_signal(self, predicted_return, confidence):
        """
        Generate trading signal based on prediction
        
        Args:
            predicted_return (float): Predicted return
            confidence (float): Prediction confidence
            
        Returns:
            str: Trading signal
        """
        # Thresholds
        return_threshold = 0.02  # 2% minimum return
        confidence_threshold = 0.6  # 60% minimum confidence
        
        if confidence < confidence_threshold:
            return 'HOLD'
        
        if predicted_return > return_threshold:
            return 'BUY'
        elif predicted_return < -return_threshold:
            return 'SELL'
        else:
            return 'HOLD'
    
    def save_models(self, filepath='ai_models/'):
        """Save trained models"""
        import os
        os.makedirs(filepath, exist_ok=True)
        
        # Save scikit-learn models
        for name, model in self.models.items():
            if name != 'lstm':
                joblib.dump(model, f"{filepath}{name}_model.pkl")
        
        # Save LSTM model
        if 'lstm' in self.models:
            self.models['lstm'].save(f"{filepath}lstm_model.h5")
        
        # Save scaler
        joblib.dump(self.scalers['main'], f"{filepath}scaler.pkl")
        
        # Save feature importance
        joblib.dump(self.feature_importance, f"{filepath}feature_importance.pkl")
        
        print(f"✅ Models saved to {filepath}")
    
    def load_models(self, filepath='ai_models/'):
        """Load trained models"""
        try:
            # Load scikit-learn models
            self.models['random_forest'] = joblib.load(f"{filepath}random_forest_model.pkl")
            self.models['gradient_boosting'] = joblib.load(f"{filepath}gradient_boosting_model.pkl")
            
            # Load LSTM model
            self.models['lstm'] = tf.keras.models.load_model(f"{filepath}lstm_model.h5")
            
            # Load scaler
            self.scalers['main'] = joblib.load(f"{filepath}scaler.pkl")
            
            # Load feature importance
            self.feature_importance = joblib.load(f"{filepath}feature_importance.pkl")
            
            print("✅ Models loaded successfully")
            return True
        except Exception as e:
            print(f"❌ Error loading models: {e}")
            return False

def main():
    """Main function untuk training dan testing"""
    
    # Database configuration
    db_config = {
        'host': 'localhost',
        'user': 'root',
        'password': '',
        'database': 'db_saham_optimized',
        'port': 3306
    }
    
    # Initialize AI Trading System
    ai_system = AITradingSystem(db_config)
    
    if not ai_system.connect_database():
        return
    
    # Get list of stocks for training
    query = "SELECT symbol FROM stocks WHERE is_active = 1 LIMIT 20"
    stocks_df = pd.read_sql(query, ai_system.connection)
    symbols = stocks_df['symbol'].tolist()
    
    print(f"🎯 Training on {len(symbols)} stocks: {symbols}")
    
    # Prepare training data
    X, y, feature_names = ai_system.prepare_training_data(symbols)
    
    if X is not None:
        # Train models
        ai_system.train_models(X, y, feature_names)
        
        # Save models
        ai_system.save_models()
        
        # Test prediction
        print("\n🔮 Testing predictions:")
        print("-" * 50)
        
        for symbol in symbols[:5]:  # Test first 5 stocks
            prediction = ai_system.predict(symbol)
            if prediction:
                print(f"{symbol:6} | Signal: {prediction['signal']:4} | "
                      f"Return: {prediction['predicted_return']:7.4f} | "
                      f"Confidence: {prediction['confidence']:6.3f}")
    
    ai_system.connection.close()
    print("\n✅ AI Trading System training completed!")

if __name__ == "__main__":
    main()
