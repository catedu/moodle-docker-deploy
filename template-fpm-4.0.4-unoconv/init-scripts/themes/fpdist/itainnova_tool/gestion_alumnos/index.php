<?php

// Comprobación de administrador
require_once '../libreria/checkAdmin.php';
include $CFG->itainnovatool_libdir.'/GestionAlumnos/autoloader.php';

$title = "Gestión de Alumnos";

$download = optional_param('download', '', PARAM_ALPHA);

$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$baseUrl = new moodle_url('/itainnova_tool/gestion_alumnos') . "/index.php?" . $query;

// Llamamos la clase de Gestión de Alumnos
$tablaAlumnos = new TablaAlumnos($baseUrl);
$table = null;

// Utilizamos estas variables para añadir la clase de "active" al elemento del submenú actual (TAB)
$listadoSigadActiv = $imporarSigadActiv = '';

// "TAB" (elemento del submenú actual)
if (empty($_GET['tab'])) $tabActual = 'actuales';
else $tabActual = $_GET['tab'];

if ($tabActual == 'listadosigad') {

    $listadoSigadActiv = 'ui-tabs-active ui-state-active';

    if (empty($_GET['listadotipo'])) {
        $params['tipo'] = 'comparacion';
        $params['comparar'] = 'datos';
    } else {
        $params['tipo'] = $_GET['listadotipo'];
        $params['comparar'] = $_GET['comparar'];
    }

    // Alumnos importados
    $table = $tablaAlumnos->getListAlumnosImportHTML($params);

} elseif ($tabActual == 'importarsigad') {

    $imporarSigadActiv = 'ui-tabs-active ui-state-active';

    // POST de la importación de archivos
    if (file_exists($_FILES['excelFile']['tmp_name']) && is_uploaded_file($_FILES['excelFile']['tmp_name'])) {
        $importAlumnos = new ImportExcelAlumnos('excelFile');
        $importAlumnos->upload();
    }
}

// Si no se está exportando, mostramos todo el HTML
// Si exportamos, debemos mostrar solo la información de exportación
// Se controla con la función de la tabla $table->is_downloading()

if ((!empty($table) && !$table->is_downloading()) || $tabActual == 'importarsigad') {

    // Configuración de la página
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->set_cacheable(false);
    $PAGE->navbar->ignore_active();
    $PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
    $PAGE->set_url('/itainnova_tool/gestion_alumnos/index.php');
    $PAGE->navbar->add($title, new moodle_url(substr($PAGE->url, 0, strpos($PAGE->url, '?'))));
    $PAGE->set_pagelayout('frametop');

    // Añadimos todos los SCRIPTS y CSS que necesita el sistema para funcionar
    // En el footer están otros JS que se han tenido que añadir manualmente
    // El parametro "ver" (versión) es para eliminar caché. Funciona cambiando los números de la variable
    $ver = '6544';
    $PAGE->requires->js(new moodle_url('/itainnova_tool/gestion_alumnos/js/upload-config.js?ver='.$ver), false);
    $PAGE->requires->js(new moodle_url('/itainnova_tool/gestion_alumnos/js/upload-ui.js?ver='.$ver), false);
    $PAGE->requires->js(new moodle_url('/itainnova_tool/gestion_alumnos/js/gestion_alumnos.js?ver='.$ver), false);
    $PAGE->requires->css(new moodle_url('/itainnova_tool/gestion_alumnos/css/bootstrap.css?ver='.$ver));
    $PAGE->requires->css(new moodle_url('/itainnova_tool/gestion_alumnos/css/styles.css'));

    echo $OUTPUT->header();
    echo '<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';

    // Subtitulo y explicación de la sección
    $subtitle = '';
    $explanation = '';
    if(!empty($_GET['listadotipo'])){
        switch($_GET['listadotipo']){
            case 'comparacion':
                if($_GET['comparar'] == 'datos'){
                    $subtitle = ' - Avisos Cambio de Email';
                }else{
                    $subtitle = ' - Avisos Cambios de Otros Datos';
                }
                // $explanation = '<p>Puede hacer clic en cada fila para obtener información detallada</p>';
                $explanation = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Puede hacer clic en cada fila para obtener información detallada</strong> 
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>';
            break;
            case 'altas':
                $subtitle = ' - Altas de Alumnos';
            break;
            case 'modulos':
                $subtitle = ' - Errores de Módulos';
            break;
            case 'altasCursos':
                $subtitle = ' - Altas de Alumnos en Cursos';
            break;
            case 'bajas':
                $subtitle = ' - Bajas de Alumnos';
            break;
            case 'bajasCursos':
                $subtitle = ' - Bajas de Alumnos en Cursos';
            break;
        }
    }
    ?>

    <h2><?= $title . $subtitle?></h2>

    <div id="tabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
        <ul class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
            <li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab <?= $imporarSigadActiv ?>">
                <a class="ui-tabs-anchor" href="./index.php?tab=importarsigad">Importar SIGAD</a></li>
            <li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab <?= $listadoSigadActiv ?>">
                <a class="ui-tabs-anchor" href="./index.php?tab=listadosigad">Listado Alumnos Importados</a></li>
            <!--<li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab <?= $listadoSigadActiv ?>">
                <a class="ui-tabs-anchor" href="./index.php?tab=erroreslistadosigad">Informe Errores</a></li>-->
        </ul>
    </div>

    <?php if ($tabActual == 'listadosigad') { ?>
        <div class="container-fluid">
            <div class="row mt-3 mb-3">
                <div class="col-12 d-flex justify-content-around align-items-center">
                    <a href="./index.php?tab=listadosigad&listadotipo=comparacion" class="btn btn-warning">Todos los datos</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=comparacion&comparar=datos" class="btn btn-warning">Avisos Cambios Email</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=comparacion&comparar=cursos" class="btn btn-info">Avisos Cambios Otros Datos</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=altas" class="btn btn-success">Altas Alumnos</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=altasCursos" class="btn btn-success">Altas Cursos</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=bajas" class="btn btn-danger">Bajas Alumnos</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=bajasCursos" class="btn btn-danger">Bajas Cursos</a>
                    <a href="./index.php?tab=listadosigad&listadotipo=modulos" class="btn btn-warning">Errores Módulos</a>
                </div>
            </div>

            <div class="row addAlert"></div>

            <?php if($_GET['listadotipo'] != 'modulos'){ ?>
                <form action="./index.php" method="GET" id="filterForm">
                    <input type="hidden" name="tab" value="<?= $_GET['tab'] ?>" />
                    <input type="hidden" name="listadotipo" value="<?= $_GET['listadotipo'] ?>" />
                    <input type="hidden" name="comparacion" value="<?= $_GET['comparacion'] ?>" />
                    <input type="hidden" name="comparar" value="<?= $_GET['comparar'] ?>" />

                    <div class="row">
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?= $_GET['email'] ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="username">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Usuario" value="<?= $_GET['username'] ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="firstname">Nombre</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Nombre" value="<?= $_GET['firstname'] ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="lastname">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Apellidos" value="<?= $_GET['lastname'] ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="dni_nif">DNI/NIE</label>
                                <input type="text" class="form-control" id="dni_nif" name="dni_nif" placeholder="DNI/NIE" value="<?= $_GET['dni_nif'] ?>">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="city">Ciudad</label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="Ciudad" value="<?= $_GET['city'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php if($_GET['listadotipo'] == 'comparacion' && $_GET['comparar'] == 'datos'){ ?>
                        <!--<div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="cambios_emails">Cambios de Email</label>
                                <select name="cambios_emails" id="cambios_emails">
                                    <option value="">Todos</option>
                                    <option value="S" <?php if($_GET['cambios_emails'] == 'S') echo 'selected' ?>>Con Cambios</option>
                                    <option value="N" <?php if($_GET['cambios_emails'] == 'N') echo 'selected' ?>>Sin Cambios</option>
                                </select>
                            </div>
                        </div>-->

                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="export_altas_bajas">Exportados Altas / Bajas</label>
                                <select name="export_altas_bajas" id="export_altas_bajas">
                                    <option value="">Todos</option>
                                    <option value="1" <?php if($_GET['export_altas_bajas'] == '1') echo 'selected' ?>>Si</option>
                                    <option value="0" <?php if($_GET['export_altas_bajas'] == '0') echo 'selected' ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="usuarios_registrados">Alumnos registrados</label>
                                <select name="usuarios_registrados" id="usuarios_registrados">
                                    <option value="">Todos</option>
                                    <option value="S" <?php if($_GET['usuarios_registrados'] == 'S') echo 'selected' ?>>Solo Alumnos Registrados</option>
                                    <option value="N" <?php if($_GET['usuarios_registrados'] == 'N') echo 'selected' ?>>Alumnos Nuevos</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="error_emails_dominio">Errores Dominio Email</label>
                                <select name="error_emails_dominio" id="error_emails_dominio">
                                    <option value="">Todos</option>
                                    <option value="S" <?php if($_GET['error_emails_dominio'] == 'S') echo 'selected' ?>>Con Errores</option>
                                    <option value="N" <?php if($_GET['error_emails_dominio'] == 'N') echo 'selected' ?>>Sin Errores</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="usuarios_duplicados">Ver Duplicados (Email)</label>
                                <select name="usuarios_duplicados" id="usuarios_duplicados">
                                    <option value="">Todos</option>
                                    <option value="S" <?php if($_GET['usuarios_duplicados'] == 'S') echo 'selected' ?>>Duplicados</option>
                                    <option value="N" <?php if($_GET['usuarios_duplicados'] == 'N') echo 'selected' ?>>Sin Duplicados</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="no_emails">Emails vacíos</label>
                                <select name="no_emails" id="no_emails">
                                    <option value="">Todos</option>
                                    <option value="S" <?php if($_GET['no_emails'] == 'S') echo 'selected' ?>>Vacíos</option>
                                    <option value="N" <?php if($_GET['no_emails'] == 'N') echo 'selected' ?>>No vacíos</option>
                                </select>
                            </div>
                        </div>
                        <?php if($_GET['listadotipo'] == 'bajas'){ ?>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group">
                                <label for="usuarios_suspendidos">Usuarios Cuenta Suspendida</label>
                                <select name="usuarios_suspendidos" id="usuarios_suspendidos">
                                    <option value="">Todos</option>
                                    <option value="S" <?php if($_GET['usuarios_suspendidos'] == 'S') echo 'selected' ?>>Si</option>
                                    <option value="N" <?php if($_GET['usuarios_suspendidos'] == 'N') echo 'selected' ?>>No</option>
                                    <option value="NULL" <?php if($_GET['usuarios_suspendidos'] == 'NULL') echo 'selected' ?>>No Existe Usuario</option>
                                </select>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success">FILTRAR</button>
                        </div>
                    </div>
                </form>
            <?php } ?>

            <div class="row">
                <?php
                    if(!empty($explanation)) echo '<div class="col-12"><strong>'.$explanation.'</strong></div>';

                    if(stripos($_GET['listadotipo'], 'altas') !== false || stripos($_GET['listadotipo'], 'bajas') !== false) {
                        echo '<div class="col-12 text-center">
                            <button class="btn btn-primary" id="gotoAltas" href="javascript:void(0)" data-href="'.new moodle_url('/admin/tool/uploaduser/index.php').'">
                                Importar Alumnos > '.strtoupper($_GET['listadotipo']).'
                            </button>
                        </div>';
                    }

                    echo '<div class="col-12">';
                    $table->out(20, true);
                    echo '</div>';
                ?>
            </div>
        </div>
    <?php } elseif ($tabActual == 'importarsigad') { ?>

        <link href="../libreria/uploader/css/jquery.dm-uploader.min.css" rel="stylesheet">

        <div class="container">
            <?php /* RECUERDA MODIFICAR gestion_alumnos.js añadiendo el nombre del archivo excel
            <!--BOTON MANUAL PARA PRUEBAS DE PROCESO-->
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <button class="btn btn-success" id="processAjax">Haz Clic Para Procesar Manualmente</button>
                </div>
            </div> */ ?>

            <div class="row">
                <div class="col-12 d-flex justify-content-center align-items-center">
                    <div id="loadingFiles" class="text-center d-none">
                        <p>En este momento se están tratando los datos. Por favor espere...<p>
                        <img src="./images/loading-files.gif" alt="Tratando Archivos" width="64" />
                    </div>
                    <div id="filesLoadedOK" class="text-center d-none">
                        <h3 class="text-success">Se han tratado todos los datos correctamente :)<h3>
                        <img src="./images/check-circle.gif" alt="Tratando Archivos" width="64" />
                    </div>
                </div>
            </div>

            <div class="row" id="uploadFiles">
                <!-- /uploader -->
                <div class="col-md-6 col-sm-12">
                    <!-- Our markup, the important part here! -->
                    <div id="drag-and-drop-zone" class="dm-uploader p-5">
                        <h3 class="mb-5 mt-5 text-muted">Arrastra un archivo hacia este cuadro</h3>

                        <div class="btn btn-primary btn-block mb-5">
                            <span>Abrir Explorador</span>
                            <input type="file" title='Clic para añadir archivos' />
                        </div>
                    </div>
                </div>

                <!-- /file list -->
                <div class="col-md-6 col-sm-12">
                    <div class="card h-100">
                        <div class="card-header">Archivos</div>

                        <ul class="list-unstyled p-2 d-flex flex-column col" id="files">
                            <li class="text-muted text-center empty">No se ha subido ningún archivo</li>
                        </ul>
                    </div>
                </div>
				
				<!-- /debug -->
				<div class="row">
					<div class="col-12">
						<div class="card h-100">
						<div class="card-header">Debug Messages</div>
						<ul class="list-group list-group-flush" id="debug">
							<li class="list-group-item text-muted empty">Loading plugin....</li>
						</ul>
						</div>
					</div>
				</div> 
            </div>
        </div>

        <!-- File item template -->
        <script type="text/html" id="files-template">
            <li class="media">
                <div class="media-body mb-1">
                    <p class="mb-2">
                        <strong>%%filename%%</strong> - Estado: <span class="text-muted">Esperando</span>
                    </p>
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <hr class="mt-1 mb-1" />
                </div>
            </li>
        </script>

        <!-- Debug item template -->
        <script type="text/html" id="debug-template">
            <li class="list-group-item text-%%color%%"><strong>%%date%%</strong>: %%message%%</li>
        </script>

    <?php } ?>

    <?php // MODAL DE ALUMNO PARA COMPARAR ?>
    <div id="studentModal" class="jqueryModal">
        <div class="jqueryModal-body container">
            
            <div class="row" id="alumnoCompararCambiosOk">
                <div class="col-12 alert alert-success">
                    El alumno ha sido exportado a los listados de altas y bajas de cursos correctamente
                </div>
            </div>
            <div class="row">
                <div class="col-6 text-center pt-0 pb-0"><h4>Alumno Actual</h4></div>
                <div class="col-6 text-center pt-0 pb-0"><h4>Alumno Importado</h4></div>
            </div>
            <div class="row">
                <div id="alumnoActual" class="col-6"></div>
                <div id="alumnoComparar" class="col-6"></div>
            </div>
            <?php if(!empty($_GET['comparar']) && $_GET['comparar'] == 'datos') { ?>
                <div class="row" id="alumnoCompararAnadirCambios">
                    <div class="col-12 text-center">
                        <button type="button" id="anadirCambiosAltasBajas" class="btn btn-success text-uppercase">
                            Añadir cambios a los listados de altas y bajas de cursos
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php // MODAL ERRORES ?>
    <div id="errorModal" class="jqueryModal">
        <div class="jqueryModal-body container">
            <div class="row message">
            </div>
        </div>
    </div>

    <?php // SCRIPTS NECSARIOS PARA QUE TODO EL INTERFAZ FUNCIONE ?>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
    <script src="<?= new moodle_url('/itainnova_tool/libreria/uploader/js/jquery.dm-uploader.min.js') ?>"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<?php

    echo $OUTPUT->footer();

} else {
	// REALIZA LA EXPORTACIÓN
	if (isset($table) && $table->is_downloading()) $table->out(15, true);
}
?>