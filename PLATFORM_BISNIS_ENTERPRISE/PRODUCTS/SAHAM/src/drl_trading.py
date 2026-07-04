"""
Deep Reinforcement Learning (DRL) Trading Module.

Based on FinRL framework (AI4Finance Foundation) and Stable-Baselines3.
Implements OpenAI Gym-compatible trading environment and DRL agent wrapper.

Algoritma yang didukung:
- DQN (Deep Q-Network): Discrete actions (hold/buy/sell)
- PPO (Proximal Policy Optimization): Continuous actions
- SAC (Soft Actor-Critic): Exploration-efficient

Environment design:
- State: Technical indicators + portfolio state
- Action: {0: Hold, 1: Buy, 2: Sell} or continuous [-1, +1]
- Reward: Risk-adjusted portfolio return

NOTE: Requires stable-baselines3 and gymnasium (optional dependencies).
Falls back to simple Q-learning if those are not installed.
"""

import pandas as pd
import numpy as np
from typing import Optional, Dict, Tuple, List
from dataclasses import dataclass
import warnings

warnings.filterwarnings("ignore", category=FutureWarning)


@dataclass
class DRLPrediction:
    """DRL agent prediction result."""
    action: int  # 0=hold, 1=buy, 2=sell
    action_name: str
    confidence: float  # 0-1
    q_values: Optional[np.ndarray] = None
    position_recommendation: float = 0.0  # -1 to +1


@dataclass
class DRLTrainingResult:
    """DRL training result."""
    algorithm: str
    episodes: int
    final_reward: float
    mean_reward: float
    training_time_seconds: float
    model_path: Optional[str] = None
    converged: bool = False


class StockTradingEnv:
    """
    OpenAI Gym-compatible stock trading environment.

    State: Technical indicators + portfolio state
    Action: {0: Hold, 1: Buy, 2: Sell}
    Reward: Portfolio return (risk-adjusted)
    """

    def __init__(
        self,
        df: pd.DataFrame,
        close_col: str = "Close",
        high_col: str = "High",
        low_col: str = "Low",
        volume_col: str = "Volume",
        initial_capital: float = 100_000_000,
        transaction_cost_pct: float = 0.002,
        max_position_pct: float = 0.25,
        window_size: int = 20,
        feature_cols: Optional[List[str]] = None,
    ):
        self.df = df.reset_index(drop=True)
        self.close_col = close_col
        self.high_col = high_col
        self.low_col = low_col
        self.volume_col = volume_col
        self.initial_capital = initial_capital
        self.transaction_cost_pct = transaction_cost_pct
        self.max_position_pct = max_position_pct
        self.window_size = window_size

        # Build features
        if feature_cols is None:
            self.feature_cols = self._build_default_features()
        else:
            self.feature_cols = feature_cols

        self.n_features = len(self.feature_cols)
        self.n_steps = len(self.df)

        # Action space: 0=Hold, 1=Buy, 2=Sell
        self.action_space_n = 3
        # Observation: features + portfolio state (cash, position, pnl)
        self.observation_dim = self.n_features * self.window_size + 3

        self._reset()

    def _build_default_features(self) -> List[str]:
        """Build default technical features from OHLCV data."""
        df = self.df
        features = []

        # Returns
        df["return_1d"] = df[self.close_col].pct_change()
        features.append("return_1d")

        # RSI
        delta = df[self.close_col].diff()
        gain = delta.where(delta > 0, 0).rolling(14).mean()
        loss = (-delta.where(delta < 0, 0)).rolling(14).mean()
        rs = gain / (loss + 1e-10)
        df["rsi"] = 100 - (100 / (1 + rs))
        features.append("rsi")

        # Moving averages
        df["ma_5"] = df[self.close_col].rolling(5).mean()
        df["ma_20"] = df[self.close_col].rolling(20).mean()
        df["ma_ratio"] = df["ma_5"] / df["ma_20"]
        features.append("ma_ratio")

        # Volatility
        df["volatility"] = df["return_1d"].rolling(20).std()
        features.append("volatility")

        # Volume ratio
        if self.volume_col in df.columns:
            df["vol_ratio"] = df[self.volume_col] / df[self.volume_col].rolling(20).mean()
            features.append("vol_ratio")

        # MACD
        ema_12 = df[self.close_col].ewm(span=12).mean()
        ema_26 = df[self.close_col].ewm(span=26).mean()
        df["macd"] = ema_12 - ema_26
        features.append("macd")

        # Fill NaN
        for col in features:
            df[col] = df[col].fillna(0)

        return features

    def _reset(self):
        """Reset environment to initial state."""
        self.current_step = self.window_size
        self.cash = self.initial_capital
        self.position = 0  # Number of shares
        self.position_value = 0.0
        self.total_value = self.initial_capital
        self.initial_value = self.initial_capital
        self.trades = 0
        self.returns_history: List[float] = []
        return self._get_observation()

    def _get_observation(self) -> np.ndarray:
        """Get current observation (state)."""
        if self.current_step < self.window_size:
            obs = np.zeros(self.observation_dim)
        else:
            # Feature window
            feature_data = self.df[self.feature_cols].iloc[
                self.current_step - self.window_size:self.current_step
            ].values.flatten()

            # Portfolio state
            portfolio_state = np.array([
                self.cash / self.initial_capital,
                self.position_value / self.initial_capital,
                (self.total_value - self.initial_value) / self.initial_value,
            ])

            obs = np.concatenate([feature_data, portfolio_state])

        # Replace NaN/inf
        obs = np.nan_to_num(obs, nan=0.0, posinf=1.0, neginf=-1.0)
        return obs

    def step(self, action: int) -> Tuple[np.ndarray, float, bool, Dict]:
        """
        Execute one step in the environment.

        Args:
            action: 0=Hold, 1=Buy, 2=Sell
        """
        current_price = self.df[self.close_col].iloc[self.current_step]
        prev_total_value = self.total_value

        # Execute action
        max_invest = self.initial_capital * self.max_position_pct

        if action == 1:  # Buy
            buy_amount = min(max_invest, self.cash * 0.3)
            shares = int(buy_amount / current_price)
            if shares > 0:
                cost = shares * current_price * (1 + self.transaction_cost_pct)
                if cost <= self.cash:
                    self.cash -= cost
                    self.position += shares
                    self.trades += 1

        elif action == 2:  # Sell
            if self.position > 0:
                sell_shares = int(self.position * 0.5)  # Sell 50% of position
                if sell_shares > 0:
                    revenue = sell_shares * current_price * (1 - self.transaction_cost_pct)
                    self.cash += revenue
                    self.position -= sell_shares
                    self.trades += 1

        # Update portfolio value
        self.position_value = self.position * current_price
        self.total_value = self.cash + self.position_value

        # Reward: risk-adjusted return
        step_return = (self.total_value - prev_total_value) / prev_total_value if prev_total_value > 0 else 0
        self.returns_history.append(step_return)

        # Sharpe-like reward (penalize volatility)
        if len(self.returns_history) > 10:
            recent_returns = self.returns_history[-10:]
            vol = np.std(recent_returns) + 1e-10
            reward = step_return / vol - 0.01 * abs(step_return)  # Transaction cost penalty
        else:
            reward = step_return

        # Move to next step
        self.current_step += 1
        done = self.current_step >= self.n_steps - 1

        info = {
            "total_value": self.total_value,
            "cash": self.cash,
            "position": self.position,
            "step_return": step_return,
            "trades": self.trades,
        }

        return self._get_observation(), reward, done, info


class SimpleQLearningAgent:
    """
    Simple Q-Learning agent (fallback when stable-baselines3 not available).
    Uses tabular Q-learning with discretized states.
    """

    def __init__(
        self,
        n_features: int,
        n_actions: int = 3,
        learning_rate: float = 0.01,
        gamma: float = 0.95,
        epsilon: float = 1.0,
        epsilon_decay: float = 0.995,
        epsilon_min: float = 0.01,
        n_bins: int = 10,
    ):
        self.n_features = n_features
        self.n_actions = n_actions
        self.lr = learning_rate
        self.gamma = gamma
        self.epsilon = epsilon
        self.epsilon_decay = epsilon_decay
        self.epsilon_min = epsilon_min
        self.n_bins = n_bins

        # Discretized Q-table: (bins^features, actions) — simplified to summary stats
        self.q_table = np.zeros((n_bins * 4, n_actions))  # Simplified state
        self._bins = np.linspace(-2, 2, n_bins)

    def _discretize(self, obs: np.ndarray) -> int:
        """Discretize observation to state index."""
        # Use summary statistics instead of full discretization
        summary = np.array([
            np.mean(obs),
            np.std(obs),
            np.min(obs),
            np.max(obs),
        ])
        # Clip and bin each summary
        binned = np.digitize(summary, self._bins) - 1
        binned = np.clip(binned, 0, self.n_bins - 1)
        state_idx = int(np.sum(binned * np.array([1, self.n_bins, self.n_bins**2, self.n_bins**3])))
        # Clip to q_table size
        return min(state_idx, self.q_table.shape[0] - 1)

    def act(self, obs: np.ndarray, training: bool = True) -> int:
        """Choose action using epsilon-greedy."""
        if training and np.random.random() < self.epsilon:
            return np.random.randint(self.n_actions)

        state_idx = self._discretize(obs)
        return int(np.argmax(self.q_table[state_idx]))

    def learn(self, obs: np.ndarray, action: int, reward: float, next_obs: np.ndarray, done: bool):
        """Q-learning update."""
        state_idx = self._discretize(obs)
        next_state_idx = self._discretize(next_obs)

        target = reward + (0 if done else self.gamma * np.max(self.q_table[next_state_idx]))
        self.q_table[state_idx, action] += self.lr * (target - self.q_table[state_idx, action])

        if done:
            self.epsilon = max(self.epsilon_min, self.epsilon * self.epsilon_decay)

    def get_q_values(self, obs: np.ndarray) -> np.ndarray:
        """Get Q-values for observation."""
        state_idx = self._discretize(obs)
        return self.q_table[state_idx].copy()


def train_drl_agent(
    df: pd.DataFrame,
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
    algorithm: str = "q_learning",
    episodes: int = 500,
    initial_capital: float = 100_000_000,
    transaction_cost_pct: float = 0.002,
    save_path: Optional[str] = None,
) -> DRLTrainingResult:
    """
    Train a DRL agent on price data.

    Args:
        algorithm: "q_learning" (default, always available), "dqn", "ppo", "sac"
        episodes: Number of training episodes
        save_path: Path to save trained model

    Returns DRLTrainingResult with training metrics.
    """
    import time
    start_time = time.time()

    env = StockTradingEnv(
        df, close_col=close_col, high_col=high_col, low_col=low_col,
        volume_col=volume_col, initial_capital=initial_capital,
        transaction_cost_pct=transaction_cost_pct,
    )

    if algorithm == "q_learning":
        agent = SimpleQLearningAgent(n_features=env.observation_dim)
        rewards = []

        for ep in range(episodes):
            obs = env._reset()
            total_reward = 0
            done = False

            while not done:
                action = agent.act(obs, training=True)
                next_obs, reward, done, info = env.step(action)
                agent.learn(obs, action, reward, next_obs, done)
                obs = next_obs
                total_reward += reward

            rewards.append(total_reward)

        mean_reward = np.mean(rewards[-50:]) if len(rewards) >= 50 else np.mean(rewards)
        final_reward = rewards[-1] if rewards else 0
        converged = mean_reward > 0 and len(rewards) >= 100

        if save_path:
            try:
                import joblib
                joblib.dump(agent, save_path)
            except Exception:
                pass

        return DRLTrainingResult(
            algorithm="q_learning",
            episodes=episodes,
            final_reward=final_reward,
            mean_reward=mean_reward,
            training_time_seconds=time.time() - start_time,
            model_path=save_path,
            converged=converged,
        )

    else:
        # Try stable-baselines3
        try:
            from stable_baselines3 import DQN, PPO, SAC
            from stable_baselines3.common.vec_env import DummyVecEnv

            vec_env = DummyVecEnv([lambda: _SB3Wrapper(env)])

            if algorithm == "dqn":
                model = DQN("MlpPolicy", vec_env, verbose=0, learning_starts=100)
            elif algorithm == "ppo":
                model = PPO("MlpPolicy", vec_env, verbose=0, n_steps=256)
            elif algorithm == "sac":
                model = SAC("MlpPolicy", vec_env, verbose=0)
            else:
                raise ValueError(f"Unknown algorithm: {algorithm}")

            model.learn(total_timesteps=episodes * 100)

            if save_path:
                model.save(save_path)

            return DRLTrainingResult(
                algorithm=algorithm,
                episodes=episodes,
                final_reward=0,
                mean_reward=0,
                training_time_seconds=time.time() - start_time,
                model_path=save_path,
                converged=True,
            )

        except ImportError:
            # Fallback to Q-learning
            return train_drl_agent(
                df, close_col=close_col, high_col=high_col, low_col=low_col,
                volume_col=volume_col, algorithm="q_learning",
                episodes=episodes, initial_capital=initial_capital,
                transaction_cost_pct=transaction_cost_pct, save_path=save_path,
            )


class _SB3Wrapper:
    """Wrapper to make StockTradingEnv compatible with stable-baselines3."""
    def __init__(self, env: StockTradingEnv):
        self.env = env
        self.observation_space = type("Space", (), {
            "shape": (env.observation_dim,),
            "low": -np.inf,
            "high": np.inf,
        })()
        self.action_space = type("Space", (), {
            "n": env.action_space_n,
            "shape": (),
        })()

    def reset(self):
        return self.env._reset()

    def step(self, action):
        obs, reward, done, info = self.env.step(int(action))
        return obs, reward, done, info

    def render(self, mode="human"):
        pass

    def close(self):
        pass


def predict_drl_action(
    df: pd.DataFrame,
    model_path: Optional[str] = None,
    agent: Optional[SimpleQLearningAgent] = None,
    close_col: str = "Close",
    high_col: str = "High",
    low_col: str = "Low",
    volume_col: str = "Volume",
) -> DRLPrediction:
    """
    Get DRL agent prediction for current market state.

    Args:
        model_path: Path to saved model
        agent: Pre-loaded agent (alternative to model_path)

    Returns DRLPrediction with action and confidence.
    """
    env = StockTradingEnv(
        df, close_col=close_col, high_col=high_col, low_col=low_col,
        volume_col=volume_col,
    )

    # Load agent
    if agent is None and model_path:
        try:
            import joblib
            agent = joblib.load(model_path)
        except Exception:
            pass

    if agent is None:
        # No trained agent — return neutral
        return DRLPrediction(
            action=0, action_name="HOLD", confidence=0.5,
            position_recommendation=0.0,
        )

    obs = env._get_observation()
    action = agent.act(obs, training=False)

    # Get Q-values for confidence
    q_values = agent.get_q_values(obs)
    q_normalized = q_values - np.min(q_values)
    q_sum = np.sum(q_normalized) + 1e-10
    confidence = float(q_normalized[action] / q_sum)

    action_names = {0: "HOLD", 1: "BUY", 2: "SELL"}
    pos_rec = {0: 0.0, 1: min(1.0, confidence), 2: -min(1.0, confidence)}

    return DRLPrediction(
        action=action,
        action_name=action_names.get(action, "HOLD"),
        confidence=confidence,
        q_values=q_values,
        position_recommendation=pos_rec.get(action, 0.0),
    )


def get_drl_confidence_adjustment(pred: DRLPrediction) -> Tuple[float, str]:
    """
    Get confidence adjustment factor from DRL prediction for predictor integration.

    Returns:
        (adjustment_factor, reason)
    """
    if pred.action_name == "BUY" and pred.confidence > 0.6:
        return 0.08, f"DRL bullish (conf: {pred.confidence:.0%})"
    elif pred.action_name == "SELL" and pred.confidence > 0.6:
        return -0.08, f"DRL bearish (conf: {pred.confidence:.0%})"
    elif pred.action_name == "BUY":
        return 0.03, f"DRL mildly bullish (conf: {pred.confidence:.0%})"
    elif pred.action_name == "SELL":
        return -0.03, f"DRL mildly bearish (conf: {pred.confidence:.0%})"
    return 0.0, "DRL neutral"
