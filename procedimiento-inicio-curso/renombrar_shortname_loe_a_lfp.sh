#!/usr/bin/env bash
# Renombra los shortnames de cursos Moodle sustituyendo códigos LOE por LFP.
# Lee un CSV con pares LFP,LOE y, por cada fila, busca en la base de datos de
# Moodle todos los cursos cuyo shortname contenga el código LOE y los renombra
# reemplazando esa subcadena por el código LFP correspondiente.
set -euo pipefail

# Directorio donde reside el propio script, usado para resolver rutas relativas
# (CSV y fichero de log) independientemente de desde dónde se invoque.
SCRIPT_DIR="$(dirname "$0")"
SCRIPT_NAME="$(basename "$0" .sh)"

# El fichero de log lleva la fecha del día en el nombre para facilitar la
# trazabilidad cuando el script se ejecuta periódicamente.
LOG_FILE="${SCRIPT_DIR}/${SCRIPT_NAME}_$(date '+%Y-%m-%d').log"

# Valores por defecto de los parámetros configurables.
CONTAINER="predesarrollofpvirtualaragones-moodle-1"
CSV="${SCRIPT_DIR}/Relación IDs sigad cursos LFP y LOE - Hoja 1.csv"

# Escribe una línea de log con timestamp y nivel (INFO, WARN, ERROR…)
# tanto en stdout como en el fichero de log (modo append).
log() {
    local level="$1"
    shift
    local msg="$*"
    local ts
    ts=$(date '+%Y-%m-%d %H:%M:%S')
    printf '%s [%-5s] %s\n' "$ts" "$level" "$msg" | tee -a "$LOG_FILE"
}

# Muestra la ayuda en stdout (sin pasar por el log) y termina.
usage() {
    cat <<EOF
Uso: $(basename "$0") [OPCIONES]

Recorre un fichero CSV y renombra los shortnames de los cursos Moodle
sustituyendo el código LOE por el código LFP correspondiente.

Los mensajes se escriben tanto por pantalla como en el fichero de log:
  ${LOG_FILE}

OPCIONES:
  --container NOMBRE   Nombre del contenedor Docker donde corre Moodle.
                       Por defecto: predesarrollofpvirtualaragones-moodle-1

  --csv RUTA           Ruta al fichero CSV de entrada (con cabecera LFP,LOE).
                       Por defecto: <directorio_del_script>/Relación IDs sigad cursos LFP y LOE - Hoja 1.csv

  --help               Muestra esta ayuda y termina.

EJEMPLOS:

  # Ejecución síncrona con los valores por defecto:
  ./$(basename "$0")

  # Ejecución síncrona especificando parámetros:
  ./$(basename "$0") --csv ./otro_fichero.csv --container mi-contenedor-moodle

  # Ejecución en segundo plano con nohup (stdout/stderr van al log del script):
  nohup ./$(basename "$0") --csv ./otro_fichero.csv > /dev/null 2>&1 &
  echo "PID: \$!"

  # Ejemplo de ejecución
  ./$(basename "$0") --container nuevofpvirtualaragones-moodle-1
EOF
}

# Parseo de argumentos con nombre. Cualquier parámetro no reconocido aborta
# con error para evitar ejecuciones silenciosas con opciones incorrectas.
while [[ $# -gt 0 ]]; do
    case "$1" in
        --container)
            CONTAINER="$2"
            shift 2
            ;;
        --csv)
            CSV="$2"
            shift 2
            ;;
        --help)
            usage
            exit 0
            ;;
        *)
            log ERROR "Parámetro desconocido: $1"
            exit 1
            ;;
    esac
done

# Marca de tiempo de inicio en segundos (epoch) para calcular la duración total.
START_TS=$(date +%s)
log INFO "Inicio"

# Comprobación temprana del CSV: si no existe no tiene sentido continuar.
if [[ ! -f "$CSV" ]]; then
    log ERROR "Fichero CSV no encontrado: $CSV"
    exit 1
fi

log INFO "Contenedor : $CONTAINER"
log INFO "CSV        : $CSV"
log INFO "Log        : $LOG_FILE"

# Lectura del CSV fila a fila. IFS=, separa las columnas por coma.
# La primera fila (cabecera) se salta con el flag first_row.
# Si el CSV tiene más de dos columnas, el resto se captura en "_resto"
# y se ignora; solo se usan las dos primeras (LFP y LOE).
first_row=true
while IFS=, read -r LFP LOE _resto; do
    if $first_row; then
        first_row=false
        continue
    fi

    # Eliminar espacios y retornos de carro (CSV con finales de línea CRLF
    # de Windows), que de lo contrario quedan pegados al final de LOE.
    LFP="${LFP//[$'\r ']/}"
    LOE="${LOE//[$'\r ']/}"

    # Ignorar filas vacías o incompletas.
    if [[ -z "$LFP" || -z "$LOE" ]]; then
        continue
    fi

    log INFO "Procesando fila: LOE='${LOE}' → LFP='${LFP}'"

    # Consulta SQL dentro del contenedor: busca todos los cursos cuyo shortname
    # contiene el código LOE. Se usa LIKE con wildcards para capturar shortnames
    # donde el código forma parte de una cadena más larga.
    sql_output=$(docker exec --user www-data "$CONTAINER" moosh sql-run \
        "SELECT id, shortname FROM mdl_course WHERE shortname LIKE '%${LOE}%'")

    # moosh sql-run devuelve objetos PHP serializados en formato multilínea:
    #   Record N stdClass Object ( [id] => X [shortname] => Y )
    # Se extraen todos los pares id/shortname del bloque completo de salida.
    mapfile -t ids        < <(echo "$sql_output" | grep -oP '\[id\] => \K\S+'        || true)
    mapfile -t shortnames < <(echo "$sql_output" | grep -oP '\[shortname\] => \K\S+' || true)

    if [[ ${#ids[@]} -eq 0 ]]; then
        log WARN  "No se encontró ningún curso con shortname que contenga '${LOE}'"
        continue
    fi

    # Por cada curso encontrado se construye el nuevo shortname y se aplica.
    for i in "${!ids[@]}"; do
        id="${ids[$i]}"
        old_shortname="${shortnames[$i]}"

        # Saltarse entradas malformadas o vacías.
        if [[ -z "$id" || -z "$old_shortname" ]]; then
            continue
        fi

        # Reemplaza todas las ocurrencias de LOE por LFP dentro del shortname,
        # preservando el resto de la cadena intacta.
        new_shortname="${old_shortname//$LOE/$LFP}"

        log INFO  "Curso id=${id}: '${old_shortname}' → '${new_shortname}'"

        # Aplica el nuevo shortname directamente en la base de datos de Moodle
        # a través de moosh course-config-set.
        docker exec --user www-data "$CONTAINER" moosh course-config-set "$id" shortname "$new_shortname"
        log INFO  "Curso id=${id} renombrado correctamente."
    done

done < "$CSV"

# Calcula e imprime la duración total del script en formato HH:MM:SS.
END_TS=$(date +%s)
ELAPSED=$(( END_TS - START_TS ))
ELAPSED_FMT=$(printf '%02d:%02d:%02d' $(( ELAPSED / 3600 )) $(( (ELAPSED % 3600) / 60 )) $(( ELAPSED % 60 )))

log INFO "Fin. Duración: ${ELAPSED_FMT} (${ELAPSED}s)"
