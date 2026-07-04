"""
Adaptive Learning Module — Online Learning + Reinforcement Learning.

P10: Online Learning
- partial_fit() untuk model yang support incremental learning
- SGDClassifier, PassiveAggressiveClassifier, MLPClassifier
- Update model setiap prediksi baru tanpa full retrain
- Track performance drift secara real-time

P11: Reinforcement Learning
- Q-learning agent untuk trading decisions
- State: (signal, confidence, sentiment, event_risk, position_state)
- Actions: BUY, SELL, HOLD
- Reward: PnL setelah N periode
- Belajar dari trading outcome untuk improve future decisions

Usage:
    from src.adaptive_learning import OnlineLearner, TradingRLAgent
    
    # P10: Online learning
    learner = OnlineLearner()
    learner.partial_fit(X_new, y_new)
    prediction = learner.predict(X_new)
    
    # P11: RL agent
    agent = TradingRLAgent()
    action = agent.choose_action(state)
    agent.learn(state, action, reward, next_state)
"""

import numpy as np
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass
import json
import os

from .config import MODELS_DIR


# =============================================================================
# P10: ONLINE LEARNING
# =============================================================================

class OnlineLearner:
    """
    Online learning dengan partial_fit untuk model adaptif.
    
    Model yang support partial_fit:
    - SGDClassifier (linear, scalable)
    - PassiveAggressiveClassifier (online SVM)
    - MLPClassifier (neural network, if partial_fit enabled)
    - MultinomialNB (Naive Bayes)
    
    Strategy:
    1. Train initial model dengan batch data
    2. Setiap ada data baru, call partial_fit() untuk update
    3. Track accuracy secara online
    4. Jika accuracy drop, trigger full retrain
    """

    def __init__(self, model_type: str = "sgd"):
        self.model_type = model_type
        self.model = None
        self.classes_ = None
        self.n_updates = 0
        self.accuracy_history: List[float] = []
        self._init_model()

    def _init_model(self):
        """Initialize online learning model."""
        if self.model_type == "sgd":
            from sklearn.linear_model import SGDClassifier
            self.model = SGDClassifier(
                loss="log_loss",  # Logistic regression
                penalty="l2",
                alpha=0.0001,
                learning_rate="adaptive",
                eta0=0.01,
                random_state=42,
            )
        elif self.model_type == "passive_aggressive":
            from sklearn.linear_model import PassiveAggressiveClassifier
            self.model = PassiveAggressiveClassifier(
                C=1.0,
                random_state=42,
            )
        elif self.model_type == "mlp":
            from sklearn.neural_network import MLPClassifier
            self.model = MLPClassifier(
                hidden_layer_sizes=(50, 25),
                max_iter=1,  # Online: 1 iteration per partial_fit
                warm_start=True,
                random_state=42,
            )
        else:
            from sklearn.linear_model import SGDClassifier
            self.model = SGDClassifier(random_state=42)

    def partial_fit(self, X: np.ndarray, y: np.ndarray, classes: Optional[np.ndarray] = None):
        """
        Incrementally fit the model with new data.
        
        Args:
            X: Feature matrix (n_samples, n_features)
            y: Target labels (n_samples,)
            classes: All possible classes (required for first call)
        """
        if classes is not None and self.classes_ is None:
            self.classes_ = classes
        if self.classes_ is None:
            self.classes_ = np.unique(y)

        self.model.partial_fit(X, y, classes=self.classes_)
        self.n_updates += 1

    def predict(self, X: np.ndarray) -> np.ndarray:
        """Predict with online model."""
        if self.model is None:
            return np.array([])
        return self.model.predict(X)

    def predict_proba(self, X: np.ndarray) -> np.ndarray:
        """Predict probabilities."""
        if hasattr(self.model, "predict_proba"):
            return self.model.predict_proba(X)
        # Fallback: convert decision function to pseudo-probabilities
        if hasattr(self.model, "decision_function"):
            scores = self.model.decision_function(X)
            from scipy.special import softmax
            return softmax(scores, axis=1)
        return np.ones((len(X), len(self.classes_))) / len(self.classes_)

    def evaluate(self, X: np.ndarray, y: np.ndarray) -> float:
        """Evaluate and track accuracy."""
        preds = self.predict(X)
        acc = float(np.mean(preds == y))
        self.accuracy_history.append(acc)
        return acc

    def needs_full_retrain(self, threshold: float = 0.1, window: int = 10) -> bool:
        """Check if accuracy has degraded enough to warrant full retrain."""
        if len(self.accuracy_history) < window:
            return False
        recent = self.accuracy_history[-window:]
        baseline = self.accuracy_history[0] if self.accuracy_history else 0.5
        return baseline - np.mean(recent) > threshold

    def save(self, path: Optional[str] = None):
        """Save online model."""
        import joblib
        path = path or os.path.join(MODELS_DIR, "online_model.pkl")
        joblib.dump({
            "model": self.model,
            "classes_": self.classes_,
            "n_updates": self.n_updates,
            "accuracy_history": self.accuracy_history,
            "model_type": self.model_type,
        }, path)

    def load(self, path: Optional[str] = None) -> bool:
        """Load online model."""
        import joblib
        path = path or os.path.join(MODELS_DIR, "online_model.pkl")
        if os.path.exists(path):
            data = joblib.load(path)
            self.model = data["model"]
            self.classes_ = data["classes_"]
            self.n_updates = data["n_updates"]
            self.accuracy_history = data["accuracy_history"]
            self.model_type = data["model_type"]
            return True
        return False

    def get_status(self) -> Dict:
        """Get online learner status."""
        return {
            "model_type": self.model_type,
            "n_updates": self.n_updates,
            "current_accuracy": self.accuracy_history[-1] if self.accuracy_history else None,
            "baseline_accuracy": self.accuracy_history[0] if self.accuracy_history else None,
            "accuracy_trend": self.accuracy_history[-10:],
            "needs_full_retrain": self.needs_full_retrain(),
        }


# =============================================================================
# P11: REINFORCEMENT LEARNING — Q-Learning Trading Agent
# =============================================================================

@dataclass
class TradingState:
    """State representation for RL agent."""
    signal: int  # 0=SELL, 1=HOLD, 2=BUY
    confidence: float  # 0.0-1.0
    sentiment: float  # -100 to 100
    event_risk: float  # 0-100
    has_position: bool  # Currently holding?
    pnl_pct: float  # Current PnL %
    rsi: float  # 0-100
    trend: int  # 0=down, 1=sideways, 2=up

    def to_array(self) -> np.ndarray:
        return np.array([
            self.signal / 2.0,
            self.confidence,
            self.sentiment / 100.0,
            self.event_risk / 100.0,
            1.0 if self.has_position else 0.0,
            self.pnl_pct / 100.0,
            self.rsi / 100.0,
            self.trend / 2.0,
        ])


class TradingRLAgent:
    """
    Q-Learning agent untuk trading decisions.
    
    State: (signal, confidence, sentiment, event_risk, has_position, pnl, rsi, trend)
    Actions: 0=SELL, 1=HOLD, 2=BUY
    Reward: PnL after N periods - transaction cost
    
    Learning:
    - Q(s, a) ← Q(s, a) + α[r + γ max Q(s', a') - Q(s, a)]
    - ε-greedy exploration
    - Experience replay buffer
    """

    def __init__(
        self,
        n_features: int = 8,
        n_actions: int = 3,
        learning_rate: float = 0.01,
        gamma: float = 0.95,  # Discount factor
        epsilon: float = 1.0,  # Exploration rate
        epsilon_min: float = 0.05,
        epsilon_decay: float = 0.995,
    ):
        self.n_features = n_features
        self.n_actions = n_actions
        self.alpha = learning_rate
        self.gamma = gamma
        self.epsilon = epsilon
        self.epsilon_min = epsilon_min
        self.epsilon_decay = epsilon_decay

        # Q-table (discretized) or Q-network
        # Using simple table with discretized states for simplicity
        self.q_table: Dict[Tuple, np.ndarray] = {}
        
        # Experience replay
        self.memory: List[Tuple] = []
        self.memory_size = 10000

        # Training stats
        self.n_episodes = 0
        self.total_reward = 0.0
        self.reward_history: List[float] = []

        # State file
        self.state_file = os.path.join(MODELS_DIR, "rl_agent_state.json")

    def _discretize_state(self, state: np.ndarray) -> Tuple:
        """Discretize continuous state for Q-table lookup."""
        bins = [
            3,  # signal: 0, 1, 2
            5,  # confidence: 0-0.2, 0.2-0.4, 0.4-0.6, 0.6-0.8, 0.8-1.0
            5,  # sentiment: -1 to 1 in 0.4 steps
            4,  # event_risk: 0-0.25, 0.25-0.5, 0.5-0.75, 0.75-1.0
            2,  # has_position: 0 or 1
            5,  # pnl: -1 to 1 in 0.4 steps
            5,  # rsi: 0-0.2, 0.2-0.4, 0.4-0.6, 0.6-0.8, 0.8-1.0
            3,  # trend: 0, 1, 2
        ]
        discretized = []
        for i, (val, n_bins) in enumerate(zip(state, bins)):
            idx = int(np.clip(val * n_bins, 0, n_bins - 1))
            discretized.append(idx)
        return tuple(discretized)

    def _get_q(self, state_key: Tuple) -> np.ndarray:
        """Get Q-values for a state."""
        if state_key not in self.q_table:
            self.q_table[state_key] = np.zeros(self.n_actions)
        return self.q_table[state_key]

    def choose_action(self, state: TradingState, training: bool = True) -> int:
        """
        Choose action using ε-greedy policy.
        
        Returns: 0=SELL, 1=HOLD, 2=BUY
        """
        state_arr = state.to_array()
        state_key = self._discretize_state(state_arr)

        if training and np.random.random() < self.epsilon:
            return np.random.randint(self.n_actions)

        q_values = self._get_q(state_key)
        return int(np.argmax(q_values))

    def learn(
        self,
        state: TradingState,
        action: int,
        reward: float,
        next_state: TradingState,
        done: bool = False,
    ):
        """
        Update Q-values using Q-learning update rule.
        
        Q(s, a) ← Q(s, a) + α[r + γ max Q(s', a') - Q(s, a)]
        """
        state_key = self._discretize_state(state.to_array())
        next_key = self._discretize_state(next_state.to_array())

        current_q = self._get_q(state_key)
        next_q = self._get_q(next_key)

        # Q-learning update
        target = reward + (0 if done else self.gamma * np.max(next_q))
        current_q[action] += self.alpha * (target - current_q[action])

        # Store in memory for experience replay
        self.memory.append((state_key, action, reward, next_key, done))
        if len(self.memory) > self.memory_size:
            self.memory.pop(0)

        # Decay epsilon
        if self.epsilon > self.epsilon_min:
            self.epsilon *= self.epsilon_decay

        # Track stats
        self.total_reward += reward
        self.n_episodes += 1

    def replay_experience(self, batch_size: int = 32):
        """Experience replay — learn from past experiences."""
        if len(self.memory) < batch_size:
            return

        indices = np.random.choice(len(self.memory), batch_size, replace=False)
        for idx in indices:
            state_key, action, reward, next_key, done = self.memory[idx]
            current_q = self._get_q(state_key)
            next_q = self._get_q(next_key)
            target = reward + (0 if done else self.gamma * np.max(next_q))
            current_q[action] += self.alpha * (target - current_q[action])

    def calculate_reward(
        self,
        action: int,
        pnl_pct: float,
        transaction_cost: float = 0.002,  # 0.2% per trade
        has_position: bool = False,
    ) -> float:
        """
        Calculate reward for an action.
        
        - BUY → reward = future return - transaction cost
        - SELL → reward = realized PnL - transaction cost
        - HOLD → reward = unrealized PnL change (small)
        """
        if action == 2:  # BUY
            return pnl_pct - transaction_cost
        elif action == 0:  # SELL
            return pnl_pct - transaction_cost if has_position else -transaction_cost
        else:  # HOLD
            return pnl_pct * 0.1  # Small reward for holding

    def save(self):
        """Save RL agent state."""
        # Convert Q-table keys to strings for JSON
        q_table_serializable = {
            str(k): v.tolist() for k, v in self.q_table.items()
        }
        state = {
            "q_table": q_table_serializable,
            "epsilon": self.epsilon,
            "n_episodes": self.n_episodes,
            "total_reward": self.total_reward,
            "reward_history": self.reward_history[-100:],
        }
        with open(self.state_file, "w") as f:
            json.dump(state, f, indent=2)

    def load(self) -> bool:
        """Load RL agent state."""
        if os.path.exists(self.state_file):
            with open(self.state_file, "r") as f:
                state = json.load(f)
            self.q_table = {
                tuple(int(x) for x in k.strip("()").split(", ")): np.array(v)
                for k, v in state.get("q_table", {}).items()
            }
            self.epsilon = state.get("epsilon", self.epsilon_min)
            self.n_episodes = state.get("n_episodes", 0)
            self.total_reward = state.get("total_reward", 0)
            self.reward_history = state.get("reward_history", [])
            return True
        return False

    def get_status(self) -> Dict:
        """Get RL agent status."""
        return {
            "n_episodes": self.n_episodes,
            "total_reward": self.total_reward,
            "epsilon": self.epsilon,
            "q_table_size": len(self.q_table),
            "memory_size": len(self.memory),
            "avg_reward": np.mean(self.reward_history[-10:]) if self.reward_history else 0,
            "reward_trend": self.reward_history[-10:],
        }

    def get_action_name(self, action: int) -> str:
        """Get human-readable action name."""
        return {0: "SELL", 1: "HOLD", 2: "BUY"}.get(action, "UNKNOWN")
