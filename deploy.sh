#!/bin/bash
# deploy.sh — Marketing Site Deploy
# Sube el dist/ ya construido al servidor de producción
# Uso: ./deploy.sh
# Requiere: dist/ generado con npm run build

set -e

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'

echo -e "${YELLOW}========================================"
echo "   Deploy Marketing Site → Producción"
echo -e "========================================${NC}"

SSH_HOST="75.119.150.113"
SSH_PORT="1968"
SSH_USER="root"
SSH_KEY="$HOME/.ssh/id_ed25519"
LOCAL_DIST="./dist"

# Verifica que dist/ existe y tiene contenido
if [ ! -f "${LOCAL_DIST}/index.html" ]; then
  echo -e "${YELLOW}dist/ no existe o está vacío. Construyendo...${NC}"
  ASTRO_TELEMETRY_DISABLED=1 npm run build
fi

echo ""
echo "[1/3] Conectando al servidor..."
if ! ssh -o ConnectTimeout=8 -o StrictHostKeyChecking=no -i "$SSH_KEY" -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "echo ok" &>/dev/null; then
  echo -e "${RED}✗ No se pudo conectar a ${SSH_HOST}:${SSH_PORT}${NC}"
  exit 1
fi
echo -e "${GREEN}✓ SSH OK${NC}"

# Descubre el directorio raíz del marketing site en el servidor
echo ""
echo "[2/3] Localizando directorio en el servidor..."
REMOTE_DIR=$(ssh -o StrictHostKeyChecking=no -i "$SSH_KEY" -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "
  ROOT=\$(grep -r 'root\|alias' /etc/nginx/sites-enabled/ 2>/dev/null | grep -v '#' | grep 'agendadereservas' | awk '{print \$2}' | tr -d ';' | head -1)
  if [ -n \"\$ROOT\" ]; then echo \"\$ROOT\"; exit 0; fi
  find /var/www /srv /home -name 'index.html' -not -path '*/node_modules/*' 2>/dev/null | head -1 | xargs dirname 2>/dev/null
")

if [ -z "$REMOTE_DIR" ]; then
  REMOTE_DIR="/var/www/agendadereservas"
  echo -e "${YELLOW}⚠ No detectado automáticamente. Usando: ${REMOTE_DIR}${NC}"
else
  echo -e "${GREEN}✓ Directorio: ${REMOTE_DIR}${NC}"
fi

# Sube el dist/
echo ""
echo "[3/3] Subiendo dist/ → ${SSH_HOST}:${REMOTE_DIR}..."
rsync -avz --delete \
  --exclude='.DS_Store' \
  -e "ssh -i ${SSH_KEY} -p ${SSH_PORT} -o StrictHostKeyChecking=no" \
  "${LOCAL_DIST}/" \
  "${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}/"

echo -e "${GREEN}✓ Subida completada${NC}"

echo ""
echo -e "${GREEN}========================================"
echo "   ✅ Deploy completado!"
echo -e "========================================${NC}"
echo ""
echo -e "🌐 ${YELLOW}https://agendadereservas.com${NC}"
echo -e "🍽️  ${YELLOW}https://agendadereservas.com/restaurantes${NC}"
echo -e "🏔️  ${YELLOW}https://agendadereservas.com/turismo${NC}"
