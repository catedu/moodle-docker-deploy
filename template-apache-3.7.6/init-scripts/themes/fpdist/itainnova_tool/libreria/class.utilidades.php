<?php
	function getRandomCode(){
		//$an = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-)(.:,;";
		$an = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$su = strlen($an) - 1;
		return substr($an, rand(0, $su), 1).substr($an, rand(0, $su), 1).substr($an, rand(0, $su), 1).substr($an, rand(0, $su), 1).substr($an, rand(0, $su), 1).substr($an, rand(0, $su), 1);
	}
	
	function getRealIP(){
		if (isset($_SERVER["HTTP_CLIENT_IP"])){
			return $_SERVER["HTTP_CLIENT_IP"];
		}elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif (isset($_SERVER["HTTP_X_FORWARDED"])){
			return $_SERVER["HTTP_X_FORWARDED"];
		}elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){
			return $_SERVER["HTTP_FORWARDED_FOR"];
		}elseif (isset($_SERVER["HTTP_FORWARDED"])){
			return $_SERVER["HTTP_FORWARDED"];
		}else{
			return $_SERVER["REMOTE_ADDR"];
		}
	}
	
	function lanzarcorreo($subject,$email,$body){
		require("class.phpmailer.php");

		$mail = new PHPMailer();
												
		$mail->IsSMTP(); 
		$mail->Host = "smtp.aragon.es";
		$mail->SMTPSecure = "tls";
		$mail->SMTPAuth = true;
		// credenciales usuario
				include "./secret/crenciales.php";
		// correo
		$mail->Subject = $subject;
		$mail->AddAddress($email);

		$mail->Body = $body;
		$mail->IsHTML(true);
		$mail->Send();
	}
?>logía demuestra que no es verdad".Desde el hospital denuncian que las personas que han mentido aparecen co