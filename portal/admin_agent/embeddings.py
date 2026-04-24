"""Embeddings via OpenRouter (OpenAI-compatible)."""
from __future__ import annotations

import logging
import os
from typing import List

import httpx

logger = logging.getLogger(__name__)

OPENROUTER_EMBED_URL = "https://openrouter.ai/api/v1/embeddings"
DEFAULT_MODEL = "openai/text-embedding-3-small"
BATCH = 64


async def embed_texts(texts: List[str], api_key: str) -> List[List[float]]:
    if not texts:
        return []
    if not api_key.strip():
        raise ValueError("OPENROUTER_API_KEY is required for embeddings")
    model = (os.getenv("OPENROUTER_EMBEDDING_MODEL") or DEFAULT_MODEL).strip()
    product = (os.getenv("APP_PRODUCT") or "medisalut").strip().lower()
    default_ref = (
        "https://github.com/s4lhadev/prevencio-meditrauma" if product == "prevencion" else "https://github.com/s4lhadev/medisalut"
    )
    referer = (os.getenv("OPENROUTER_HTTP_REFERER") or default_ref)[:200]
    all_emb: List[List[float]] = []
    async with httpx.AsyncClient(timeout=httpx.Timeout(120.0, connect=20.0)) as client:
        for i in range(0, len(texts), BATCH):
            batch = texts[i : i + BATCH]
            r = await client.post(
                OPENROUTER_EMBED_URL,
                headers={
                    "Authorization": f"Bearer {api_key}",
                    "Content-Type": "application/json",
                    "HTTP-Referer": referer,
                },
                json={"model": model, "input": batch},
            )
            if r.status_code >= 400:
                raise RuntimeError(f"Embeddings {r.status_code}: {r.text[:1500]}")
            data = r.json()
            items = data.get("data") or []
            items = sorted(items, key=lambda x: x.get("index", 0))
            for it in items:
                emb = it.get("embedding")
                if not isinstance(emb, list):
                    raise RuntimeError("Invalid embedding in response")
                all_emb.append([float(x) for x in emb])
    if len(all_emb) != len(texts):
        raise RuntimeError("Embedding count mismatch")
    return all_emb
