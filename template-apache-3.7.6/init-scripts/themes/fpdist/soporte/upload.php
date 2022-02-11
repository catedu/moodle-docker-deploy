<?php
    session_start();

    require_once(__DIR__ . '/../config.php');
    require_once('secret.php');
    /** ************************************** **/


    /** ************************************** **/

    $logfile = fopen("log", "w");
    fwrite($logfile, date('Y-m-d H:i:s') . "\n");
    
    /** ************************************ **/
    // https://uploadcare.com/blog/file-upload-ajax/
    $currentDir = getcwd();
    $uploadDirectory = "uploads/";

    // Store all errors
    $errors = [];

    // Available file extensions
    $fileExtensions = ['jpeg','jpg','png','gif'];

   if(!empty($_FILES['fileAjax'] ?? null)) {
        $fileName = $_FILES['fileAjax']['name'];
        $fileTmpName  = $_FILES['fileAjax']['tmp_name'];
        $fileType = $_FILES['fileAjax']['type'];
        $fileExtension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
        //
        fwrite($logfile, "fileName: " . $fileName . "\n");
        fwrite($logfile, "fileTmpName: " . $fileTmpName . "\n");
        fwrite($logfile, "fileType: " . $fileType . "\n");
        fwrite($logfile, "fileExtension: " . $fileExtension . "\n");
        //
        if (isset($fileName)) {
            // Compruebo extensión del fichero subida es válido
            if (! in_array($fileExtension,$fileExtensions)) {
                $errors[] = "JPEG, JPG, PNG and GIF images are only supported";
            }
            // 
            if (empty($errors)) {
                // Hago lo relativo a redmine
                fwrite($logfile, "No hay errores\n");
                fwrite($logfile, "apiRedmine antes de llamar a funcion: $apiRedmine.\n");

                $token = upload_file_to_redmine($fileTmpName, $fileName, $apiRedmine, $logfile);

                fwrite($logfile, "token: " . $token . "\n");
                echo "respuesta desde upload.php token: " . $token;
            } else {
                foreach ($errors as $error) {
                    fwrite($logfile, "The following error occured: " . $error . "\n");
                }
            }
        }
    }

    
    
    
    // https://www.redmine.org/boards/2/topics/40563
    function upload_file_to_redmine($fileTmpName, $fileName, $apiRedmine, $logfile) {
        fwrite($logfile, "upload_file_to_redmine(fileTmpName: " . $fileTmpName . ",...)\n");

        $urlOrIp = 'soportearagon.catedu.es';
        // Abro la conexión
        $fp = fsockopen($urlOrIp, "80", $errno, $errstr, 30);
        
        if ($fp) {
            fwrite($logfile, "Conexión establecida\n");
            // Creo la cabecera para la conexión
            $out = "POST /uploads.json?filename=$fileName HTTP/1.1\r\n";
            $out .= "Host: $urlOrIp\r\n";
            $out .= "Content-Type: application/octet-stream\r\n";
            $out .= "X-Redmine-API-Key: $apiRedmine\r\n";
            $out .= 'Content-Length: ' . strlen(base64_encode(file_get_contents($fileTmpName))) . "\r\n\r\n";
            $out .= base64_encode(file_get_contents($fileTmpName));
            //Envío a través de la conexión la cabecera y los datos
            fwrite($fp, $out);            
            //fwrite($fp, base64_encode(file_get_contents($fileTmpName)) );
            // Log mío
            fwrite($logfile, "out:\n". $out . "\n");
            fwrite($logfile, "fileTmpName:\n". $fileTmpName . "\n");
            fwrite($logfile, "file_get_contents(fileTmpName):\n". file_get_contents($fileTmpName)."\n");
            // Obtengo la respuesta
            $response = '';
            while (!feof($fp)) {
                $response .= fgets($fp, 128);
            }

            fwrite($logfile, "\nRespuesta:\n" . $response . "\n");
            // Cierro la conexión
            fclose($fp);
            // Busco el token dentro de la respuesta y lo devuelvo
            $o = array();
            if (preg_match_all('/\{"upload":\{"token":"(.+)"\}\}/ms', $response, $o)) {
                return $o[1][0];
            }
        }else{
            fwrite($logfile, "No se pudo conectar. $errstr: ($errno)\n");
        }
        fwrite($logfile, "\nDevuelvo falso \n");
        return false;

    }
    // Leer:
    // https://stackoverflow.com/questions/19471089/how-can-i-upload-files-to-redmine-via-activeresource-rest-api
    

    /** ************************************** **/
    fclose($logfile);

?>