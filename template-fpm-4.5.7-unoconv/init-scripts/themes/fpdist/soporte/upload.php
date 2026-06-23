<?php
    session_start();

    require_once(__DIR__ . '/../config.php');
    require_once('secret.php');
    
    /** ************************************ **/
    $logFile = fopen("log.txt", 'a');
    fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: -------------------------------------------------------------------------");
    // Store all errors
    $errors = [];

    // Available file extensions
    $fileExtensions = ['jpeg','jpg','png','gif'];

   if(!empty($_FILES['fileAjax'] ?? null)) {
        // Recojo form 
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: Recojo form");
        $fileName = $_FILES['fileAjax']['name'];
        $fileTmpName  = $_FILES['fileAjax']['tmp_name'];
        $fileType = $_FILES['fileAjax']['type'];
        $fileExtension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: fileName: '" . $fileName . "'");
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: fileTmpName: '" . $fileTmpName . "'");
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: fileType: '" . $fileType . "'");
        fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: fileExtension: '" . $fileExtension . "'");

        // Creo el fichero que enviaré al servidor de redmine
        $file = fopen($fileTmpName, 'r');
        $size = filesize($fileTmpName);
        $filedata = fread($file, $size);
        //
        if (isset($fileName)) {
            // Compruebo extensión del fichero subida es válido
            if (! in_array($fileExtension,$fileExtensions)) {
                $errors[] = "Las extensiones JPEG, JPG, PNG y GIF son las únicas permitidas";
            }
            // 
            if (empty($errors)) {
                // Hago lo relativo a redmine
                //fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: 1");
                
                //$token = upload_file_to_redmine($fileTmpName, $fileName, $apiRedmine, $logfile);

                $url = "https://soportearagon.catedu.es/uploads.json?filename=" . $fileTmpName;
                fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: url: '" . $url . "'");
                //fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: 2");

                $curl = curl_init();
                //fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: 3");
                // Cabeceras
                curl_setopt($curl, CURLOPT_HTTPHEADER, 
                    array(
                            'Content-Type: application/octet-stream',
                            'X-Redmine-API-Key: ' . $apiRedmine
                    )
                );
                //fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: 4");
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $filedata );
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                //fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: 5");

                $result = curl_exec($curl)  ;
                fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: result: '" . $result . "'");

                $respuesta = json_decode($result, true);
                fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: respuesta: '" . $respuesta . "'");

                $token = $respuesta["upload"]["token"];
                fwrite($logFile, "\n" . date("d/m/Y H:i:s") . " upload.php: token: '" . $token . "'");

                curl_close($curl);
                
                echo $token;
            }
        }
    }

?>