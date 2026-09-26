#!/bin/bash
# deploy.sh — Marketing Site (agendadereservas.com)
#
# Este script NO despliega nada ni se conecta a ningún servidor.
# agendadereservas.com está en Netlify y se publica automáticamente
# al hacer push a la rama `main`.
#
# Uso:
#   ./deploy.sh           Muestra cómo se despliega y hace un build local de comprobación
#   ./deploy.sh --no-build Solo muestra el mensaje, sin build
#
# Cualquier otro argumento termina con error: el antiguo deploy por
# rsync/SSH al VPS se eliminó porque podía borrar sitios de otros clientes.

set -euo pipefail

YELLOW='\033[1;33m'; GREEN='\033[0;32m'; RED='\033[0;31m'; NC='\033[0m'

RUN_BUILD=1
if [ "$#" -gt 0 ]; then
  if [ "$#" -eq 1 ] && [ "$1" = "--no-build" ]; then
    RUN_BUILD=0
  else
    echo -e "${RED}✗ deploy.sh ya no acepta argumentos (recibido: $*).${NC}" >&2
    echo "  El deploy por SSH/rsync al VPS se eliminó. El sitio se publica en Netlify al hacer push a main." >&2
    echo "  Uso: ./deploy.sh [--no-build]" >&2
    exit 2
  fi
fi

echo -e "${YELLOW}========================================"
echo "   Deploy Marketing Site"
echo -e "========================================${NC}"
echo ""
echo "agendadereservas.com se sirve desde Netlify y se publica"
echo "automáticamente al hacer push a la rama main:"
echo ""
echo "    git push origin main"
echo ""
echo "Este script no se conecta a ningún servidor."

if [ "$RUN_BUILD" -eq 1 ]; then
  echo ""
  echo "Build local de comprobación..."
  ASTRO_TELEMETRY_DISABLED=1 npm run build
  echo -e "${GREEN}✓ Build local correcto${NC}"
fi
