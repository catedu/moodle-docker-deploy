<?php
    session_start();

    require_once(__DIR__ . '/../config.php');
    require_once('secret.php');
    
    /** ************************************ **/

    // Store all errors
    $errors = [];

    // Available file extensions
    $fileExtensions = ['jpeg','jpg','png','gif'];

   if(!empty($_FILES['fileAjax'] ?? null)) {
        // Recojo form 
        $fileName = $_FILES['fileAjax']['name'];
        $fileTmpName  = $_FILES['fileAjax']['tmp_name'];
        $fileType = $_FILES['fileAjax']['type'];
        $fileExtension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

        // Creo el fichero que enviaré al servidor de redmine
        $file = fopen($fileTmpName, 'r');
        $size = filesize($fileTmpName);
        $filedata = fread($file, $size);
        //
        if (isset($fileName)) {
            // Compruebo extensión del fichero subida es válido
            if (! in_array($fileExtension,$fileExtensions)) {
                $errors[] = "Las extensiones JPEG, JPG, PNG and GIF son las únicas permitidas";
            }
            // 
            if (empty($errors)) {
                // Hago lo relativo a redmine
                fwrite($logfile, "No hay errores\n");
                
                //$token = upload_file_to_redmine($fileTmpName, $fileName, $apiRedmine, $logfile);

                $url = "https://soportearagon.catedu.es/uploads.json?filename=" . $fileName;
                $curl = curl_init();
                // Cabeceras
                curl_setopt($curl, CURLOPT_HTTPHEADER, 
                    array(
                            'Content-Type: application/octet-stream',
                            'X-Redmine-API-Key: ' . $apiRedmine
                    )
                );
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $filedata );
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                
                $result = curl_exec($curl)  ;

                $respuesta = json_decode($result, true);
                $token = $respuesta["upload"]["token"];

                curl_close($curl);
                
                echo $token;
            } 
        }
    }

?>