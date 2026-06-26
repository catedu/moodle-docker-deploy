# AGENTS.md — Guía para agentes de código

Este documento resume la arquitectura, convenciones y procesos del repositorio `moodle-docker-deploy`. Está escrito para agentes de IA que no conozcan el proyecto. El contenido se basa exclusivamente en los archivos del repositorio; no se asumen comportamientos no documentados.

---

## 1. Visión general del proyecto

Este repositorio automatiza el despliegue, actualización, migración y borrado de instancias Moodle contenedorizadas. Está orientado a dos productos principales:

- **Aeducar**: plataformas Moodle para centros educativos de Aragón (CEIP, CPI, IES, CPEPA).
- **FP Virtual Aragón**: plataforma Moodle para Formación Profesional a Distancia (FPD), con dominios propios (`*.fpvirtualaragon.es`, `*.campusdigitalfp.com`).

Cada instancia Moodle se despliega como un conjunto de contenedores Docker Compose independiente, compartiendo una red frontal común gestionada por un `nginx-proxy` centralizado con soporte SSL automático (Let's Encrypt).

---

## 2. Stack tecnológico

- **Orquestación**: Docker Compose (sintaxis `version: "3"` / `"3.5"`).
- **Servidor web / proxy inverso**: `jwilder/nginx-proxy` + `jrcs/letsencrypt-nginx-proxy-companion`.
- **Servidor de aplicaciones Moodle**:
  - Imágenes personalizadas publicadas en Docker Hub: `cateduac/moodle:<version>-nginx-fpm-unoconv` o `cateduac/moodle:<version>-apache`.
  - La plantilla activa (`template/`) usa actualmente `cateduac/moodle:4.5.7-nginx-fpm-unoconv`.
- **Base de datos**: MariaDB/MySQL externa, accedida desde los contenedores Moodle (host definido en `MOODLE_DB_HOST`).
- **Caché**: Redis (un contenedor por instancia).
- **Configuración PHP**: volúmenes montados sobre `/usr/local/etc/php/conf.d/` y `/usr/local/etc/php-fpm.d/`.
- **Automatización interna**: scripts Bash + [`moosh`](https://moosh-online.com/) para configurar Moodle, instalar plugins, crear usuarios, roles, categorías y restaurar cursos.
- **Gestión DNS (opcional)**: scripts Node.js (`api-ovh/`) que usan la API de OVH para crear/borrar registros A en la zona `aeducar.es`.
- **Gestión de secretos**: variables de entorno en archivos `.env` (no commiteados; sí hay ficheros `env-sample` y `env-sample-update`).

---

## 3. Arquitectura de runtime

### 3.1 Componentes por instancia Moodle

Cada instancia se materializa en un directorio cuyo nombre coincide con el `VIRTUAL_HOST` (por ejemplo, `ies.ejemplo.aeducar.es`). Dentro hay:

- `docker-compose.yml` → define los servicios `redis`, `web` (nginx) y `moodle`.
- `.env` → variables del sitio (host virtual, credenciales DB, datos del admin, etc.).
- `moodle-code/` → código fuente de Moodle.
- `moodle-data/` → `moodledata` (volúmenes persistentes de Moodle).
- `init-scripts/` → scripts de inicialización/actualización copiados desde la plantilla.
- `nginx/default.conf` → configuración del nginx frontal de la instancia.
- `php-conf/`, `fpm-conf/` → configuraciones PHP y PHP-FPM.

### 3.2 Redes Docker

- `nginx-proxy_frontend` (externa): permite que `nginx-proxy` enrute tráfico HTTP/HTTPS a los contenedores `web`/`moodle` según la variable `VIRTUAL_HOST`.
- `redis-network` (bridge por instancia): comunicación Moodle ↔ Redis.
- `moodle_network` (externa, solo en plantillas recientes): para integraciones adicionales.

### 3.3 Proxy central

El directorio `nginx-proxy/` contiene su propio `docker-compose.yml` con:

- `nginx-proxy` en puertos 80/443.
- `letsencrypt` para generar certificados automáticamente a partir de `LETSENCRYPT_HOST`.
- `phpmyadmin` como utilidad puntual para administrar la base de datos.

Debe levantarse **antes** de crear instancias Moodle.

---

## 4. Estructura de directorios

```
.
├── .env                          # Variables globales (no commiteado)
├── env-sample                    # Plantilla de variables globales
├── env-sample-update             # Plantilla para añadir/modificar vars en updates
├── createMoodle.sh               # Crear una nueva instancia
├── updateMoodle.sh               # Actualizar instancia (misma versión Moodle)
├── upgradeMoodle.sh              # Actualizar instancia a versión superior
├── upgradeMoodleold.sh           # Versión antigua del upgrade (conservada)
├── deleteMoodle.sh               # Borrar instancia y backups
├── restoreMoodle.sh              # Restaurar una instancia a partir de backup
├── migrateMoodle.sh              # Migrar instancia a otro servidor
├── migrateDB.sh                  # Migrar solo la base de datos
├── deleteTeacherBackups.sh       # Limpiar ficheros de backup de profesorado
├── README.md                     # Documentación humana (español/inglés)
│
├── nginx-proxy/                  # Compose del proxy inverso central
│   ├── docker-compose.yml
│   ├── custom.conf
│   └── env-sample
│
├── api-ovh/                      # Scripts Node.js para gestión DNS OVH
│   ├── package.json
│   ├── createSubdomain.js
│   ├── deleteSubdomain.js
│   ├── askCredentials.js
│   └── env.sample
│
├── template/                     # Plantilla activa (Moodle 4.5.7 nginx+fpm)
│   ├── docker-compose.yml
│   ├── init-scripts/
│   │   ├── init.sh
│   │   ├── new-install/          # Scripts de primera instalación
│   │   ├── upgrade/              # Scripts de actualización de versión
│   │   ├── mbzs/                 # Backups de cursos (.mbz)
│   │   └── themes/               # Recursos de tema Moove / fpdist
│   ├── nginx/default.conf
│   ├── php-conf/
│   └── fpm-conf/
│
├── template-apache-3.7.6/        # Plantilla legacy Apache/Moodle 3.7.6
├── template-aularagon-4.0.4/     # Plantillas históricas nginx+fpm
├── template-aularagon-4.1.1/
├── template-aularagon-4.1.3/
├── template-fpm-4.1.6-unoconv/
├── template-fpm-4.2.1-unoconv/
└── template-fpm-4.5.7-fpvirtualaragon/  # Equivalente a template/ actual
│
├── procedimiento-inicio-curso/   # Scripts CSV y utilidades de inicio de curso
└── procedimiento-final-curso/    # Scripts de backup y limpieza de fin de curso
```

Los directorios `template-*/` son **plantillas de origen**; las instancias reales se generan como carpetas hermanas en la raíz del proyecto.

---

## 5. Ficheros de configuración clave

### 5.1 Variables globales (`env-sample` → `.env` raíz)

Se usan en `createMoodle.sh` para crear la base de datos y generar el `.env` del nuevo sitio:

- `MYSQL_ROOT_PASSWORD`, `MOODLE_DB_HOST` → acceso al servidor MariaDB.
- `SSL_EMAIL` → correo para certificados Let's Encrypt.
- `MOODLE_ADMIN_USER`, `MOODLE_ADMIN_PASSWORD`, `MOODLE_ADMIN_EMAIL`, `MOODLE_LANG`, `MOODLE_SITE_NAME`, `MOODLE_SITE_FULLNAME`.
- `SMTP_HOSTS`, `SMTP_USER`, `SMTP_PASSWORD`, `SMTP_MAXBULK`, `NO_REPLY_ADDRESS`.
- `CRON_BROWSER_PASS`, `MOODLE_MANAGER`, `MANAGER_PASSWORD`, `ASESORIA_PASSWORD`, `ASESORIA_EMAIL`.
- Variables específicas FPD: `FPD_PASSWORD`, `FPD_EMAIL`, `APP_PASSWORD`, `APP_TEACHER_PASSWORD`.
- `BLACKBOARD_URL`, `BLACKBOARD_KEY`, `BLACKBOARD_SECRET`.

### 5.2 Variables por sitio (`<sitio>/.env`)

Generadas automáticamente por `createMoodle.sh`. Campos importantes:

- `VIRTUAL_HOST`, `MOODLE_URL`, `SSL_EMAIL`, `SSL_PROXY=true`.
- `MOODLE_DB_NAME`, `MOODLE_MYSQL_USER`, `MOODLE_MYSQL_PASSWORD`.
- `INSTALL_TYPE` (`new-install`, `update`, `upgrade`).
- `SCHOOL_TYPE` (`CEIP`, `CPI`, `IES`, `CPEPA`, `FPD`, `VACIO`).
- `VERSION` → versión de Moodle, extraída de la imagen Docker.

### 5.3 Docker Compose

- `template/docker-compose.yml` es la referencia actual.
- Cada plantilla histórica tiene el suyo propio con versiones de imagen anteriores.

### 5.4 package.json

Solo existe en `api-ovh/package.json`. No hay tests; el script `test` es el placeholder por defecto.

---

## 6. Flujos de trabajo (comandos principales)

> **No hay tests unitarios ni un sistema de CI/CD en el repositorio.** La "build" es el propio despliegue de contenedores. Los scripts están pensados para ejecutarse manualmente en el servidor de producción.

### 6.1 Crear una instancia

```bash
# Copiar y configurar variables globales
cp env-sample .env
# Editar .env con los valores reales

# Crear instancia (ejemplo IES)
./createMoodle.sh -t IES -u "https://ies.ejemplo.aeducar.es" -e admin@centro.com \
  -l es -n "IES Ejemplo" ies_ejemplo
```

El script:

1. Lee `.env` raíz.
2. Genera una contraseña aleatoria para la base de datos.
3. Crea la base de datos y el usuario MySQL.
4. Crea el directorio de la instancia copiando `template/`.
5. Genera el `.env` del sitio.
6. Crea directorios de repositorios y monta `bind mounts` para recursos compartidos (`zz_cursos_cidead`, `zz_ftp_ministerio*`, etc.).
7. Levanta los contenedores con `docker compose up -d`.
8. Opcionalmente crea el registro DNS en OVH si la URL no resuelve.

### 6.2 Actualizar (misma versión de Moodle)

```bash
./updateMoodle.sh -y -u "ies.ejemplo.aeducar.es" -d template-aularagon-4.1.3
```

- Requiere que el `VERSION` del sitio y de la plantilla coincidan.
- Hace backup de la base de datos y de los ficheros en `/var/backup_update/`.
- Sincroniza la plantilla sobre el directorio del sitio.
- Cambia `INSTALL_TYPE=update` para que se ejecuten los scripts de `init-scripts/upgrade/`.

### 6.3 Actualizar a versión superior (upgrade)

```bash
./upgradeMoodle.sh -y -u "ies.ejemplo.aeducar.es" -d template-fpm-4.5.7-fpvirtualaragon
```

- Requiere que la plantilla tenga una versión **superior** a la del sitio.
- Guarda el `config.php` anterior en `oldmoodlecode/`, elimina `moodle-code/` y vuelve a generarlo desde la plantilla.
- Backup en `/var/backup_upgrade/`.
- Cambia `INSTALL_TYPE=upgrade` y actualiza `VERSION`.
- Incluye mecanismo de rollback por `trap` en caso de error.

### 6.4 Borrar una instancia

```bash
./deleteMoodle.sh -y -u "ies.ejemplo.aeducar.es"   # conserva la DB
./deleteMoodle.sh -y -b -u "ies.ejemplo.aeducar.es" # borra también la DB
```

- Hace backup en `/var/backup_delete/`.
- Desmonta los `bind mounts` de repositorios.
- Borra el directorio del sitio.
- Elimina el registro DNS en OVH.

### 6.5 Migrar a otro servidor

```bash
./migrateMoodle.sh -y -i /ruta/clave_ssh -u "ies.ejemplo.aeducar.es" -s 192.168.1.100
./migrateDB.sh -y -b 192.168.1.200 -i /ruta/clave_ssh -u "ies.ejemplo.aeducar.es"
```

- `migrateMoodle.sh` transfiere el directorio completo vía `rsync`+SSH y cambia el DNS.
- `migrateDB.sh` migra solo la base de datos a otro servidor de bases de datos.
- Ambos usan el usuario remoto `debian` y rutas fijas en `/var/moodle-docker-deploy`.

### 6.6 Tareas de mantenimiento

- `deleteTeacherBackups.sh` → recorre todos los directorios `*.es` y borra ficheros con `mimetype='application/vnd.moodle.backup'` mediante `moosh file-delete`.
- `procedimiento-inicio-curso/renombrar_shortname_loe_a_lfp.sh` → renombra shortnames de cursos según CSV de equivalencias LOE→LFP.
- `procedimiento-final-curso/backup_to_mbz_moodle_courses.sh` → exporta cursos a `.mbz`.
- `procedimiento-final-curso/mover-cursos-lfp-vacios.sh` → mueve ficheros de cursos LFP vacíos a `LFP.old/`.

---

## 7. Organización del código

### 7.1 Scripts de orquestación (raíz)

- Todos son Bash.
- Usan `set -eu` para detenerse ante errores y variables no definidas.
- Uso intensivo de `getopts` para parsear argumentos.
- Cargan variables con `set -a; source .env; set +a`.
- Usan `docker compose` (nueva sintaxis) salvo `updateMoodle.sh`, `migrateMoodle.sh`, `migrateDB.sh` y `upgradeMoodleold.sh`, que todavía usan `docker-compose` (CLI antiguo).

### 7.2 Scripts de inicialización (`template/init-scripts/`)

`init.sh` es el punto de entrada del contenedor Moodle. Decide qué ejecutar según `INSTALL_TYPE`:

```bash
FILES="/init-scripts/${INSTALL_TYPE}/moodle.sh
/init-scripts/${INSTALL_TYPE}/plugins.sh
/init-scripts/${INSTALL_TYPE}/import_${SCHOOL_TYPE}_categories_and_courses.sh
/init-scripts/${INSTALL_TYPE}/theme.sh"
```

- `new-install/moodle.sh` → configura parámetros generales de Moodle, SMTP, autenticación, usuarios base (`gestorae`, `asesoria`, `familiar`), estudiantes de prueba, notificaciones, apps móviles, etc.
- `new-install/plugins.sh` → instala y configura plugins adicionales (`format_tiles`, `block_xp`, `local_mail`, `mod_pdfannotator`, `mod_board`, `local_educaaragon`, etc.). Lee el catálogo desde `/init-scripts/plugins.json` y filtra por `SCHOOL_TYPE` e `INSTALL_TYPE`. Las variables `PLUGIN_*` del `.env` permiten habilitar/deshabilitar plugins en runtime.
- `new-install/import_<TIPO>_categories_and_courses.sh` → crea categorías, cursos, roles y cohortes específicos del tipo de centro. Para `VACIO` no importa nada.
- `new-install/theme.sh` → aplica el tema `moove` y personalizaciones (Aeducar / FPD).
- `upgrade/moodle.sh` → ejecuta `admin/cli/upgrade.php` automatizado con `expect` y aplica ajustes post-upgrade.
- `upgrade/plugins.sh` y `upgrade/theme.sh` → reinstalan/recuperan plugins y tema tras el upgrade.

### 7.3 Plantillas

- `template/` debe considerarse la versión canónica actual.
- Las demás plantillas son versiones históricas necesarias para `updateMoodle.sh` y `upgradeMoodle.sh`.
- La única diferencia significativa entre plantillas suele ser la imagen Docker (`cateduac/moodle:<version>-...`) y pequeños ajustes de variables de entorno.

---

## 8. Convenciones de estilo

- **Idioma**: los comentarios y la documentación mezclan español e inglés; predominan los comentarios en español en los scripts de inicialización y de procedimientos.
- **Shebang**: `#!/bin/bash` en scripts raíz; `#!/usr/bin/env bash` en algunos scripts recientes de `procedimiento-*`.
- **Modo estricto**: `set -eu` (o `set -euo pipefail` en scripts más recientes).
- **Nombres de variables**: mayúsculas para variables globales/de entorno (`MOODLE_URL`, `WORKDIR`), minúsculas para locales.
- **Indentación**: mezcla de 4 espacios y tabuladores; no hay formateador obligatorio.
- **Mensajes**: se usa `echo >&2 "..."` para mensajes de error/diagnóstico y `echo "# $(basename $0) - ..."` en `deleteMoodle.sh`/`migrateMoodle.sh`.
- **Captura de IDs de moosh**: tras cambios recientes (junio 2026) se utiliza el patrón `| grep -o '[0-9]*' | tail -1` para extraer únicamente el ID numérico de comandos como `moosh category-create`, `moosh course-create`, `moosh course-restore`, `moosh user-create` y `moosh role-create`.
- **Backups**: se guardan siempre bajo `/var/backup_<tipo>/<fecha-hora>__<sitio>/`.

---

## 9. Consideraciones de seguridad

- **Credenciales**: se almacenan en archivos `.env` que **no deben commitearse** (están en `.gitignore`).
- **OVH**: `api-ovh/.env` contiene `APP_KEY`, `APP_SECRET` y `TOKEN`; nunca se incluye en el repositorio.
- **Bind mounts sensibles**: el `.gitignore` oculta directorios `zz_*` (repositorios de cursos compartidos), `secret.php` y credenciales de herramientas de soporte.
- **MySQL**: los usuarios de base de datos se crean con host `192.168.1.%`; asegurarse de que la red/contenedores tengan acceso autorizado.
- **SSH**: `migrateMoodle.sh` y `migrateDB.sh` usan `StrictHostKeyChecking=no`; en entornos restringidos esto puede suponer un riesgo de MITM.
- **Permisos**: los scripts usan `sudo` para montar/desmontar, cambiar propietarios (`www-data:www-data`) y eliminar directorios; requieren que el usuario de ejecución tenga privilegios adecuados.
- **Airnotifier**: las claves de acceso a Moodle Mobile Notifications están hardcodeadas en `new-install/moodle.sh`; son credenciales de servicio.

---

## 10. Notas operativas importantes

- El proxy `nginx-proxy` debe estar levantado **antes** de crear sitios; de lo contrario no se generarán los certificados SSL ni se enrutará el tráfico.
- Los dominios deben apuntar a la IP pública del servidor para que `check_url` en `createMoodle.sh` / `restoreMoodle.sh` tenga éxito; si no, el script intenta crear el registro DNS vía OVH.
- La imagen Moodle base (`cateduac/moodle`) incluye `moosh`, `expect` y herramientas necesarias para ejecutar los scripts de inicialización.
- Tras un upgrade, `moodle-code` se regenera desde cero; cualquier modificación manual al código fuente debe respaldarse o aplicarse mediante volúmenes/patches.
- Los scripts de procedimientos de inicio/fin de curso son manuales y dependen de CSVs locales; verificar siempre las rutas y el contenedor objetivo antes de ejecutarlos.

---

## 11. Cómo empezar a trabajar

1. Revisar `README.md` para la visión general humana.
2. Copiar `env-sample` a `.env` y rellenar los valores reales.
3. Copiar `nginx-proxy/env-sample` a `nginx-proxy/.env` y levantar el proxy:
   ```bash
   cd nginx-proxy && docker compose up -d
   ```
4. (Opcional) Configurar `api-ovh/.env` y ejecutar `npm install` en `api-ovh/` si se desea gestión DNS automática.
5. Para modificar el comportamiento de una instalación limpia, editar archivos en `template/init-scripts/new-install/`.
6. Para modificar el comportamiento de un upgrade, editar archivos en `template/init-scripts/upgrade/`.
7. Para actualizar la versión de Moodle por defecto, modificar la imagen en `template/docker-compose.yml` y asegurar la compatibilidad de plugins en `template/init-scripts/plugins.json`.
8. Para añadir/modificar plugins, editar `template/init-scripts/plugins.json` y, si es necesario, añadir acciones post-instalación en `template/init-scripts/new-install/plugins.sh`.
9. Para el plugin `local_educaaragon` (FPD), configurar `EDUCAARAGON_RESOURCES_PATH` en `.env` y asegurar que el directorio `recursos-editables` existe en el host.
