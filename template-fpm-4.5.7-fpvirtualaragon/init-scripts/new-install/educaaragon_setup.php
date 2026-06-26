<?php
/**
 * Configuracion automatica del plugin local_educaaragon.
 *
 * Crea (si no existe) un repositorio de tipo filesystem apuntando a
 * moodledata/repository/recursos-editables y configura el plugin
 * local_educaaragon para utilizarlo.
 *
 * @package local_educaaragon
 */

define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/repository/lib.php');

mtrace('=== Configurando plugin local_educaaragon ===');

// 1. Activar el tipo de repositorio filesystem si no esta activo.
$type = repository::get_type_by_typename('filesystem');
if (!$type) {
    mtrace('Tipo de repositorio filesystem no encontrado. Creandolo...');
    $type = new repository_type('filesystem', [], true);
    $type->create();
    $type = repository::get_type_by_typename('filesystem');
}
$type->update_visibility(true);
mtrace('Tipo de repositorio filesystem activado.');

// 2. Buscar o crear la instancia del repositorio.
$target_path = 'recursos-editables';
$instances   = repository::get_instances(['type' => 'filesystem']);
$repository_id = null;

foreach ($instances as $instance) {
    if ($instance->get_option('fs_path') === $target_path) {
        $repository_id = $instance->id;
        break;
    }
}

if (!$repository_id) {
    mtrace("Creando instancia del repositorio filesystem para '{$target_path}'...");
    $instance = repository::create(
        'filesystem',
        0,
        context_system::instance(),
        [
            'name'          => 'Recursos Editables',
            'fs_path'       => $target_path,
            'relativefiles' => 0,
        ],
        0
    );
    $repository_id = $instance->id;
    mtrace("Repositorio creado con ID {$repository_id}.");
} else {
    mtrace("Repositorio ya existe con ID {$repository_id}.");
}

// 3. Configurar el plugin local_educaaragon.
set_config('activetask', 1, 'local_educaaragon');
set_config('repository', $repository_id, 'local_educaaragon');
set_config('allcourses', 1, 'local_educaaragon');

mtrace('Plugin local_educaaragon configurado correctamente.');
mtrace('=============================================');
