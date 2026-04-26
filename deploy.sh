#!/bin/bash
# deploy.sh — Marketing Site Deploy
# Builds Astro static site and syncs to production server
# Usage: ./deploy.sh

set -e

# ── Config ─────────────────────────────────────────────────────────────────
# Reutiliza la misma clave SSH y servidor que cita2
SSH_KEY="${HOME}/.ssh/id_rsa"
SSH_USER="joseignacio"
SSH_HOST="agendadereservas.com"
REMOTE_DIR="/var/www/agendadereservas"   # Ajusta si es diferente
LOCAL_DIST="./dist"

# ── Colores ────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

echo -e "${YELLOW}========================================"
echo "   Deploy Marketing Site"
echo -e "========================================${NC}"

# ── 1. Build ───────────────────────────────────────────────────────────────
echo ""
echo "[1/3] Construyendo sitio estático..."
ASTRO_TELEMETRY_DISABLED=1 npm run build
echo -e "${GREEN}✓ Build completado${NC}"

# ── 2. SSH check ───────────────────────────────────────────────────────────
echo ""
echo "[2/3] Verificando conexión SSH..."
if ssh -o ConnectTimeout=5 -i "$SSH_KEY" "${SSH_USER}@${SSH_HOST}" "echo ok" &>/dev/null; then
  echo -e "${GREEN}✓ SSH OK${NC}"
else
  echo -e "${RED}✗ No se pudo conectar al servidor. Verifica SSH_HOST y SSH_KEY en deploy.sh${NC}"
  exit 1
fi

# ── 3. Sync ────────────────────────────────────────────────────────────────
echo ""
echo "[3/3] Sincronizando dist/ → ${SSH_HOST}:${REMOTE_DIR}..."
rsync -avz --delete \
  -e "ssh -i ${SSH_KEY}" \
  "${LOCAL_DIST}/" \
  "${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/"

echo -e "${GREEN}✓ Sincronización completada${NC}"

echo ""
echo -e "${GREEN}========================================"
echo "   ✅ Deploy completado!"
echo -e "========================================${NC}"
echo ""
echo -e "🌐 Web: ${YELLOW}https://agendadereservas.com${NC}"
echo ""
echo "Páginas actualizadas:"
echo "  /                → Homepage con tabs por vertical"
echo "  /restaurantes    → Comparativa TheFork vs Restoo vs Agenda (24€)"
echo "  /turismo         → Comparativa Bókun vs FareHarbor vs Agenda (29€)"
echo "  /precios         → Precios modulares"
echo ""
