#!/usr/bin/env bash
# Install ControClinic post-deploy health hook on the VPS (run once from repo root).
# Requires: SSH access as deploy@5.78.235.235

set -euo pipefail

HOST="${CONTROCLINIC_DEPLOY_HOST:-deploy@5.78.235.235}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REMOTE_BIN="/home/deploy/bin"
REMOTE_SCRIPT="${REMOTE_BIN}/controclinic-post-deploy-health.sh"
WATCH_SCRIPT="${REMOTE_BIN}/controclinic-watch-webserver.sh"

log() { echo "[install] $*"; }

log "Copying scripts to ${HOST}"
ssh "$HOST" "mkdir -p ${REMOTE_BIN}"
scp "${SCRIPT_DIR}/post-deploy-health.sh" "${HOST}:${REMOTE_SCRIPT}"
ssh "$HOST" "chmod +x ${REMOTE_SCRIPT}"

ssh "$HOST" "cat > ${WATCH_SCRIPT}" <<'WATCH'
#!/usr/bin/env bash
set -euo pipefail
docker events --filter type=container --filter event=start --format '{{.Actor.Attributes.name}}' | while read -r name; do
  case "$name" in
    *controclinic-webserver*)
      sleep 50
      /home/deploy/bin/controclinic-post-deploy-health.sh
      ;;
  esac
done
WATCH

ssh "$HOST" "chmod +x ${WATCH_SCRIPT}"

log "Installing systemd service controclinic-watch-webserver"

ssh "$HOST" "sudo tee /etc/systemd/system/controclinic-watch-webserver.service > /dev/null" <<UNIT
[Unit]
Description=ControClinic post-deploy HTTPS health check watcher
After=docker.service
Requires=docker.service

[Service]
User=deploy
Group=docker
Restart=always
RestartSec=10
ExecStart=${WATCH_SCRIPT}

[Install]
WantedBy=multi-user.target
UNIT

ssh "$HOST" "sudo systemctl daemon-reload && sudo systemctl enable --now controclinic-watch-webserver.service && sudo systemctl status controclinic-watch-webserver.service --no-pager | head -8"

log "Done. Manual test: ssh ${HOST} ${REMOTE_SCRIPT}"
