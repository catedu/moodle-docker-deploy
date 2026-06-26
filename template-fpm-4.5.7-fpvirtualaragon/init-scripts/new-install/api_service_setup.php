<?php
/**
 * Script CLI para crear/configurar servicio externo de Moodle,
 * autorizar usuario, registrar funciones y generar token.
 *
 * Usa la API nativa de Moodle (clase webservice) en lugar de SQL directo,
 * evitando el bug de moosh sql-run con strings que contienen ':'.
 */

define('CLI_SCRIPT', true);

require('/var/www/html/config.php');
require_once($CFG->dirroot . '/webservice/lib.php');

// -----------------------------------------------------------------------------
// CONFIGURACIÓN
// -----------------------------------------------------------------------------
$service_name       = 'Test API';
$service_shortname  = 'test_api';
$api_user           = 'moodle-api';

// -----------------------------------------------------------------------------
// 1. VALIDAR USUARIO
// -----------------------------------------------------------------------------
$user = $DB->get_record('user', ['username' => $api_user, 'deleted' => 0]);

if (!$user) {
    fwrite(STDERR, "ERROR: Usuario '$api_user' no encontrado.\n");
    exit(1);
}

$ws = new webservice();

// -----------------------------------------------------------------------------
// 2. CREAR O ACTUALIZAR SERVICIO EXTERNO
// -----------------------------------------------------------------------------
$service = new stdClass();
$service->name             = $service_name;
$service->shortname        = $service_shortname;
$service->enabled          = 1;
$service->restrictedusers  = 1;
$service->downloadfiles    = 1;
$service->uploadfiles      = 1;

$existing = $ws->get_external_service_by_shortname($service_shortname);

if ($existing) {
    $service->id = $existing->id;
    $ws->update_external_service($service);
    $serviceid = $existing->id;
    echo "  ✓ Servicio actualizado (ID: $serviceid)\n";
} else {
    $serviceid = $ws->add_external_service($service);
    echo "  ✓ Servicio creado (ID: $serviceid)\n";
}

// -----------------------------------------------------------------------------
// 3. AUTORIZAR USUARIO AL SERVICIO
// -----------------------------------------------------------------------------
if (!$ws->get_ws_authorised_user($serviceid, $user->id)) {
    $auth = new stdClass();
    $auth->externalserviceid = $serviceid;
    $auth->userid            = $user->id;
    $auth->timecreated       = time();
    $auth->timemodified      = time();
    $auth->creatorid         = $user->id;
    $DB->insert_record('external_services_users', $auth);
    echo "  ✓ Usuario autorizado al servicio\n";
} else {
    echo "  ✓ Usuario ya estaba autorizado\n";
}

// -----------------------------------------------------------------------------
// 4. AÑADIR FUNCIONES AL SERVICIO
// -----------------------------------------------------------------------------
$functions = [
    // Usuarios
    'core_user_create_users',
    'core_user_delete_users',
    'core_user_update_users',
    'core_user_get_users',
    'core_user_get_users_by_field',
    'core_user_get_course_user_profiles',
    // Cohortes
    'core_cohort_add_cohort_members',
    'core_cohort_delete_cohort_members',
    'core_cohort_get_cohort_members',
    'core_cohort_get_cohorts',
    'core_cohort_create_cohorts',
    'core_cohort_delete_cohorts',
    'core_cohort_update_cohorts',
    'core_cohort_search_cohorts',
    // Cursos
    'core_course_get_courses',
    'core_course_get_courses_by_field',
    'core_course_create_courses',
    'core_course_update_courses',
    'core_course_delete_courses',
    'core_course_import_course',
    'core_course_search_courses',
    'core_course_get_contents',
    'core_course_get_categories',
    'core_course_create_categories',
    // Matriculación
    'enrol_manual_enrol_users',
    'enrol_manual_unenrol_users',
    'core_enrol_get_enrolled_users',
    'core_enrol_get_course_enrolment_methods',
    'core_enrol_get_users_courses',
    'core_enrol_get_enrolled_users_with_capability',
    'core_enrol_get_potential_users',
    'core_enrol_search_users',
    'core_enrol_edit_user_enrolment',
    // Grupos
    'core_group_create_groups',
    'core_group_delete_groups',
    'core_group_get_groups',
    'core_group_get_course_groups',
    'core_group_add_group_members',
    'core_group_delete_group_members',
    'core_group_get_group_members',
    'core_group_update_groups',
    // Backup/Restore
    'core_backup_get_course_backup_status',
    'core_backup_get_copy_progress',
    'core_backup_submit_course_backup',
    'core_course_duplicate_course',
    // Archivos
    'core_files_get_files',
    'core_files_upload',
    'core_files_delete_draft_files',
    'core_files_get_unused_draft_itemid',
    // Roles
    'core_role_assign_roles',
    'core_role_unassign_roles',
    // Info sitio
    'core_webservice_get_site_info',
];

$added = 0;
$skipped = 0;

foreach ($functions as $func) {
    if (!$ws->service_function_exists($func, $serviceid)) {
        $ws->add_external_function_to_service($func, $serviceid);
        $added++;
    } else {
        $skipped++;
    }
}

echo "  ✓ $added funciones añadidas, $skipped ya existían (total: " . count($functions) . ")\n";

// -----------------------------------------------------------------------------
// 5. GENERAR TOKEN
// -----------------------------------------------------------------------------
// generate_user_ws_tokens() solo actúa si:
// - El usuario NO es siteadmin (por eso quitamos admin a moodle-api).
// - Tiene la capacidad moodle/webservice:createtoken.
// - Los webservices están habilitados a nivel de sitio.
$ws->generate_user_ws_tokens($user->id);

// -----------------------------------------------------------------------------
// 6. RECUPERAR E IMPRIMIR TOKEN
// -----------------------------------------------------------------------------
$token = $DB->get_record('external_tokens', [
    'userid'            => $user->id,
    'externalserviceid' => $serviceid,
    'tokentype'         => EXTERNAL_TOKEN_PERMANENT,
]);

if ($token) {
    echo "  ✓ Token generado: " . $token->token . "\n";
} else {
    fwrite(STDERR, "ERROR: No se pudo generar/recuperar el token.\n");
    fwrite(STDERR, "Verifica que el usuario tenga la capacidad 'moodle/webservice:createtoken'.\n");
    exit(1);
}

echo "\n==========================================\n";
echo "CONFIGURACIÓN COMPLETADA\n";
echo "==========================================\n";
echo "Servicio:     $service_name\n";
echo "Shortname:    $service_shortname\n";
echo "Usuario API:  $api_user (ID: {$user->id})\n";
echo "Token:        {$token->token}\n";
echo "Endpoint:     {$CFG->wwwroot}/webservice/rest/server.php\n";
echo "==========================================\n";
