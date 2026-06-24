#!/usr/bin/env bash
set -euo pipefail

CONTENEDOR_POR_DEFECTO="wwwfpvirtualaragones_2-moodle-1"
MOODLE_MAINTENANCE="/var/www/html/admin/cli/maintenance.php"
USUARIO_CONTENEDOR="www-data"
RUTA_SCRIPT="/var/moodle-docker-deploy/procedimiento-final-curso/activar_mantenimiento_moodle.sh"

mostrar_ayuda() {
    cat <<EOF
Uso:
  $0 [contenedor]

Descripción:
  Activa el modo mantenimiento de Moodle 4.5.7 dentro de un contenedor Docker.

Parámetros:
  contenedor    Nombre del contenedor Moodle.
                Si no se indica, se usa:
                $CONTENEDOR_POR_DEFECTO

Opciones:
  -h, --help    Muestra esta ayuda.

Ejemplos:
  Ejecución normal síncrona desde la carpeta del script:
    ./activar_mantenimiento_moodle.sh

  Ejecución indicando explícitamente el contenedor:
    ./activar_mantenimiento_moodle.sh testfpvirtualaragones-moodle-1

  Ejecución desde ruta absoluta:
    /var/moodle-docker-deploy/procedimiento-final-curso/activar_mantenimiento_moodle.sh

  Ejecución desde ruta absoluta indicando explícitamente el contenedor:
    /var/moodle-docker-deploy/procedimiento-final-curso/activar_mantenimiento_moodle.sh testfpvirtualaragones-moodle-1

Datos configurados:
  Ruta del script: $RUTA_SCRIPT
  Moodle CLI:      $MOODLE_MAINTENANCE
  Usuario:         $USUARIO_CONTENEDOR
  Versión Moodle:  4.5.7
  Imagen/base:     fpm unoconv

Ejemplo en crontab para ejecutarlo el 25 de junio a las 00:01:
  1 0 25 6 * $RUTA_SCRIPT

EOF
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    mostrar_ayuda
    exit 0
fi

CONTENEDOR="${1:-$CONTENOR_POR_DEFECTO}"

if ! command -v docker >/dev/null 2>&1; then
    echo "ERROR: Docker no está instalado o no está disponible en el PATH." >&2
    exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -Fxq "$CONTENEDOR"; then
    echo "ERROR: El contenedor '$CONTENEDOR' no está en ejecución." >&2
    echo
    echo "Contenedores actualmente en ejecución:"
    docker ps --format '  - {{.Names}}'
    exit 1
fi

echo "Activando modo mantenimiento en Moodle..."
echo "Contenedor: $CONTENEDOR"
echo "Script CLI Moodle: $MOODLE_MAINTENANCE"

docker exec -u "$USUARIO_CONTENEDOR" "$CONTENEDOR" php "$MOODLE_MAINTENANCE" --enable

echo "Modo mantenimiento activado correctamente."