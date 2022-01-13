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
include "./secret/crenciales.php";
$mail->IsHTML(true);
