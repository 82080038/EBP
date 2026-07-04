"""
Market Hours Module — Jadwal bursa seluruh dunia.

Fitur:
- 8 bursa: IDX, NYSE, NASDAQ, TSE, HKEX, SGX, SSE, LSE
- Timezone conversion (WIB ↔ ET ↔ JST ↔ HKT ↔ SGT ↔ CST ↔ GMT)
- Holiday calendar per bursa per tahun
- Market status real-time: open, pre-open, post-close, closed, weekend, holiday
- Next open/close countdown
- 24h global trading session map

Usage:
    from src.market_hours import MarketHours, Exchange
    
    mh = MarketHours()
    status = mh.get_status("IDX")
    all_open = mh.get_all_open_exchanges()
    next_event = mh.get_next_market_event()
"""

from datetime import datetime, timedelta, time
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass, field
from enum import Enum

try:
    from zoneinfo import ZoneInfo
except ImportError:
    from backports.zoneinfo import ZoneInfo


class SessionType(Enum):
    PRE_OPEN = "pre_open"
    OPEN = "open"
    LUNCH_BREAK = "lunch_break"
    POST_CLOSE = "post_close"
    CLOSED = "closed"
    WEEKEND = "weekend"
    HOLIDAY = "holiday"


@dataclass
class ExchangeSchedule:
    """Trading schedule for an exchange."""
    code: str
    name: str
    country: str
    timezone: str
    # Trading sessions (in local time)
    sessions: List[Tuple[time, time]]  # (open, close) pairs
    pre_open: Optional[time] = None
    post_close: Optional[time] = None
    # Days of week that are trading days (0=Monday, 6=Sunday)
    trading_days: set = field(default_factory=lambda: {0, 1, 2, 3, 4})
    # Half-day sessions (e.g., before holidays)
    half_day_sessions: Dict[str, Tuple[time, time]] = field(default_factory=dict)
    # Currency
    currency: str = "IDR"
    # Suffix for Yahoo Finance tickers
    yf_suffix: str = ".JK"


@dataclass
class MarketStatus:
    """Real-time market status for an exchange."""
    exchange: str
    session: SessionType
    is_open: bool
    can_trade: bool
    local_time: str
    wib_time: str
    next_open: Optional[str] = None
    next_close: Optional[str] = None
    countdown_open: Optional[str] = None
    countdown_close: Optional[str] = None
    is_holiday: bool = False
    holiday_name: str = ""


# =============================================================================
# EXCHANGE DEFINITIONS
# =============================================================================

EXCHANGES: Dict[str, ExchangeSchedule] = {
    "IDX": ExchangeSchedule(
        code="IDX",
        name="Indonesia Stock Exchange (BEI)",
        country="Indonesia",
        timezone="Asia/Jakarta",
        sessions=[(time(9, 0), time(15, 0))],
        pre_open=time(8, 45),
        post_close=time(15, 15),
        currency="IDR",
        yf_suffix=".JK",
    ),
    "NYSE": ExchangeSchedule(
        code="NYSE",
        name="New York Stock Exchange",
        country="United States",
        timezone="America/New_York",
        sessions=[(time(9, 30), time(16, 0))],
        pre_open=time(9, 0),
        post_close=time(16, 30),
        trading_days={0, 1, 2, 3, 4},
        currency="USD",
        yf_suffix="",
    ),
    "NASDAQ": ExchangeSchedule(
        code="NASDAQ",
        name="Nasdaq Stock Market",
        country="United States",
        timezone="America/New_York",
        sessions=[(time(9, 30), time(16, 0))],
        pre_open=time(9, 0),
        post_close=time(16, 30),
        trading_days={0, 1, 2, 3, 4},
        currency="USD",
        yf_suffix="",
    ),
    "TSE": ExchangeSchedule(
        code="TSE",
        name="Tokyo Stock Exchange",
        country="Japan",
        timezone="Asia/Tokyo",
        sessions=[(time(9, 0), time(11, 30)), (time(12, 30), time(15, 0))],
        pre_open=time(8, 45),
        post_close=time(15, 15),
        trading_days={0, 1, 2, 3, 4},
        currency="JPY",
        yf_suffix=".T",
    ),
    "HKEX": ExchangeSchedule(
        code="HKEX",
        name="Hong Kong Stock Exchange",
        country="Hong Kong",
        timezone="Asia/Hong_Kong",
        sessions=[(time(9, 30), time(12, 0)), (time(13, 0), time(16, 0))],
        pre_open=time(9, 15),
        post_close=time(16, 15),
        trading_days={0, 1, 2, 3, 4},
        currency="HKD",
        yf_suffix=".HK",
    ),
    "SGX": ExchangeSchedule(
        code="SGX",
        name="Singapore Exchange",
        country="Singapore",
        timezone="Asia/Singapore",
        sessions=[(time(9, 0), time(12, 0)), (time(13, 0), time(17, 0))],
        pre_open=time(8, 45),
        post_close=time(17, 15),
        trading_days={0, 1, 2, 3, 4},
        currency="SGD",
        yf_suffix=".SI",
    ),
    "SSE": ExchangeSchedule(
        code="SSE",
        name="Shanghai Stock Exchange",
        country="China",
        timezone="Asia/Shanghai",
        sessions=[(time(9, 30), time(11, 30)), (time(13, 0), time(15, 0))],
        pre_open=time(9, 15),
        post_close=time(15, 15),
        trading_days={0, 1, 2, 3, 4},
        currency="CNY",
        yf_suffix=".SS",
    ),
    "LSE": ExchangeSchedule(
        code="LSE",
        name="London Stock Exchange",
        country="United Kingdom",
        timezone="Europe/London",
        sessions=[(time(8, 0), time(16, 30))],
        pre_open=time(7, 50),
        post_close=time(16, 45),
        trading_days={0, 1, 2, 3, 4},
        currency="GBP",
        yf_suffix=".L",
    ),
}


# =============================================================================
# HOLIDAY CALENDARS (simplified — key holidays per exchange per year)
# =============================================================================

def _get_us_holidays(year: int) -> Dict[str, datetime]:
    """US market holidays (NYSE/NASDAQ)."""
    holidays = {}

    # Fixed date holidays
    holidays["New Year's Day"] = datetime(year, 1, 1)
    holidays["Independence Day"] = datetime(year, 7, 4)
    holidays["Christmas"] = datetime(year, 12, 25)

    # Floating holidays (approximate — actual dates vary)
    # MLK Day: 3rd Monday of January
    mlk = _nth_weekday(year, 1, 3, 0)  # Monday=0
    holidays["MLK Day"] = datetime(year, 1, mlk)

    # Presidents Day: 3rd Monday of February
    pres = _nth_weekday(year, 2, 3, 0)
    holidays["Presidents Day"] = datetime(year, 2, pres)

    # Memorial Day: last Monday of May
    mem = _last_weekday(year, 5, 0)
    holidays["Memorial Day"] = datetime(year, 5, mem)

    # Labor Day: 1st Monday of September
    labor = _nth_weekday(year, 9, 1, 0)
    holidays["Labor Day"] = datetime(year, 9, labor)

    # Thanksgiving: 4th Thursday of November
    thanks = _nth_weekday(year, 11, 4, 3)  # Thursday=3
    holidays["Thanksgiving"] = datetime(year, 11, thanks)

    # Good Friday (approximate — 2025: Apr 18, 2026: Apr 3)
    good_friday = {
        2024: datetime(2024, 3, 29),
        2025: datetime(2025, 4, 18),
        2026: datetime(2026, 4, 3),
        2027: datetime(2027, 3, 26),
    }
    if year in good_friday:
        holidays["Good Friday"] = good_friday[year]

    # Juneteenth: June 19
    holidays["Juneteenth"] = datetime(year, 6, 19)

    return holidays


def _get_japan_holidays(year: int) -> Dict[str, datetime]:
    """Japan market holidays (TSE)."""
    holidays = {
        "New Year": datetime(year, 1, 1),
        "Coming of Age Day": datetime(year, 1, _nth_weekday(year, 1, 2, 0)),
        "National Foundation Day": datetime(year, 2, 11),
        "Emperor's Birthday": datetime(year, 2, 23),
        "Golden Week - Showa Day": datetime(year, 4, 29),
        "Golden Week - Constitution Day": datetime(year, 5, 3),
        "Golden Week - Greenery Day": datetime(year, 5, 4),
        "Golden Week - Children's Day": datetime(year, 5, 5),
        "Marine Day": datetime(year, 7, _nth_weekday(year, 7, 3, 0)),
        "Mountain Day": datetime(year, 8, 11),
        "Respect for the Aged Day": datetime(year, 9, _nth_weekday(year, 9, 3, 0)),
        "Autumn Equinox": datetime(year, 9, 23),
        "Culture Day": datetime(year, 11, 3),
        "Labor Thanksgiving Day": datetime(year, 11, 23),
        "Christmas (market closed)": datetime(year, 12, 23),
        "Year End": datetime(year, 12, 31),
    }
    return holidays


def _get_hk_holidays(year: int) -> Dict[str, datetime]:
    """Hong Kong market holidays (HKEX)."""
    holidays = {
        "New Year": datetime(year, 1, 1),
        "Chinese New Year (1)": datetime(year, 2, 17) if year == 2026 else datetime(year, 1, 29),
        "Chinese New Year (2)": datetime(year, 2, 18) if year == 2026 else datetime(year, 1, 30),
        "Chinese New Year (3)": datetime(year, 2, 19) if year == 2026 else datetime(year, 1, 31),
        "Ching Ming": datetime(year, 4, 5),
        "Good Friday": datetime(year, 4, 18) if year == 2025 else datetime(year, 4, 3) if year == 2026 else datetime(year, 3, 29),
        "Easter Monday": datetime(year, 4, 21) if year == 2025 else datetime(year, 4, 6) if year == 2026 else datetime(year, 4, 1),
        "Labour Day": datetime(year, 5, 1),
        "Buddha's Birthday": datetime(year, 5, _nth_weekday(year, 5, 1, 0) + 7),  # Approx
        "Tuen Ng (Dragon Boat)": datetime(year, 6, _nth_weekday(year, 6, 2, 0) + 7),  # Approx
        "HK SAR Establishment Day": datetime(year, 7, 1),
        "Mid-Autumn Festival": datetime(year, 9, _nth_weekday(year, 9, 2, 0) + 14),  # Approx
        "National Day": datetime(year, 10, 1),
        "Christmas": datetime(year, 12, 25),
        "Boxing Day": datetime(year, 12, 26),
    }
    return holidays


def _get_sg_holidays(year: int) -> Dict[str, datetime]:
    """Singapore market holidays (SGX)."""
    holidays = {
        "New Year": datetime(year, 1, 1),
        "Chinese New Year (1)": datetime(year, 2, 17) if year == 2026 else datetime(year, 1, 29),
        "Chinese New Year (2)": datetime(year, 2, 18) if year == 2026 else datetime(year, 1, 30),
        "Good Friday": datetime(year, 4, 18) if year == 2025 else datetime(year, 4, 3) if year == 2026 else datetime(year, 3, 29),
        "Labour Day": datetime(year, 5, 1),
        "Vesak Day": datetime(year, 5, _nth_weekday(year, 5, 1, 0) + 14),  # Approx
        "Hari Raya Puasa": datetime(year, 3, 31) if year == 2025 else datetime(year, 3, 20) if year == 2026 else datetime(year, 4, 10),
        "Hari Raya Haji": datetime(year, 6, 7) if year == 2025 else datetime(year, 5, 27) if year == 2026 else datetime(year, 6, 16),
        "National Day": datetime(year, 8, 9),
        "Deepavali": datetime(year, 10, 20) if year == 2025 else datetime(year, 11, 8) if year == 2026 else datetime(year, 11, 1),
        "Christmas": datetime(year, 12, 25),
    }
    return holidays


def _get_china_holidays(year: int) -> Dict[str, datetime]:
    """China market holidays (SSE)."""
    holidays = {
        "New Year": datetime(year, 1, 1),
        "Chinese New Year (1)": datetime(year, 2, 17) if year == 2026 else datetime(year, 1, 28) if year == 2025 else datetime(year, 2, 10),
        "Chinese New Year (2)": datetime(year, 2, 18) if year == 2026 else datetime(year, 1, 29) if year == 2025 else datetime(year, 2, 11),
        "Chinese New Year (3)": datetime(year, 2, 19) if year == 2026 else datetime(year, 1, 30) if year == 2025 else datetime(year, 2, 12),
        "Qingming": datetime(year, 4, 5),
        "Labour Day": datetime(year, 5, 1),
        "Dragon Boat": datetime(year, 6, _nth_weekday(year, 6, 2, 0) + 7),
        "Mid-Autumn": datetime(year, 9, _nth_weekday(year, 9, 2, 0) + 14),
        "National Day": datetime(year, 10, 1),
        "National Day (2)": datetime(year, 10, 2),
        "National Day (3)": datetime(year, 10, 3),
    }
    return holidays


def _get_uk_holidays(year: int) -> Dict[str, datetime]:
    """UK market holidays (LSE)."""
    holidays = {
        "New Year": datetime(year, 1, 1),
        "Good Friday": datetime(year, 4, 18) if year == 2025 else datetime(year, 4, 3) if year == 2026 else datetime(year, 3, 29),
        "Easter Monday": datetime(year, 4, 21) if year == 2025 else datetime(year, 4, 6) if year == 2026 else datetime(year, 4, 1),
        "Early May Bank Holiday": datetime(year, 5, _nth_weekday(year, 5, 1, 0)),
        "Spring Bank Holiday": datetime(year, 5, _last_weekday(year, 5, 0)),
        "Summer Bank Holiday": datetime(year, 8, _last_weekday(year, 8, 0)),
        "Christmas": datetime(year, 12, 25),
        "Boxing Day": datetime(year, 12, 26),
    }
    return holidays


def _get_idx_holidays(year: int) -> Dict[str, datetime]:
    """Indonesia market holidays (IDX)."""
    holidays = {
        "New Year": datetime(year, 1, 1),
        "Isra Mi'raj": datetime(year, 1, 27) if year == 2025 else datetime(year, 1, 16) if year == 2026 else datetime(year, 2, 8),
        "Chinese New Year": datetime(year, 1, 29) if year == 2025 else datetime(year, 2, 17) if year == 2026 else datetime(year, 2, 10),
        "Nyepi": datetime(year, 3, 29) if year == 2025 else datetime(year, 3, 19) if year == 2026 else datetime(year, 3, 11),
        "Eid al-Fitr (1)": datetime(year, 3, 31) if year == 2025 else datetime(year, 3, 20) if year == 2026 else datetime(year, 4, 10),
        "Eid al-Fitr (2)": datetime(year, 4, 1) if year == 2025 else datetime(year, 3, 21) if year == 2026 else datetime(year, 4, 11),
        "Good Friday": datetime(year, 4, 18) if year == 2025 else datetime(year, 4, 3) if year == 2026 else datetime(year, 3, 29),
        "Labour Day": datetime(year, 5, 1),
        "Ascension Day": datetime(year, 5, 12) if year == 2025 else datetime(year, 5, 1) if year == 2026 else datetime(year, 5, 9),
        "Pancasila Day": datetime(year, 6, 1),
        "Eid al-Adha": datetime(year, 6, 6) if year == 2025 else datetime(year, 5, 26) if year == 2026 else datetime(year, 6, 16),
        "Independence Day": datetime(year, 8, 17),
        "Islamic New Year": datetime(year, 9, 5) if year == 2025 else datetime(year, 6, 26) if year == 2026 else datetime(year, 7, 7),
        "Prophet Muhammad's Birthday": datetime(year, 10, 2) if year == 2025 else datetime(year, 9, 4) if year == 2026 else datetime(year, 9, 15),
        "Christmas": datetime(year, 12, 25),
    }
    return holidays


def _nth_weekday(year: int, month: int, n: int, weekday: int) -> int:
    """Get the day of the nth weekday in a month. weekday: 0=Monday, 6=Sunday."""
    from calendar import monthrange
    _, days_in_month = monthrange(year, month)
    count = 0
    for day in range(1, days_in_month + 1):
        if datetime(year, month, day).weekday() == weekday:
            count += 1
            if count == n:
                return day
    return 1


def _last_weekday(year: int, month: int, weekday: int) -> int:
    """Get the day of the last weekday in a month."""
    from calendar import monthrange
    _, days_in_month = monthrange(year, month)
    for day in range(days_in_month, 0, -1):
        if datetime(year, month, day).weekday() == weekday:
            return day
    return 1


HOLIDAY_FUNCTIONS = {
    "IDX": _get_idx_holidays,
    "NYSE": _get_us_holidays,
    "NASDAQ": _get_us_holidays,
    "TSE": _get_japan_holidays,
    "HKEX": _get_hk_holidays,
    "SGX": _get_sg_holidays,
    "SSE": _get_china_holidays,
    "LSE": _get_uk_holidays,
}


# =============================================================================
# MARKET HOURS ENGINE
# =============================================================================

class MarketHours:
    """
    Global market hours engine — track all exchanges, timezones, holidays.
    
    Supports 8 exchanges: IDX, NYSE, NASDAQ, TSE, HKEX, SGX, SSE, LSE.
    """

    def __init__(self):
        self.exchanges = EXCHANGES
        self._holiday_cache: Dict[Tuple[str, int], Dict[str, datetime]] = {}

    def _get_holidays(self, exchange: str, year: int) -> Dict[str, datetime]:
        """Get holidays for an exchange in a given year (cached)."""
        key = (exchange, year)
        if key not in self._holiday_cache:
            func = HOLIDAY_FUNCTIONS.get(exchange)
            if func:
                self._holiday_cache[key] = func(year)
            else:
                self._holiday_cache[key] = {}
        return self._holiday_cache[key]

    def _is_holiday(self, exchange: str, dt: datetime) -> Tuple[bool, str]:
        """Check if a date is a holiday for an exchange."""
        holidays = self._get_holidays(exchange, dt.year)
        for name, h_date in holidays.items():
            if dt.date() == h_date.date():
                return True, name
        return False, ""

    def _get_local_time(self, exchange: str) -> datetime:
        """Get current time in exchange's local timezone."""
        tz = ZoneInfo(self.exchanges[exchange].timezone)
        return datetime.now(tz)

    def _get_wib_time(self) -> datetime:
        """Get current time in WIB (Asia/Jakarta)."""
        return datetime.now(ZoneInfo("Asia/Jakarta"))

    def get_status(self, exchange: str) -> MarketStatus:
        """
        Get real-time market status for an exchange.
        
        Returns MarketStatus with session type, open/close info, countdown.
        """
        sched = self.exchanges.get(exchange)
        if not sched:
            return MarketStatus(
                exchange=exchange,
                session=SessionType.CLOSED,
                is_open=False,
                can_trade=False,
                local_time="N/A",
                wib_time=self._get_wib_time().strftime("%H:%M"),
            )

        local_now = self._get_local_time(exchange)
        wib_now = self._get_wib_time()
        local_time_str = local_now.strftime("%H:%M")
        wib_time_str = wib_now.strftime("%H:%M")

        # Check weekend
        if local_now.weekday() not in sched.trading_days:
            return MarketStatus(
                exchange=exchange,
                session=SessionType.WEEKEND,
                is_open=False,
                can_trade=False,
                local_time=local_time_str,
                wib_time=wib_time_str,
                next_open=self._next_open_str(exchange, local_now),
                countdown_open=self._countdown_to_open(exchange, local_now),
            )

        # Check holiday
        is_hol, hol_name = self._is_holiday(exchange, local_now)
        if is_hol:
            return MarketStatus(
                exchange=exchange,
                session=SessionType.HOLIDAY,
                is_open=False,
                can_trade=False,
                local_time=local_time_str,
                wib_time=wib_time_str,
                is_holiday=True,
                holiday_name=hol_name,
                next_open=self._next_open_str(exchange, local_now),
                countdown_open=self._countdown_to_open(exchange, local_now),
            )

        current_time = local_now.time()
        current_time.hour * 60 + current_time.minute

        # Check pre-open
        if sched.pre_open and current_time >= sched.pre_open:
            first_open = sched.sessions[0][0]
            if current_time < first_open:
                return MarketStatus(
                    exchange=exchange,
                    session=SessionType.PRE_OPEN,
                    is_open=False,
                    can_trade=False,
                    local_time=local_time_str,
                    wib_time=wib_time_str,
                    next_open=first_open.strftime("%H:%M"),
                    countdown_open=self._countdown_to(exchange, local_now, first_open),
                )

        # Check post-close
        last_close = sched.sessions[-1][1]
        if sched.post_close and current_time > last_close and current_time <= sched.post_close:
            return MarketStatus(
                exchange=exchange,
                session=SessionType.POST_CLOSE,
                is_open=False,
                can_trade=False,
                local_time=local_time_str,
                wib_time=wib_time_str,
                next_open=self._next_open_str(exchange, local_now),
                countdown_open=self._countdown_to_open(exchange, local_now),
            )

        # Check trading sessions
        for i, (open_t, close_t) in enumerate(sched.sessions):
            if current_time >= open_t and current_time < close_t:
                return MarketStatus(
                    exchange=exchange,
                    session=SessionType.OPEN,
                    is_open=True,
                    can_trade=True,
                    local_time=local_time_str,
                    wib_time=wib_time_str,
                    next_close=close_t.strftime("%H:%M"),
                    countdown_close=self._countdown_to(exchange, local_now, close_t),
                )
            # Check lunch break (between sessions)
            if i < len(sched.sessions) - 1:
                next_open = sched.sessions[i + 1][0]
                if current_time >= close_t and current_time < next_open:
                    return MarketStatus(
                        exchange=exchange,
                        session=SessionType.LUNCH_BREAK,
                        is_open=False,
                        can_trade=False,
                        local_time=local_time_str,
                        wib_time=wib_time_str,
                        next_open=next_open.strftime("%H:%M"),
                        countdown_open=self._countdown_to(exchange, local_now, next_open),
                    )

        # After post-close or before pre-open
        return MarketStatus(
            exchange=exchange,
            session=SessionType.CLOSED,
            is_open=False,
            can_trade=False,
            local_time=local_time_str,
            wib_time=wib_time_str,
            next_open=self._next_open_str(exchange, local_now),
            countdown_open=self._countdown_to_open(exchange, local_now),
        )

    def _countdown_to(self, exchange: str, from_dt: datetime, target_time: time) -> str:
        """Countdown from current time to target time today."""
        target_dt = from_dt.replace(hour=target_time.hour, minute=target_time.minute, second=0)
        if target_dt <= from_dt:
            target_dt += timedelta(days=1)
        delta = target_dt - from_dt
        hours = int(delta.total_seconds() // 3600)
        minutes = int((delta.total_seconds() % 3600) // 60)
        return f"{hours}h {minutes}m"

    def _next_open_str(self, exchange: str, from_dt: datetime) -> str:
        """Get next open time string."""
        sched = self.exchanges[exchange]
        # Find next trading day
        check_dt = from_dt
        for _ in range(7):  # Check up to 7 days ahead
            check_dt += timedelta(days=1)
            check_dt = check_dt.replace(hour=sched.sessions[0][0].hour, minute=sched.sessions[0][0].minute)
            if check_dt.weekday() in sched.trading_days:
                is_hol, _ = self._is_holiday(exchange, check_dt)
                if not is_hol:
                    # Convert to WIB
                    wib_tz = ZoneInfo("Asia/Jakarta")
                    local_tz = ZoneInfo(sched.timezone)
                    wib_dt = check_dt.replace(tzinfo=local_tz).astimezone(wib_tz)
                    return wib_dt.strftime("%a %H:%M WIB")
        return "Unknown"

    def _countdown_to_open(self, exchange: str, from_dt: datetime) -> str:
        """Countdown to next market open."""
        sched = self.exchanges[exchange]
        check_dt = from_dt
        for _ in range(7):
            # If today and before open, target today's open
            if check_dt.date() == from_dt.date() and check_dt.weekday() in sched.trading_days:
                is_hol, _ = self._is_holiday(exchange, check_dt)
                if not is_hol:
                    open_t = sched.sessions[0][0]
                    target = check_dt.replace(hour=open_t.hour, minute=open_t.minute, second=0)
                    if target > from_dt:
                        delta = target - from_dt
                        hours = int(delta.total_seconds() // 3600)
                        minutes = int((delta.total_seconds() % 3600) // 60)
                        return f"{hours}h {minutes}m"
            check_dt += timedelta(days=1)
            check_dt = check_dt.replace(hour=sched.sessions[0][0].hour, minute=sched.sessions[0][0].minute, second=0)
            if check_dt.weekday() in sched.trading_days:
                is_hol, _ = self._is_holiday(exchange, check_dt)
                if not is_hol:
                    delta = check_dt - from_dt
                    hours = int(delta.total_seconds() // 3600)
                    minutes = int((delta.total_seconds() % 3600) // 60)
                    return f"{hours}h {minutes}m"
        return "N/A"

    def get_all_status(self) -> Dict[str, MarketStatus]:
        """Get status for all exchanges."""
        return {code: self.get_status(code) for code in self.exchanges}

    def get_all_open_exchanges(self) -> List[str]:
        """Get list of currently open exchanges."""
        return [
            code for code, status in self.get_all_status().items()
            if status.is_open
        ]

    def get_all_tradable_exchanges(self) -> List[str]:
        """Get list of exchanges where trading is possible (open or pre-open)."""
        return [
            code for code, status in self.get_all_status().items()
            if status.can_trade or status.session == SessionType.PRE_OPEN
        ]

    def is_any_market_open(self) -> bool:
        """Check if any exchange is currently open."""
        return len(self.get_all_open_exchanges()) > 0

    def get_next_market_event(self) -> Optional[Tuple[str, str, str]]:
        """
        Get the next market event (open or close) across all exchanges.
        
        Returns: (exchange, event_type, countdown) or None
        """
        all_status = self.get_all_status()
        next_event = None
        min_countdown = float('inf')

        for code, status in all_status.items():
            if status.is_open and status.countdown_close:
                # Market closing soon
                mins = self._parse_countdown(status.countdown_close)
                if mins < min_countdown:
                    min_countdown = mins
                    next_event = (code, "CLOSE", status.countdown_close)
            elif status.countdown_open:
                mins = self._parse_countdown(status.countdown_open)
                if mins < min_countdown:
                    min_countdown = mins
                    next_event = (code, "OPEN", status.countdown_open)

        return next_event

    def _parse_countdown(self, countdown: str) -> float:
        """Parse 'Xh Ym' to minutes."""
        try:
            parts = countdown.replace("h", "").replace("m", "").split()
            hours = int(parts[0]) if len(parts) > 0 else 0
            minutes = int(parts[1]) if len(parts) > 1 else 0
            return hours * 60 + minutes
        except (ValueError, IndexError):
            return float('inf')

    def get_exchange_for_ticker(self, ticker: str) -> Optional[str]:
        """Determine which exchange a ticker belongs to."""
        for code, sched in self.exchanges.items():
            if sched.yf_suffix and ticker.endswith(sched.yf_suffix):
                return code
            # US tickers have no suffix
            if code in ("NYSE", "NASDAQ") and "." not in ticker and "^" not in ticker:
                # Could be US — check against known US tickers
                if ticker in US_BLUE_CHIPS:
                    return "NYSE"  # Default to NYSE
        return None

    def get_24h_schedule(self) -> List[Dict]:
        """
        Get 24-hour schedule showing which exchanges are open at each hour (WIB).
        
        Returns list of {hour, exchanges_open, exchanges_pre_open}
        """
        wib_tz = ZoneInfo("Asia/Jakarta")
        now = datetime.now(wib_tz)
        schedule = []

        for hour in range(24):
            check_time = now.replace(hour=hour, minute=0, second=0, microsecond=0)
            open_list = []
            pre_open_list = []

            for code, sched in self.exchanges.items():
                local_tz = ZoneInfo(sched.timezone)
                local_time = check_time.astimezone(local_tz)

                if local_time.weekday() not in sched.trading_days:
                    continue
                is_hol, _ = self._is_holiday(code, local_time)
                if is_hol:
                    continue

                current_t = local_time.time()
                for open_t, close_t in sched.sessions:
                    if open_t <= current_t < close_t:
                        open_list.append(code)
                        break
                if sched.pre_open and sched.pre_open <= current_t < sched.sessions[0][0]:
                    pre_open_list.append(code)

            schedule.append({
                "hour_wib": f"{hour:02d}:00",
                "exchanges_open": open_list,
                "exchanges_pre_open": pre_open_list,
            })

        return schedule


# =============================================================================
# MULTI-COUNTRY BLUE CHIPS
# =============================================================================

US_BLUE_CHIPS = {
    "AAPL": "Apple",
    "MSFT": "Microsoft",
    "GOOGL": "Alphabet",
    "AMZN": "Amazon",
    "NVDA": "NVIDIA",
    "META": "Meta Platforms",
    "TSLA": "Tesla",
    "JPM": "JPMorgan Chase",
}

JAPAN_BLUE_CHIPS = {
    "7203.T": "Toyota Motor",
    "9984.T": "SoftBank Group",
    "6861.T": "Keyence",
    "8306.T": "Mitsubishi UFJ",
    "7974.T": "Nintendo",
    "6758.T": "Sony Group",
    "8316.T": "Sumitomo Mitsui",
    "9433.T": "KDDI",
}

HK_BLUE_CHIPS = {
    "0700.HK": "Tencent Holdings",
    "9988.HK": "Alibaba Group",
    "1299.HK": "AIA Group",
    "0939.HK": "China Construction Bank",
    "0005.HK": "HSBC Holdings",
    "3690.HK": "Meituan",
    "1810.HK": "Xiaomi",
    "2318.HK": "Ping An Insurance",
}

SG_BLUE_CHIPS = {
    "D05.SI": "DBS Group",
    "O39.SI": "OCBC Bank",
    "U11.SI": "UOB",
    "Z74.SI": "Singtel",
    "C6L.SI": "Singapore Airlines",
    "F34.SI": "Wilmar International",
    "C38U.SI": "CapitaLand Integrated",
    "S58.SI": "Sembcorp Industries",
}

MULTI_COUNTRY_BLUE_CHIPS = {
    "US": US_BLUE_CHIPS,
    "Japan": JAPAN_BLUE_CHIPS,
    "HongKong": HK_BLUE_CHIPS,
    "Singapore": SG_BLUE_CHIPS,
}
