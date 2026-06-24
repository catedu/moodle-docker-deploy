#!/bin/bash
set -euo pipefail

# Se recorre el listado de cursos y para cada curso se obtiene su ID y su shortname 
# Los cursos se exportan como shortname.mbz 
# Ejemplo de ejecución:
#   ./backup_to_mbz_moodle_courses.sh --contenedor "predesarrollofpvirtualaragones-moodle-1" --filtro "CPIFP CORONA DE ARAGÓN"

mostrar_ayuda() {
    cat << EOF
Uso: $0 [OPCIONES]

Opciones:
  -d, --destino RUTA      Carpeta destino de los backups MBZ
                          (por defecto: ./zz_mbzs_YYYYMMDD)
  -c, --contenedor NOMBRE Nombre del contenedor Docker
                          (por defecto: predesarrollofpvirtualaragones-moodle-1)
  -f, --filtro TEXTO      Filtra los cursos con grep -i (ignora mayúsculas/minúsculas)
  -h, --help              Muestra esta ayuda y sale

Ejemplo de uso:
  /var/moodle-docker-deploy/procedimiento-final-curso/backup_to_mbz_moodle_courses.sh --contenedor "wwwfpvirtualaragones_2-moodle-1" --destino /var/moodle-docker-deploy/zz_mbzs_20260625
  ./backup_to_mbz_moodle_courses.sh --contenedor "predesarrollofpvirtualaragones-moodle-1" --filtro "CPIFP CORONA DE ARAGÓN"
  nohup ./backup_to_mbz_moodle_courses.sh --contenedor "predesarrollofpvirtualaragones-moodle-1" --filtro "CPIFP CORONA DE ARAGÓN" &
EOF
}

LOG_FILE=""

log() {
    local nivel="$1"
    shift
    local mensaje="$*"
    local timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local linea="[${timestamp}] [${nivel}] ${mensaje}"
    if [ "$nivel" = "ERROR" ]; then
        echo "$linea" >&2
    else
        echo "$linea"
    fi
    if [ -n "$LOG_FILE" ]; then
        echo "$linea" >> "$LOG_FILE"
    fi
}

log_info()  { log "INFO"  "$@"; }
log_warn()  { log "WARN"  "$@"; }
log_error() { log "ERROR" "$@"; }

DATE=$( date -u +"%Y%m%d" )
DEFAULT_DESTINO="zz_mbzs_${DATE}"
DESTINO="$DEFAULT_DESTINO"
CONTENEDOR="predesarrollofpvirtualaragones-moodle-1"
FILTRO=""

while [[ $# -gt 0 ]]; do
  case $1 in
    -d|--destino)
      DESTINO="$2"
      shift 2
      ;;
    -c|--contenedor)
      CONTENEDOR="$2"
      shift 2
      ;;
    -f|--filtro)
      FILTRO="$2"
      shift 2
      ;;
    -h|--help)
      mostrar_ayuda
      exit 0
      ;;
    *)
      log_error "Opción desconocida: $1"
      mostrar_ayuda
      exit 1
      ;;
  esac
done

LOG_FILE="$(pwd)/backup_to_mbz_moodle_courses_$(date +%Y%m%d_%H%M%S).log"
LOG_FILE_SECONDARY="$(pwd)/backup_to_mbz_moodle_courses_secondary_$(date +%Y%m%d_%H%M%S).log"
mkdir -p "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"
touch "$LOG_FILE_SECONDARY"

# El destino siempre se ubica bajo /var/www/moodledata/repository
DESTINO="/var/www/moodledata/repository/${DESTINO#/}"

log_info "Iniciando backup"
log_info "Destino: ${DESTINO}"
log_info "Contenedor: ${CONTENEDOR}"
if [ -n "$FILTRO" ]; then
  log_info "Filtro: ${FILTRO}"
fi

if ! docker inspect "$CONTENEDOR" > /dev/null 2>&1; then
  log_error "El contenedor '${CONTENEDOR}' no existe o no está disponible"
  exit 1
fi

FECHA_HORA_INI=$( date -u +"%Y/%m/%d %H:%M:%S" )
log_info "Empezó ${FECHA_HORA_INI}"

# Creo la ruta donde dejaré los ficheros (dentro del contenedor)
docker exec "$CONTENEDOR" mkdir -p "${DESTINO}"

# Se obtiene el listado de cursos quitando la 1era línea
RAW_CURSOS=$(docker exec "$CONTENEDOR" moosh -n -p /var/www/html course-list 2>>"$LOG_FILE_SECONDARY" | tail -n +2)
if [ -n "$FILTRO" ]; then
  CURSOS=$(grep -i "$FILTRO" <<< "$RAW_CURSOS" || true)
else
  CURSOS="$RAW_CURSOS"
fi

if [ -z "$CURSOS" ]; then
  log_warn "No se encontraron cursos para procesar"
fi

# Para cada curso previamente obtenido
while IFS= read -r linea; do
  log_info "Procesando la línea: $linea"
  # Obtengo el ID procesando la línea
  ID=$(echo "$linea" | cut -d , -f 1)
  # Obtengo el shortname procesando la línea
  SHORTNAME=$(echo "$linea" | cut -d , -f 3)

  # Al ID le quito aquello que sobra
  ID=$(echo "$ID" | sed 's/^.//;s/.$//')

  # Al shortname le quito aquello que sobra
  SHORTNAME=$(echo "$SHORTNAME" | sed 's/^.//;s/.$//')

  # Creo el nombre del fichero
  NOMBRE="${SHORTNAME}.mbz"
  
  # Realizo la exportanción del curso de id $ID en la ruta indicada con el nombre $NOMBRE creado
  # siempre que no exista ya un fichero con ese nombre
  NOW=$( date -u +"%Y/%m/%d %H:%M:%S" )
  log_info "${NOW} Exportando el curso ${SHORTNAME} (id $ID)... (se paciente, hay cursos de gran tamaño)"
  if docker exec "$CONTENEDOR" test ! -f "${DESTINO}/$NOMBRE"; then
if docker exec "$CONTENEDOR" moosh -n -p /var/www/html course-backup --template -f "${DESTINO}/$NOMBRE" "$ID" >>"$LOG_FILE_SECONDARY" 2>&1; then
      log_info "Backup del curso ${SHORTNAME} completado"
    else
      log_error "Falló el backup del curso ${SHORTNAME} (id $ID)"
    fi
  fi
done <<< "$CURSOS"

FECHA_HORA_FIN=$( date -u +"%Y/%m/%d %H:%M:%S" )

log_info "Terminó ${FECHA_HORA_FIN}"
log_info "Proceso terminado"
