#!/usr/bin/env bash
# ControClinic — post-deploy HTTPS health check (run on VPS host as deploy user)
# Restarts Traefik + webserver if https://controclinic.com/up is unreachable after deploy.
#
# Usage:
#   ./scripts/post-deploy-health.sh
#   CONTROCLINIC_HEALTH_URL=https://controclinic.com/up ./scripts/post-deploy-health.sh

set -euo pipefail

URL="${CONTROCLINIC_HEALTH_URL:-https://controclinic.com/up}"
INITIAL_WAIT="${CONTROCLINIC_INITIAL_WAIT:-45}"
MAX_ATTEMPTS="${CONTROCLINIC_MAX_ATTEMPTS:-12}"
RETRY_WAIT="${CONTROCLINIC_RETRY_WAIT:-10}"

log() { echo "[post-deploy] $(date -Iseconds) $*"; }

check_url() {
    curl -sf -o /dev/null --max-time 15 "$URL"
}

log "Waiting ${INITIAL_WAIT}s for containers to settle..."
sleep "$INITIAL_WAIT"

for attempt in $(seq 1 "$MAX_ATTEMPTS"); do
    if check_url; then
        log "OK: $URL responds (attempt $attempt)"
        exit 0
    fi
    log "Attempt $attempt/$MAX_ATTEMPTS failed — retry in ${RETRY_WAIT}s"
    sleep "$RETRY_WAIT"
done

log "Health check failed — restarting coolify-proxy and controclinic-webserver"

WS=$(sudo docker ps -q --filter 'name=controclinic-webserver' | head -1 || true)

if [[ -z "${WS}" ]]; then
    log "ERROR: controclinic-webserver container not found"
    exit 1
fi

sudo docker restart coolify-proxy "$WS"
log "Restarted proxy + webserver — waiting 20s"
sleep 20

for attempt in $(seq 1 8); do
    if check_url; then
        log "OK after restart: $URL (attempt $attempt)"
        exit 0
    fi
    log "Post-restart attempt $attempt/8 failed"
    sleep 10
done

log "FAIL: $URL still unreachable after proxy restart"
exit 1
