#!/usr/bin/env bash
#
# Nombre: mover-cursos-loe-no-usados.sh
# Propósito: Lee un fichero CSV con códigos LFP en la primera columna, busca
#            ficheros cuyo nombre contenga dichos códigos en un directorio base
#            y los mueve a la carpeta LFP.old.
#
# Uso:
#   ./mover-cursos-loe-no-usados.sh [OPCIONES]
#   ./mover-cursos-loe-no-usados.sh --help
#
# Opciones:
#   --csv, -c <ruta>      Ruta al fichero CSV (por defecto: CSV en el mismo
#                         directorio que este script).
#   --base-dir, -b <dir>  Directorio donde se buscan los ficheros a mover
#                         (por defecto: directorio actual).
#   --help, -h            Muestra esta ayuda y sale.
#
# Configuración mediante variables de entorno:
#   BASE_DIR    Directorio donde se buscan los ficheros (por defecto: directorio actual).
#   DEFAULT_CSV Ruta por defecto al fichero CSV.
#
# Ejemplo de CSV esperado:
#   LFP001,...
#   LFP002,...
#
# Autor: Script generado automáticamente.

set -euo pipefail

# Directorio donde reside este script (resuelto de forma robusta)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="$(basename "$0")"

# Configuración de logs (nombre: script_YYYYMMDD_HHMMSS.log)
EXEC_TIMESTAMP="$(date '+%Y%m%d_%H%M%S')"
LOG_FILE="${SCRIPT_DIR}/${SCRIPT_NAME%.sh}_${EXEC_TIMESTAMP}.log"

# Función de logging: escribe a fichero y a stdout/stderr según el nivel
log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp
    timestamp="$(date '+%Y-%m-%d %H:%M:%S')"
    local log_line="[${timestamp}] [${level}] ${message}"

    # Escribir siempre al fichero de log
    echo "$log_line" >> "$LOG_FILE"

    # Escribir a stdout/stderr según nivel
    case "$level" in
        ERROR|WARN)
            echo "$log_line" >&2
            ;;
        *)
            echo "$log_line"
            ;;
    esac
}

# Función de ayuda
show_help() {
    cat << 'EOF'
Uso: mover-cursos-loe-no-usados.sh [OPCIONES]
       mover-cursos-loe-no-usados.sh --help

Descripción:
  Lee un fichero CSV con códigos LFP en la primera columna, busca ficheros
  cuyo nombre contenga dichos códigos en un directorio base y los mueve a
  la carpeta LFP.old (siempre dentro de BASE_DIR).

Opciones:
  --csv, -c <ruta>      Ruta al fichero CSV. Si se omite, se usa el CSV por
                        defecto ubicado en el mismo directorio que este script.
  --base-dir, -b <dir>  Directorio donde se buscan los ficheros a mover.
                        Por defecto: directorio actual (o valor de BASE_DIR).
  --help, -h            Muestra esta ayuda y termina.

Configuración mediante variables de entorno:
  BASE_DIR     Directorio donde se buscan los ficheros (por defecto: directorio actual).
  DEFAULT_CSV  Ruta al fichero CSV por defecto.

Ejemplos:
  # Ejecución síncrona con valores por defecto
  ./mover-cursos-loe-no-usados.sh

  # Especificando CSV y directorio base
  ./mover-cursos-loe-no-usados.sh --csv /ruta/al/fichero.csv --base-dir /datos/cursos

  # Abreviado
  ./mover-cursos-loe-no-usados.sh -c fichero.csv -b /datos/cursos

  # Ejecución en segundo plano con nohup
  nohup ./mover-cursos-loe-no-usados.sh --base-dir /datos/cursos > /dev/null 2>&1 &

  # Ejecución con nohup y redirección a un fichero de salida personalizado
  nohup ./mover-cursos-loe-no-usados.sh -c fichero.csv -b /datos/cursos > salida.log 2>&1 &
EOF
    exit 0
}

# Variables para los argumentos con nombre
ARG_CSV=""
ARG_BASE_DIR=""

# Procesar argumentos con nombre
while [[ $# -gt 0 ]]; do
    case "$1" in
        --csv|-c)
            if [[ -z "${2:-}" ]]; then
                echo "Error: La opción $1 requiere un valor." >&2
                exit 1
            fi
            ARG_CSV="$2"
            shift 2
            ;;
        --base-dir|-b)
            if [[ -z "${2:-}" ]]; then
                echo "Error: La opción $1 requiere un valor." >&2
                exit 1
            fi
            ARG_BASE_DIR="$2"
            shift 2
            ;;
        --help|-h)
            show_help
            ;;
        *)
            echo "Error: Opción desconocida: $1" >&2
            echo "Usa --help para ver la ayuda." >&2
            exit 1
            ;;
    esac
done

# Configuración operativa (prioridad: argumento > variable de entorno > valor por defecto)
BASE_DIR="${ARG_BASE_DIR:-${BASE_DIR:-.}}"
DEST_DIR="${BASE_DIR}/LFP.old"
DEFAULT_CSV="${DEFAULT_CSV:-${SCRIPT_DIR}/Relación IDs sigad cursos LFP y LOE - Hoja 1.csv}"

# Medición del tiempo de ejecución
SECONDS=0

# Determinar fichero CSV a procesar
CSV_FILE="${ARG_CSV:-$DEFAULT_CSV}"

# Validar que el fichero CSV existe
if [[ ! -f "$CSV_FILE" ]]; then
    log ERROR "No se encuentra el fichero CSV: '$CSV_FILE'"
    log ERROR "Usa --help para ver la ayuda."
    exit 1
fi

log INFO "Inicio del script. CSV: '$CSV_FILE' | BASE_DIR: '$BASE_DIR' | DEST_DIR: '$DEST_DIR'"

# Crear directorio destino si no existe
if [[ ! -d "$DEST_DIR" ]]; then
    mkdir -p "$DEST_DIR"
    log INFO "Creado directorio destino: '$DEST_DIR'"
fi

# Convertir posibles saltos de línea DOS a UNIX y extraer la primera columna
declare -a lfp_codes=()
while IFS= read -r line; do
    # Ignorar líneas vacías
    [[ -z "$line" ]] && continue

    # Extraer primera columna (asumiendo separador coma)
    code="${line%%,*}"

    # Limpiar espacios y comillas
    code="$(echo "$code" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//;s/^"//;s/"$//')"

    # Ignorar cabecera o códigos vacíos
    [[ -z "$code" ]] && continue
    [[ "$code" =~ ^[[:space:]]*Código ]] && continue

    lfp_codes+=("$code")
done < <(sed 's/\r$//' "$CSV_FILE")

# Eliminar duplicados
declare -A seen
unique_codes=()
for code in "${lfp_codes[@]}"; do
    if [[ -z "${seen[$code]:-}" ]]; then
        seen[$code]=1
        unique_codes+=("$code")
    fi
done

log INFO "Códigos LFP únicos a procesar: ${#unique_codes[@]}"

# Procesar cada código LFP
for code in "${unique_codes[@]}"; do
    found_any=false

    # Buscar ficheros que contengan el código en su nombre (sin recursividad)
    while IFS= read -r -d '' file; do
        found_any=true
        filename="$(basename "$file")"
        log INFO "Moviendo: '$filename' -> '$DEST_DIR/'"
        mv -- "$file" "$DEST_DIR/"
    done < <(find "$BASE_DIR" -maxdepth 1 -type f -name "*${code}*" -print0 2>/dev/null)

    if [[ "$found_any" == false ]]; then
        log WARN "No se encontró ningún fichero para el código '$code' en '$BASE_DIR'"
    fi
done

# Calcular duración
DURATION=$SECONDS
MINUTES=$((DURATION / 60))
SECS=$((DURATION % 60))

log INFO "Proceso completado. Duración total: ${MINUTES}m ${SECS}s."
