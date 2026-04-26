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

# ── Busca automáticamente la clave SSH y el directorio remoto ──────────────
SSH_HOST="agendadereservas.com"
SSH_USER="joseignacio"
LOCAL_DIST="./dist"

# Detecta clave SSH (misma que usa deploy-quick.sh de cita2)
SSH_KEY=""
for KEY in "$HOME/.ssh/id_rsa" "$HOME/.ssh/id_ed25519" "$HOME/.ssh/agendadereservas"; do
  if [ -f "$KEY" ]; then SSH_KEY="$KEY"; break; fi
done

if [ -z "$SSH_KEY" ]; then
  echo -e "${RED}✗ No se encontró clave SSH en ~/.ssh/${NC}"
  exit 1
fi

# Verifica que dist/ existe y tiene contenido
if [ ! -f "${LOCAL_DIST}/index.html" ]; then
  echo -e "${YELLOW}dist/ no existe o está vacío. Construyendo...${NC}"
  ASTRO_TELEMETRY_DISABLED=1 npm run build
fi

echo ""
echo "[1/3] Conectando al servidor..."
if ! ssh -o ConnectTimeout=8 -i "$SSH_KEY" "${SSH_USER}@${SSH_HOST}" "echo ok" &>/dev/null; then
  echo -e "${RED}✗ No se pudo conectar. Comprueba la clave SSH y el servidor.${NC}"
  exit 1
fi
echo -e "${GREEN}✓ SSH OK${NC}"

# Descubre el directorio raíz del marketing site en el servidor
echo ""
echo "[2/3] Localizando directorio en el servidor..."
REMOTE_DIR=$(ssh -i "$SSH_KEY" "${SSH_USER}@${SSH_HOST}" "
  # Busca en nginx dónde está mapeado agendadereservas.com
  ROOT=\$(grep -r 'root\|alias' /etc/nginx/sites-enabled/ 2>/dev/null | grep -v '#' | grep -v 'app\.' | awk '{print \$2}' | tr -d ';' | head -1)
  if [ -n \"\$ROOT\" ]; then echo \"\$ROOT\"; exit 0; fi
  # Fallback: busca el index.html del marketing
  find /var/www /srv /home -name 'index.html' -not -path '*/node_modules/*' 2>/dev/null | head -1 | xargs dirname
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
  -e "ssh -i ${SSH_KEY}" \
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
