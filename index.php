<?php

    require_once("./vendor/autoload.php");
    require('helpers/function_email.php');
    require('request/validated.php');
    require('helpers/function_document.php');
    $path_raiz = dirname(__DIR__, 1);
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $response = [
        'status' => true,
        'message' => ''
    ];


    try {

        /*var_dump($GLOBALS);
        $request = json_decode(file_get_contents("php://input"), true);
        print_r($request);*/

        $validated = validaciones( $_POST );
        if( !$validated['status'] ){
            throw new Exception($validated['message']);
        }
    

        $attached = [];

    
        if( isset($_FILES['attached']) && count($_FILES['attached']) > 0 ){
            for ( $i = 0; $i < count($_FILES['attached']['name']); $i++ ) { 
                $ext = pathinfo($_FILES['attached']['name'][$i], PATHINFO_EXTENSION);
                $uploadfile = tempnam(sys_get_temp_dir(), hash('sha256', $_FILES['attached']['name'][$i])) . '.' . $ext;

                if ( !move_uploaded_file($_FILES['attached']['tmp_name'][$i], $uploadfile) ) {
                    throw new Exception("No se pudo leer el archivo ".$_FILES['attached']['name'][$i]);
                }
                
                $data_file['file']      = $uploadfile;
                $data_file['filename']  = $_FILES['attached']['name'][$i];
                $data_file['base']      = "base64";
                $data_file['tipo']      = "application/pdf";
                array_push($attached, $data_file);
            }
        }


        $attachedString = [];
        if( isset($_POST['attachedString']) && !empty($_POST['attachedString']) ){
            $attachedString = $_POST['attachedString'];
        }

        $addReplyTo = [];
        if( isset($_POST['addReplyTo']) && count($_POST['addReplyTo']) > 0 ){
            $addReplyTo = $_POST['addReplyTo'];
        }

        $addCC = [];
        if( isset($_POST['addCC']) && count($_POST['addCC']) > 0 ){
            $addCC = $_POST['addCC'];
        }

        $addBCC = [];
        if( isset($_POST['addBCC']) && count($_POST['addBCC']) > 0 ){
            $addBCC = $_POST['addBCC'];
        }
    

        $response = sendEmailDefault($_POST['username'], $_POST['password'], $_POST['company'], $_POST['mailTo'], $_POST['subject'], $_POST['message'], $attached, $attachedString, $addReplyTo, $addCC, $addBCC );

    } catch (Exception $e) {
        $response['status'] = false;
        $response['message'] = $e->getMessage();
    }

    echo json_encode( $response );
?>