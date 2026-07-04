"""
Real-time updates using Server-Sent Events (SSE).
Provides live data streaming for market data, predictions, and notifications.
"""
from fastapi import APIRouter
from sse_starlette.sse import EventSourceResponse
from typing import AsyncGenerator
import asyncio
import json
from datetime import datetime

router = APIRouter(prefix="/realtime", tags=["realtime"])


async def market_data_stream() -> AsyncGenerator[dict, None]:
    """Stream real-time market data updates."""
    while True:
        # Simulate real-time data - in production, fetch from cache or database
        data = {
            "type": "market_data",
            "timestamp": datetime.now().isoformat(),
            "data": {
                "ticker": "^JKSE",
                "price": 7200.0 + (hash(datetime.now().isoformat()) % 100) / 10,
                "change": 0.5,
                "volume": 1000000
            }
        }
        yield data
        await asyncio.sleep(2)  # Update every 2 seconds


async def prediction_stream(ticker: str = "^JKSE") -> AsyncGenerator[dict, None]:
    """Stream real-time prediction updates."""
    while True:
        # Simulate prediction updates - in production, fetch from database
        data = {
            "type": "prediction",
            "timestamp": datetime.now().isoformat(),
            "ticker": ticker,
            "data": {
                "prediction": 7250.0,
                "confidence": 0.75,
                "signal": "BUY"
            }
        }
        yield data
        await asyncio.sleep(5)  # Update every 5 seconds


async def notification_stream() -> AsyncGenerator[dict, None]:
    """Stream real-time notification updates."""
    while True:
        # Simulate notification updates - in production, fetch from database
        data = {
            "type": "notification",
            "timestamp": datetime.now().isoformat(),
            "data": {
                "message": "New trading signal generated",
                "level": "info"
            }
        }
        yield data
        await asyncio.sleep(10)  # Update every 10 seconds


@router.get("/market")
async def stream_market_data():
    """Stream real-time market data."""
    async def event_generator():
        async for data in market_data_stream():
            yield {
                "event": "market_update",
                "data": json.dumps(data)
            }
    
    return EventSourceResponse(event_generator())


@router.get("/predictions/{ticker}")
async def stream_predictions(ticker: str = "^JKSE"):
    """Stream real-time prediction updates for a ticker."""
    async def event_generator():
        async for data in prediction_stream(ticker):
            yield {
                "event": "prediction_update",
                "data": json.dumps(data)
            }
    
    return EventSourceResponse(event_generator())


@router.get("/notifications")
async def stream_notifications():
    """Stream real-time notification updates."""
    async def event_generator():
        async for data in notification_stream():
            yield {
                "event": "notification",
                "data": json.dumps(data)
            }
    
    return EventSourceResponse(event_generator())


@router.get("/all")
async def stream_all_updates():
    """Stream all real-time updates (market, predictions, notifications)."""
    async def event_generator():
        # Merge all streams
        market_gen = market_data_stream()
        prediction_gen = prediction_stream()
        notification_gen = notification_stream()
        
        while True:
            # Yield market data
            try:
                data = await asyncio.anext(market_gen)
                yield {
                    "event": "market_update",
                    "data": json.dumps(data)
                }
            except StopAsyncIteration:
                break
            
            # Yield prediction data
            try:
                data = await asyncio.anext(prediction_gen)
                yield {
                    "event": "prediction_update",
                    "data": json.dumps(data)
                }
            except StopAsyncIteration:
                break
            
            # Yield notification data
            try:
                data = await asyncio.anext(notification_gen)
                yield {
                    "event": "notification",
                    "data": json.dumps(data)
                }
            except StopAsyncIteration:
                break
            
            await asyncio.sleep(1)
    
    return EventSourceResponse(event_generator())
