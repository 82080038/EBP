"""
Corporate action support for Indonesian stocks.
Handles dividends, stock splits, rights issues, and buybacks.
"""
from dataclasses import dataclass
from datetime import datetime
from typing import List, Dict, Optional
import pandas as pd


@dataclass
class CorporateAction:
    """Represents a corporate action event."""
    ticker: str
    action_type: str  # dividend, split, rights_issue, buyback
    announcement_date: str
    effective_date: str
    details: Dict
    impact_factor: float  # Expected price impact multiplier


class CorporateActionTracker:
    """Track and analyze corporate actions for Indonesian stocks."""
    
    def __init__(self):
        self.actions: List[CorporateAction] = []
        self._load_sample_data()
    
    def _load_sample_data(self):
        """Load sample corporate action data for IDX stocks."""
        # Sample dividend data
        sample_dividends = [
            {
                "ticker": "BBCA.JK",
                "action_type": "dividend",
                "announcement_date": "2024-06-15",
                "effective_date": "2024-07-10",
                "details": {"dividend_per_share": 150, "currency": "IDR"},
                "impact_factor": -0.02  # Price typically drops by dividend amount
            },
            {
                "ticker": "BBRI.JK",
                "action_type": "dividend",
                "announcement_date": "2024-06-20",
                "effective_date": "2024-07-15",
                "details": {"dividend_per_share": 80, "currency": "IDR"},
                "impact_factor": -0.015
            },
            {
                "ticker": "UNVR.JK",
                "action_type": "dividend",
                "announcement_date": "2024-06-25",
                "effective_date": "2024-08-01",
                "details": {"dividend_per_share": 2500, "currency": "IDR"},
                "impact_factor": -0.03
            }
        ]
        
        # Sample stock split data
        sample_splits = [
            {
                "ticker": "GOTO.JK",
                "action_type": "split",
                "announcement_date": "2024-05-01",
                "effective_date": "2024-05-20",
                "details": {"split_ratio": "5:1", "old_price": 250, "new_price": 50},
                "impact_factor": 0.0  # Price adjusts proportionally
            }
        ]
        
        # Sample buyback data
        sample_buybacks = [
            {
                "ticker": "ASII.JK",
                "action_type": "buyback",
                "announcement_date": "2024-06-10",
                "effective_date": "2024-06-15",
                "details": {"shares": 10000000, "max_price": 6500, "budget": 65000000000},
                "impact_factor": 0.01  # Slight positive impact
            }
        ]
        
        for action in sample_dividends + sample_splits + sample_buybacks:
            self.actions.append(CorporateAction(**action))
    
    def get_actions_for_ticker(self, ticker: str) -> List[CorporateAction]:
        """Get all corporate actions for a specific ticker."""
        return [a for a in self.actions if a.ticker == ticker]
    
    def get_upcoming_actions(self, days: int = 30) -> List[CorporateAction]:
        """Get corporate actions within the next N days."""
        cutoff_date = datetime.now().date()
        upcoming = []
        
        for action in self.actions:
            effective_date = datetime.strptime(action.effective_date, "%Y-%m-%d").date()
            if effective_date >= cutoff_date:
                days_until = (effective_date - cutoff_date).days
                if days_until <= days:
                    upcoming.append(action)
        
        return sorted(upcoming, key=lambda x: x.effective_date)
    
    def calculate_adjusted_price(self, ticker: str, current_price: float) -> float:
        """Calculate price adjustment based on corporate actions."""
        actions = self.get_actions_for_ticker(ticker)
        adjusted_price = current_price
        
        for action in actions:
            if action.action_type == "dividend":
                dividend = action.details.get("dividend_per_share", 0)
                adjusted_price -= dividend
            elif action.action_type == "split":
                split_ratio = action.details.get("split_ratio", "1:1")
                old_ratio, new_ratio = map(int, split_ratio.split(":"))
                adjusted_price = adjusted_price * new_ratio / old_ratio
        
        return max(adjusted_price, 0)
    
    def get_action_impact(self, ticker: str) -> Dict:
        """Get summary of corporate action impact for a ticker."""
        actions = self.get_actions_for_ticker(ticker)
        
        if not actions:
            return {"has_actions": False, "message": "No corporate actions"}
        
        total_impact = sum(a.impact_factor for a in actions)
        latest_action = max(actions, key=lambda x: x.effective_date)
        
        return {
            "has_actions": True,
            "ticker": ticker,
            "action_count": len(actions),
            "total_impact_factor": total_impact,
            "latest_action": {
                "type": latest_action.action_type,
                "effective_date": latest_action.effective_date,
                "details": latest_action.details
            },
            "recommendation": self._get_recommendation(total_impact)
        }
    
    def _get_recommendation(self, impact_factor: float) -> str:
        """Get trading recommendation based on impact factor."""
        if impact_factor > 0.02:
            return "Positive sentiment - consider accumulation"
        elif impact_factor < -0.02:
            return "Negative sentiment - exercise caution"
        else:
            return "Neutral impact - no significant effect"
    
    def to_dataframe(self) -> pd.DataFrame:
        """Convert all corporate actions to DataFrame."""
        data = []
        for action in self.actions:
            data.append({
                "ticker": action.ticker,
                "action_type": action.action_type,
                "announcement_date": action.announcement_date,
                "effective_date": action.effective_date,
                "impact_factor": action.impact_factor,
                "details": str(action.details)
            })
        
        return pd.DataFrame(data)


# Global instance
_tracker = CorporateActionTracker()


def get_corporate_actions() -> CorporateActionTracker:
    """Get the global corporate action tracker instance."""
    return _tracker


def analyze_corporate_action_impact(ticker: str, current_price: float) -> Dict:
    """Analyze corporate action impact for a ticker."""
    tracker = get_corporate_actions()
    impact = tracker.get_action_impact(ticker)
    adjusted_price = tracker.calculate_adjusted_price(ticker, current_price)
    
    impact["current_price"] = current_price
    impact["adjusted_price"] = adjusted_price
    impact["price_adjustment"] = adjusted_price - current_price
    
    return impact
