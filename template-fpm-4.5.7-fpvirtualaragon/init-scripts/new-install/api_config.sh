#!/bin/bash
# =============================================================================
# Script de configuración del API Web Service de Moodle
# =============================================================================
# Usuario: moodle-api (usuario normal, NO admin)
# Rol: integracion_api (rol personalizado con capacidades limitadas)
# Servicio: Test API
# =============================================================================

set -e

echo "=========================================="
echo "Configuración API Moodle"
echo "=========================================="
echo ""

# -----------------------------------------------------------------------------
# VARIABLES CONFIGURABLES
# -----------------------------------------------------------------------------
API_USER="moodle-api"
API_ROLE="integracion_api"
SERVICE_NAME="Test API"
SERVICE_SHORTNAME="test_api"

# -----------------------------------------------------------------------------
# 1. VERIFICAR ENTORNO
# -----------------------------------------------------------------------------
echo "[1/9] Verificando instalación de moosh..."
if ! command -v moosh &> /dev/null; then
    echo "ERROR: moosh no está instalado."
    exit 1
fi
echo "  ✓ moosh detectado"

if [ ! -f "config.php" ]; then
    echo "ERROR: No se encontró config.php. Ejecuta este script desde /var/www/html"
    exit 1
fi

# -----------------------------------------------------------------------------
# 2. HABILITAR WEB SERVICES A NIVEL DE SITIO
# -----------------------------------------------------------------------------
echo ""
echo "[2/9] Habilitando servicios web..."
moosh -n config-set enablewebservices 1
echo "  ✓ enablewebservices = 1"

# -----------------------------------------------------------------------------
# 3. HABILITAR PROTOCOLO REST
# -----------------------------------------------------------------------------
echo ""
echo "[3/9] Habilitando protocolo REST..."
# En Moodle 4.5+ REST es un protocolo core; solo hay que asegurar que esté activo
moosh -n config-set webserviceprotocols 'rest' || true
echo "  ✓ Protocolo REST habilitado"

# -----------------------------------------------------------------------------
# 4. CREAR USUARIO API SI NO EXISTE
# -----------------------------------------------------------------------------
echo ""
echo "[4/9] Creando usuario API si no existe..."

USER_ID=$(moosh -n sql-run "SELECT id FROM {user} WHERE username = '$API_USER'" | grep -oP '\d+' | tail -1)

if [ -z "$USER_ID" ]; then
    moosh -n user-create \
        --password "${API_USER_PASSWORD:-${MOODLE_ADMIN_PASSWORD}}" \
        --email "${API_USER_EMAIL:-api@localhost}" \
        --digest 2 \
        --city "Aragón" \
        --country ES \
        --firstname "API" \
        --lastname "Moodle" \
        "$API_USER"
    echo "  ✓ Usuario '$API_USER' creado"
else
    echo "  ✓ Usuario '$API_USER' ya existe"
fi

# -----------------------------------------------------------------------------
# 5. CREAR ROL DE INTEGRACIÓN API
# -----------------------------------------------------------------------------
echo ""
echo "[5/9] Creando rol de integración API..."

ROLE_EXISTS=$(moosh -n role-list | grep "$API_ROLE" | wc -l)

if [ "$ROLE_EXISTS" -eq "0" ]; then
    moosh -n role-create \
        -d "Rol para acceso programático vía web services" \
        -a user \
        -n "Integración API" \
        "$API_ROLE"
    echo "  ✓ Rol '$API_ROLE' creado"
else
    echo "  ✓ Rol '$API_ROLE' ya existe"
fi

# Habilitar asignación a nivel de sistema (requerido para user-assign-system-role)
echo "  → Habilitando contexto de sistema para el rol..."
moosh -n role-update-contextlevel --system-on "$API_ROLE"
echo "  ✓ Contexto de sistema activado"

# -----------------------------------------------------------------------------
# 6. ASIGNAR CAPACIDADES AL ROL
# -----------------------------------------------------------------------------
echo ""
echo "[6/9] Asignando capacidades al rol..."

capabilities=(
    # Usuarios
    "moodle/user:create"
    "moodle/user:viewalldetails"
    "moodle/user:update"
    "moodle/user:delete"
    "moodle/user:viewhiddendetails"
    # Cohortes
    "moodle/cohort:manage"
    "moodle/cohort:view"
    # Cursos
    "moodle/course:create"
    "moodle/course:update"
    "moodle/course:viewhiddencourses"
    "moodle/course:viewparticipants"
    "moodle/course:manageactivities"
    # Backup/Restore
    "moodle/backup:backupcourse"
    "moodle/backup:restorecourse"
    "moodle/restore:restorecourse"
    "moodle/backup:backuptargetimport"
    "moodle/backup:configure"
    # Matriculación
    "enrol/manual:enrol"
    "enrol/manual:unenrol"
    "enrol/manual:manage"
    # Grupos
    "moodle/course:managegroups"
    "moodle/site:accessallgroups"
    # Web Services
    "webservice/rest:use"
    "moodle/webservice:createtoken"
    "moodle/webservice:managealltokens"
    # Archivos
    "moodle/user:manageownfiles"
)

for cap in "${capabilities[@]}"; do
    moosh -n role-update-capability "$API_ROLE" "$cap" allow 1 > /dev/null 2>&1 || true
done

echo "  ✓ ${#capabilities[@]} capacidades asignadas"

# -----------------------------------------------------------------------------
# 7. ASIGNAR ROL AL USUARIO moodle-api EN CONTEXTO DE SISTEMA
# -----------------------------------------------------------------------------
echo ""
echo "[7/9] Asignando rol '$API_ROLE' a usuario '$API_USER'..."

# El usuario ya fue verificado/creado en el paso 4
if [ -z "$USER_ID" ]; then
    echo "ERROR: Usuario '$API_USER' no encontrado."
    exit 1
fi

moosh -n user-assign-system-role "$API_USER" "$API_ROLE" > /dev/null 2>&1 || true
echo "  ✓ Rol asignado a $API_USER (ID: $USER_ID)"

# -----------------------------------------------------------------------------
# 7. CREAR SERVICIO EXTERNO, FUNCIONES Y GENERAR TOKEN (vía PHP nativo)
# -----------------------------------------------------------------------------
echo ""
echo "[8/9] Creando servicio externo, funciones y token..."

PHP_SCRIPT="/init-scripts/new-install/api_service_setup.php"

if [ ! -f "$PHP_SCRIPT" ]; then
    echo "ERROR: No se encontró $PHP_SCRIPT"
    exit 1
fi

php "$PHP_SCRIPT"

# -----------------------------------------------------------------------------
# 8. LIMPIEZA DE CACHÉ
# -----------------------------------------------------------------------------
echo ""
echo "[9/9] Limpiando cachés..."
moosh -n cache-clear
echo "  ✓ Caché limpiada"

echo ""
echo "=========================================="
echo "CONFIGURACIÓN API COMPLETADA"
echo "=========================================="
