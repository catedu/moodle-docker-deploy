#!/bin/bash
# Upgrade de plugins.
# Lee el catalogo desde /init-scripts/plugins.json y las variables PLUGIN_* del .env.
# Filtra por SCHOOL_TYPE e INSTALL_TYPE=upgrade.

# Cargar helpers
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/../lib/plugins-lib.sh"

# GET PLUGIN LIST
echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"

# INSTALL PLUGINS (theme is installed in theme.sh)
echo >&2 "Installing plugins..."
echo "Moodle's version: ${VERSION}"
VERSION_MINOR=$(echo ${VERSION} | cut -d. -f1,2)
echo "Moodle's minor version: ${VERSION_MINOR}"

# Mostrar resumen antes de empezar
plugins_show_summary "${SCHOOL_TYPE}" "upgrade"

# Iterar sobre los plugins habilitados para upgrade
while IFS= read -r PLUGIN; do
    [ -z "$PLUGIN" ] && continue

    echo ""
    echo "===> Processing plugin: ${PLUGIN}"

    # En upgrade instalamos directamente (sin comprobacion remota previa)
    echo "trying to install ${PLUGIN} ..."
    moosh plugin-install -d ${PLUGIN} || echo "${PLUGIN} already present or install skipped"
done < <(plugins_list_enabled "${SCHOOL_TYPE}" "upgrade")

echo >&2 "Plugins installed!"
