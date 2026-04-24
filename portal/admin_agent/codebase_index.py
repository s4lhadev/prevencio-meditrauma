"""
Semantic index of the repo: SQLite + embeddings. Incremental by file content hash.
"""
from __future__ import annotations

import hashlib
import json
import logging
import os
import sqlite3
import struct
import time
from pathlib import Path
from typing import Any, Dict, List, Optional, Set, Tuple

from embeddings import embed_texts

logger = logging.getLogger(__name__)

MAX_FILE_BYTES = 450_000
CHUNK_CHARS = 4_000
CHUNK_OVERLAP = 400

EXCLUDE_DIR_NAMES = {
    "vendor",
    "node_modules",
    ".git",
    "var",
    "cache",
    "logs",
    "uploads",
    "web",
    "public",
    "coverage",
    ".idea",
    "__pycache__",
    ".venv",
    "eggs",
    "admin_agent",
}

INCLUDE_SUFFIXES = {
    ".php",
    ".twig",
    ".yml",
    ".yaml",
    ".xml",
    ".js",
    ".ts",
    ".tsx",
    ".md",
    ".json",
}


def _default_codebase_root() -> str:
    # admin_agent/ → project root (medisalut repo) o portal/ (prevencion repo) según dónde esté el paquete
    return str(Path(__file__).resolve().parent.parent)


def _default_db_path() -> str:
    return str(Path(__file__).resolve().parent / ".codebase_index.sqlite")


def split_by_size(content: str, max_chars: int = CHUNK_CHARS) -> List[str]:
    if len(content) <= max_chars:
        return [content] if content.strip() else []
    chunks: List[str] = []
    start = 0
    while start < len(content):
        end = min(start + max_chars, len(content))
        piece = content[start:end]
        if end < len(content):
            nl = piece.rfind("\n", max(0, len(piece) - 500))
            if nl > max_chars // 4:
                piece = piece[: nl + 1]
                end = start + len(piece)
        chunks.append(piece)
        start = end - CHUNK_OVERLAP if end < len(content) else end
        if start >= len(content):
            break
    return [c for c in chunks if c.strip()]


def chunk_source(content: str, rel_path: str) -> List[str]:
    ext = Path(rel_path).suffix.lower()
    if ext in (".yml", ".yaml", ".json", ".xml", ".md"):
        return split_by_size(content)
    if ext == ".php" or ext == ".twig":
        return split_by_size(content)
    return split_by_size(content)


def file_sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def iter_files(root: Path) -> List[Path]:
    out: List[Path] = []
    root = root.resolve()
    for dirpath, dirnames, filenames in os.walk(root, topdown=True):
        base = Path(dirpath)
        try:
            rel_parts = base.relative_to(root).parts
        except ValueError:
            continue
        if any(p in EXCLUDE_DIR_NAMES for p in rel_parts):
            dirnames[:] = []
            continue
        dirnames[:] = [d for d in dirnames if d not in EXCLUDE_DIR_NAMES and not d.startswith(".")]

        for fn in filenames:
            p = base / fn
            if p.suffix.lower() not in INCLUDE_SUFFIXES:
                continue
            if p.name.endswith(".min.js") or p.suffix == ".map":
                continue
            try:
                if not p.is_file() or p.stat().st_size > MAX_FILE_BYTES:
                    continue
            except OSError:
                continue
            out.append(p)
    out.sort()
    return out


def rel_path(root: Path, path: Path) -> str:
    return str(path.resolve().relative_to(root.resolve())).replace("\\", "/")


def _pack_emb(vec: List[float]) -> bytes:
    return struct.pack(f"{len(vec)}f", *vec)


def _unpack_emb(blob: bytes) -> List[float]:
    n = len(blob) // 4
    return list(struct.unpack(f"{n}f", blob))


class CodebaseIndex:
    def __init__(self) -> None:
        self.root = Path(os.getenv("CODEBASE_ROOT") or _default_codebase_root()).resolve()
        self.db_path = os.getenv("INDEX_DB_PATH") or _default_db_path()
        self._ensure_schema()

    def _conn(self) -> sqlite3.Connection:
        c = sqlite3.connect(self.db_path, timeout=60.0)
        c.row_factory = sqlite3.Row
        return c

    def _ensure_schema(self) -> None:
        with self._conn() as c:
            c.executescript(
                """
                CREATE TABLE IF NOT EXISTS file_fingerprints (
                    path TEXT PRIMARY KEY,
                    sha256 TEXT NOT NULL,
                    mtime REAL,
                    size INTEGER
                );
                CREATE TABLE IF NOT EXISTS chunks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    path TEXT NOT NULL,
                    ordinal INTEGER NOT NULL,
                    text TEXT NOT NULL,
                    emb BLOB NOT NULL,
                    UNIQUE(path, ordinal)
                );
                CREATE INDEX IF NOT EXISTS idx_chunks_path ON chunks(path);
                CREATE TABLE IF NOT EXISTS index_meta (key TEXT PRIMARY KEY, value TEXT);
                """
            )

    def status(self) -> Dict[str, Any]:
        with self._conn() as c:
            n_chunks = c.execute("SELECT COUNT(*) FROM chunks").fetchone()[0]
            n_files = c.execute("SELECT COUNT(*) FROM file_fingerprints").fetchone()[0]
            row = c.execute("SELECT value FROM index_meta WHERE key='last_reindex'").fetchone()
            last = row[0] if row else None
        last_s = last
        if last and isinstance(last, str) and last.startswith("{"):
            try:
                meta = json.loads(last)
                last_s = f"{meta.get('took_sec', '?')}s, full={meta.get('full', '?')}"
            except (json.JSONDecodeError, TypeError):
                pass
        return {
            "codebase_root": str(self.root),
            "index_db": self.db_path,
            "file_count": n_files,
            "chunk_count": n_chunks,
            "last_reindex_utc": last_s,
        }

    async def reindex(self, api_key: str, full: bool = False) -> Dict[str, Any]:
        t0 = time.time()
        if full:
            with self._conn() as c:
                c.executescript("DELETE FROM chunks; DELETE FROM file_fingerprints;")

        on_disk: List[Path] = iter_files(self.root)
        on_disk_rels: Set[str] = {rel_path(self.root, p) for p in on_disk}

        with self._conn() as c:
            for row in c.execute("SELECT path FROM file_fingerprints").fetchall():
                p = row[0]
                if p not in on_disk_rels:
                    c.execute("DELETE FROM chunks WHERE path=?", (p,))
                    c.execute("DELETE FROM file_fingerprints WHERE path=?", (p,))
            c.commit()

        updated_files = 0
        new_chunks = 0
        skipped_unchanged = 0
        errors: List[str] = []

        for fpath in on_disk:
            rel = rel_path(self.root, fpath)
            try:
                data = fpath.read_bytes()
            except OSError as e:
                errors.append(f"{rel}: read error {e}")
                continue
            h = file_sha256(data)
            with self._conn() as c:
                row = c.execute(
                    "SELECT sha256 FROM file_fingerprints WHERE path=?", (rel,)
                ).fetchone()
            if row and row[0] == h:
                skipped_unchanged += 1
                continue

            text = data.decode("utf-8", errors="replace")
            parts = chunk_source(text, rel)
            if not parts:
                with self._conn() as c:
                    c.execute("DELETE FROM chunks WHERE path=?", (rel,))
                    c.execute(
                        "INSERT OR REPLACE INTO file_fingerprints(path, sha256, mtime, size) VALUES (?,?,?,?)",
                        (rel, h, fpath.stat().st_mtime, len(data)),
                    )
                continue

            try:
                embs = await embed_texts(parts, api_key)
            except Exception as e:
                errors.append(f"{rel}: {e!s}")
                logger.exception("embed failed for %s", rel)
                continue

            with self._conn() as c:
                c.execute("DELETE FROM chunks WHERE path=?", (rel,))
                for i, (tchunk, emb) in enumerate(zip(parts, embs)):
                    c.execute(
                        "INSERT INTO chunks(path, ordinal, text, emb) VALUES (?,?,?,?)",
                        (rel, i, tchunk, _pack_emb(emb)),
                    )
                c.execute(
                    "INSERT OR REPLACE INTO file_fingerprints(path, sha256, mtime, size) VALUES (?,?,?,?)",
                    (rel, h, fpath.stat().st_mtime, len(data)),
                )
            updated_files += 1
            new_chunks += len(parts)

        took = round(time.time() - t0, 2)
        with self._conn() as c:
            c.execute(
                "INSERT OR REPLACE INTO index_meta(key, value) VALUES (?,?)",
                ("last_reindex", json.dumps({"took_sec": took, "full": full, "ts": time.time()})),
            )
            c.commit()

        st = self.status()
        st.update(
            {
                "ok": True,
                "took_sec": took,
                "files_on_disk": len(on_disk),
                "files_reindexed": updated_files,
                "files_unchanged": skipped_unchanged,
                "chunk_rows_written": new_chunks,
                "errors": errors[:20],
            }
        )
        return st

    async def search(self, query: str, api_key: str, top_k: int = 8) -> List[Dict[str, Any]]:
        if not query.strip():
            return []
        qe = (await embed_texts([query], api_key))[0]
        # numpy optional
        try:
            import numpy as np

            q = np.array(qe, dtype=np.float32)
            nq = np.linalg.norm(q) + 1e-9
            q /= nq
        except ImportError:
            q = qe
            nq = sum(x * x for x in q) ** 0.5 or 1.0
            q = [x / nq for x in q]

        with self._conn() as c:
            rows = c.execute("SELECT path, ordinal, text, emb FROM chunks").fetchall()
        if not rows:
            return []

        scored: List[Tuple[float, str, int, str]] = []
        for path, ord_, text, emb_b in rows:
            vec = _unpack_emb(emb_b)
            if isinstance(q, list):
                dot = sum(a * b for a, b in zip(q, vec))
                n2 = sum(x * x for x in vec) ** 0.5 or 1.0
                sim = dot / n2
            else:
                v = np.array(vec, dtype=np.float32)
                nv = np.linalg.norm(v) + 1e-9
                v /= nv
                sim = float(np.dot(q, v))
            scored.append((sim, path, ord_, text))

        scored.sort(key=lambda x: -x[0])
        out: List[Dict[str, Any]] = []
        for sim, path, ord_, text in scored[:top_k]:
            out.append(
                {
                    "score": round(sim, 4),
                    "path": path,
                    "chunk": (text[:1200] + "…") if len(text) > 1200 else text,
                }
            )
        return out


index_singleton: Optional[CodebaseIndex] = None


def get_index() -> CodebaseIndex:
    global index_singleton
    if index_singleton is None:
        index_singleton = CodebaseIndex()
    return index_singleton
