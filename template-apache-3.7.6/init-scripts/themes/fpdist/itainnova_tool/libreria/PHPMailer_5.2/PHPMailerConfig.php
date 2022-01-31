<?php
/**
* CONFIGURACIÓN DE DATOS E INSTANCIACIÓN PARA ENVÍO DE CORREOS
*/

$mail = new PHPMailer();

$mail->IsSMTP();
$mail->Host = "smtp.aragon.es";
$mail->SMTPSecure = "tls";
$mail->SMTPAuth = true;

// Credenciales usuario
$mail->Username = "admin.moodlefpdistancia@aragon.es";
$mail->Password = "xxx";
$mail->From = "admin.moodlefpdistancia@aragon.es";
$mail->FromName = "Administrador de Plataforma FP";
$mail->IsHTML(true);
