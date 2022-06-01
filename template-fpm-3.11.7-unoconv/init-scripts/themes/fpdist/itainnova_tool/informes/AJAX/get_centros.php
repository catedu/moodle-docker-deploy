<?php

date_default_timezone_set('Europe/Madrid');

// Configuración del Moodle
require_once('../../../config.php');

header('Content-type:application/json;charset=utf-8');

// Ciclos del centro
$sqlciclos = "SELECT * FROM {course_categories} WHERE parent = :centro";
$ciclos = array_values($DB->get_records_sql($sqlciclos, array('centro' => $_REQUEST['idCentro'])));

if(empty($ciclos)){
    echo json_encode(array('error' => 'No existen ciclos para este centro: '.$_REQUEST['idCentro']));
    exit();
}

// Variable con la fecha del pasado septiembre
$last_september = (date("m") > 8) ? gmmktime(0, 0, 0, 9, 1, date("y")) : gmmktime(0, 0, 0, 9, 1, date("y") - 1);

//////////////////////////////////////////////////
// CONSULTAS SQL QUE SE EJECUTARÁN POSTERIORMENTE
// Las consultas luego se ejecutarán utilizando la sustitución de parámetros
//////////////////////////////////////////////////

// SQL PROFESORES POR CENTRO
$sql_profesores_por_centro = "SELECT role_assignments.id,course.id AS courseid, user.id AS userid, 
    CONCAT(user.firstname,' ',user.lastname) AS name, 
    course.fullname, course.shortname, course.id AS curso
    FROM {user} user
    JOIN {role_assignments} role_assignments ON user.id = role_assignments.userid
    JOIN {context} context ON role_assignments.contextid = context.id
    JOIN {course} course ON course.id = context.instanceid
    JOIN {user_enrolments} user_enrolments on user_enrolments.userid = user.id
    JOIN {enrol} enrol ON enrol.id = user_enrolments.enrolid
    WHERE context.contextlevel = 50
    AND enrol.status = 0
    AND role_assignments.roleid = 3
    AND course.shortname not like 'OLD%'
    AND user.id != '9835'
    AND course.category = :coursecategory
    GROUP BY role_assignments.id, user.id, course.id, course.category";

// SQL NÚMERO DE DÍAS QUE SE HA CONECTADO
$sqlconexiones = "SELECT DISTINCT(from_unixtime(timecreated,'%Y-%m-%d')) AS dia
	FROM {logstore_standard_log}
	WHERE userid = :userid
	AND action = :action
    AND timecreated >= :time";

// SQL NÚMERO DE CONEXIONES POR CURSO
$sqlconexionescurso = "SELECT DISTINCT(from_unixtime(timecreated,'%Y-%m-%d')) AS dia
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND timecreated >= :time";

// SQL NÚMERO TOTAL DE CONEXIONES
$sqltotalconexiones = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE action = :action
    AND userid = :userid
    AND timecreated >= :time ";

// SQL NÚMERO TOTAL DE CLICKS POR DÍA
$sql_clicks_dia = "SELECT week(from_unixtime(timecreated),1) AS date,count(action) AS clicks
    FROM {logstore_standard_log} log
    WHERE userid = :userid
    AND courseid = :courseid
    AND timecreated >= :time
    GROUP BY date";

// SQL REGISTROS
$sqlregistros = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND timecreated >= :time ";

// SQL MENSAJES ENVIADOS
$sqlmensajes = "SELECT count(id)
    FROM {message}
    WHERE useridfrom = :userid
    AND timecreated >= :time";

// SQL MENSAJES RECIBIDOS
$sqlmensajesrecibidos = "SELECT count(id)
    FROM {message}
    WHERE useridto = :userid
    AND timecreated >= :time ";

// SQL CORREOS PLATAFORMA
$sqllocalmail = "SELECT count(local_mail_messages.id)
    FROM {local_mail_messages} local_mail_messages
    JOIN {local_mail_message_users} local_mail_message_users ON local_mail_messages.id = local_mail_message_users.messageid
    WHERE role = 'from'
    AND local_mail_message_users.userid = :userid
    AND local_mail_messages.courseid = :courseid
    AND time >= :time ";

// SQL MENSAJES FOROS
$sqlposts = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND component = 'mod_forum'
    AND action = 'created'
    AND ( target = 'post' OR target = 'discussion' )
    AND timecreated >= :time ";

// SQL ACTUALIZACIONES DE RECURSOS
$sqlactualizados = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND action = 'updated'
    AND component = 'core'
    AND target = 'course_module'
    AND timecreated >= :time";

// SQL SUBIDA DE ARCHIVOS
$sqlnuevos = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND component = 'core'
    AND action = 'created'
    AND target = 'course_module'
    AND timecreated >= :time";

$coursesData = [];
foreach($ciclos as $ciclo){
        
    $profesores = array_values($DB->get_records_sql($sql_profesores_por_centro, array('coursecategory' => $ciclo->id)));

    $teachersData = [];
    foreach ($profesores as $profesor) {

        $teacherData = [];
        $teacherData['profesor'] = $profesor;
        $teacherData['calendar'] = $profesor->userid . '-calendar-' . $profesor->curso;
        $teacherData['clicks'] = $profesor->userid . '-clicks-' . $profesor->curso;

        //////////////////////////////////////////////////
        // ACTIVIDAD
        $dataActividad = [];
        $actividad = $DB->get_records_sql($sql_clicks_dia, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        if(!empty($actividad)){
            foreach ($actividad AS $click) {
                $fecha = new DateTime();
                $fecha->setISODate(date('Y'), $click->date);
                $dataActividad[] = array(strftime("%d-%b", $fecha->getTimestamp()), (int) $click->clicks, '#e74c3c', (int) $click->clicks);
            }
        }else{
            $dataActividad[] = array('Sin actividad',0,'#e74c3c',0);
        }

        $teacherData['actividad'] = $dataActividad;

        //////////////////////////////////////////////////
        // CONEXIONES CURSO
        $dataConexiones = [];
        $conexiones = $DB->get_records_sql($sqlconexionescurso, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        if(!empty($conexiones)){
            foreach ($conexiones as $dia) {
                // $conexionesDate = date('d/m/Y', strtotime($dia->dia));
                // $conexionesDate = date("c",strtotime($dia->dia));

                $dia = explode("-",$dia->dia);
                $dia[2]=intval($dia[2]);
                $dia[1]-=1;

                // La fecha la enviamos dividida en array para luego crearla con Javascript
                $dataConexiones[] = ['date' => array($dia[0],$dia[1], $dia[2]), 'num' => 10 ];
            }
        }

        $teacherData['conexiones'] = $dataConexiones;
                
        //////////////////////////////////////////////////
        // TOTAL CONEXIONES
        $totalconexiones = $DB->count_records_sql($sqltotalconexiones, array('action' => 'loggedin', 'userid' => $profesor->userid, 'time' => $last_september));
        $teacherData['totalconexiones'] = $totalconexiones;

        //////////////////////////////////////////////////
        // DÍAS CONECTADO
        $dias = count($DB->get_records_sql($sqlconexiones, array('userid' => $profesor->userid, 'action' => 'loggedin', 'time' => $last_september)));
        $teacherData['dias'] = $dias;

        //////////////////////////////////////////////////
        // NÚMERO DE REGISTROS DEL CURSO
        $registros = $DB->count_records_sql($sqlregistros, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        $teacherData['registros'] = $registros;

        //////////////////////////////////////////////////
        // NÚMERO DE MENSAJES ENVIADOS A TRAVÉS DE LA PLATAFORMA
        $num_mensajes = $DB->count_records_sql($sqlmensajes, array('userid' => $profesor->userid, 'time' => $last_september));
        $teacherData['num_mensajes'] = $num_mensajes;

        //////////////////////////////////////////////////
        // NÚMERO DE MENSAJES RECIBIDOS A TRAVÉS DE LA PLATAFORMA
        $num_mensajes_recibidos = $DB->count_records_sql($sqlmensajesrecibidos, array('userid' => $profesor->userid, 'time' => $last_september));
        $teacherData['num_mensajes_recibidos'] = $num_mensajes_recibidos;

        //////////////////////////////////////////////////
        // NÚMERO DE CORREOS ENVIADOS A TRAVÉS DE LA PLATAFORMA
        $localmail = $DB->count_records_sql($sqllocalmail, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        $teacherData['localmail'] = $localmail;

        //////////////////////////////////////////////////
        // NÚMERO DE POSTS ESCRITOS EN LOS FOROS DEL CURSO
        $posts = $DB->count_records_sql($sqlposts, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        $teacherData['posts'] = $posts;

        //////////////////////////////////////////////////
        // NÚMERO DE ACTUALIZACIONES DE RECURSOS
        $actualizados = $DB->count_records_sql($sqlactualizados, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        $teacherData['actualizados'] = $actualizados;

        //////////////////////////////////////////////////
        // NÚMERO DE SUBDIA DE RECURSOS NUEVOS
        $nuevos = $DB->count_records_sql($sqlnuevos, array('userid' => $profesor->userid, 'courseid' => $profesor->courseid, 'time' => $last_september));
        $teacherData['nuevos'] = $nuevos;

        // AÑADIMOS TODOS LOS DATOS EN ÚN ÚNICO ARRAY
        $teachersData[] = $teacherData;

    } //END FOR profesores

    
    $coursesData[] = [
        'courseData' => $ciclo,
        'teachersData' => $teachersData
    ];
}

echo json_encode($coursesData);