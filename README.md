# moodle-docker-deploy

Arquitecture de deploy moodle containers

## Customize new deploy

1. Copy .env-sample to .env in root directory and customize creation new moodle's to your infraestrcture

2. Copy .env-sample to .env in nginx-proxy customize your proxy and phpmyadmin connection

3. To create DNS dinamic with OVH provider (api-ovh), you can use api-ovh. Copy .env-sample to .env and customize your API connection with OVH. (not mandatory)
   1. It's necessary to run npm install in api-ovh directory for dependences

4. Think and create a template directory (with this name) to deploy

5. Run createMoodle.sh with this template

## Update Upgrade deploy

1. Think and create a template directory to update your deploy

2. Copy env-sample-update to .env-update and customize new variables to add or modify variables (not mandatory)

3. Run upgradeMoodle over your deploy using the template

Note: The update/upgrade process make a backup of all data of the deploy in the root directoy

## Actualizaciones a pasar de Moodle 4.1.19+ a Moodle 4.5.7

Fecha: junio de 2026

## Cambio 1

Listo. He actualizado las **54 líneas** donde se invoca `moosh category-create` en el script para extraer únicamente el ID numérico de la salida.

**Cambio aplicado en cada línea:**
```bash
# Antes
ID_CATEGORY_sg=$(moosh category-create -p 0 -v 1 -d "22002521" "IES SIERRA DE GUARA")

# Después
ID_CATEGORY_sg=$(moosh category-create -p 0 -v 1 -d "22002521" "IES SIERRA DE GUARA" | grep -o '[0-9]*' | tail -1)
```

Esto captura el último número de la salida (p. ej. `22` de `Created category IES SIERRA DE GUARA 2 with id: 22.`), independientemente del texto precedente. También funciona correctamente en categorías con paréntesis en el nombre, como *"Educación Infantil (Formación Profesional)"*.

## Cambio 2

He revisado y corregido el script activo `import_FPD_categories_and_courses.sh` (el backup servía de referencia). El problema era que moosh 1.34 ahora devuelve frases completas en lugar de solo el ID numérico.

He aplicado el mismo patrón de extracción que ya se usaba para `category-create` (`| grep -o '[0-9]*' | tail -1`) a todos los comandos moosh que capturan IDs en variables:

**Cambios realizados:**

1. **`moosh course-create`** (línea 767):
   ```bash
   COURSE_ID=$(moosh course-create ... | grep -o '[0-9]*' | tail -1)
   ```

2. **`moosh course-restore`** (líneas 771-772):
   Unificado en una sola línea más robusta:
   ```bash
   COURSE_ID=$(moosh course-restore ... | tail -n 1 | grep -o '[0-9]*' | tail -1)
   ```

3. **`moosh user-create`** — Todas las capturas de IDs de usuario:
   - `FPD_ADMIN_USER_ID`
   - `INSPECCION_USER_ID`
   - `JE_SG_USER_ID` ... `JE_MM_USER_ID`
   - `FPD_APP_USER_STUDENT_ID` y `FPD_APP_USER_TEACHER_ID`

4. **`moosh role-create`** — Todas las capturas de IDs de rol:
   - `INSPECCION_ROLE_ID`
   - `JEFATURA_ROLE_ID`

Con estos cambios, todas las variables quedan únicamente con el número de ID, independientemente de la frase que devuelva moosh 1.34.

¿Quieres que aplique la misma corrección a los demás scripts (`import_IES_categories_and_courses.sh`, etc.) si los hubiera, o actualice también el archivo `_backup`?

