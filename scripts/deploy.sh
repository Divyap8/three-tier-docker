#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
#  deploy.sh — One-command setup for the three-tier stack
# ─────────────────────────────────────────────────────────────
set -euo pipefail

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

log()  { echo -e "${GREEN}[✔]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
err()  { echo -e "${RED}[✘]${NC} $*" >&2; exit 1; }

# ── Prerequisites ─────────────────────────────────────────────
command -v docker      >/dev/null 2>&1 || err "Docker not found. Install from https://docs.docker.com/get-docker/"
command -v docker compose >/dev/null 2>&1 || err "Docker Compose v2 not found."

log "Docker $(docker --version | awk '{print $3}' | tr -d ',')"
log "Compose $(docker compose version --short)"

# ── Env file ──────────────────────────────────────────────────
if [[ ! -f .env ]]; then
    warn ".env not found — copying from .env.example"
    cp .env.example .env
    warn "⚠  Edit .env and set strong passwords before production use."
fi

# ── Build & start ─────────────────────────────────────────────
log "Building images..."
docker compose build --no-cache

log "Starting containers..."
docker compose up -d

# ── Wait for healthy status ───────────────────────────────────
log "Waiting for all services to become healthy..."
TIMEOUT=120; ELAPSED=0
while true; do
    UNHEALTHY=$(docker compose ps --format json 2>/dev/null | \
        python3 -c "import sys,json; data=sys.stdin.read().strip()
lines=[l for l in data.splitlines() if l.startswith('{')]
svcs=[json.loads(l) for l in lines]
print(sum(1 for s in svcs if s.get('Health','') not in ('healthy','')))" 2>/dev/null || echo 1)
    [[ "$UNHEALTHY" == "0" ]] && break
    [[ "$ELAPSED" -ge "$TIMEOUT" ]] && { warn "Timeout waiting for healthy status"; break; }
    sleep 3; ELAPSED=$((ELAPSED+3)); printf "."
done
echo ""

# ── Summary ───────────────────────────────────────────────────
echo ""
echo "────────────────────────────────────────────────────────"
echo "  🐳 Three-Tier Stack is UP"
echo ""
echo "  Web app   →  http://localhost"
echo "  Health    →  http://localhost/health"
echo "  API users →  http://localhost/users"
echo ""
echo "  Logs:  make logs"
echo "  Shell: make shell-php"
echo "  Stop:  make down"
echo "────────────────────────────────────────────────────────"
