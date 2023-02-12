<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 300000");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once '../class/wsdlBradesco.php';
    include_once '../class/dotEnv.php';

    try {  

        //Configurações de ambiente
        $dotEnv = new DotEnv(dirname(__DIR__ . '1') . '/config/.env') ;
        $dotEnv->load();

        //Basic Authorization
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])
        ) {
            http_response_code(401);  
            echo json_encode(array('codigo' => 401, "mensagem" => 'Sem autorização',  "sucesso" => false));
            exit;
        }
        if (!(($_SERVER['PHP_AUTH_USER'] == getenv('AUTH_USER')) && ($_SERVER['PHP_AUTH_PW'] == getenv('AUTH_PW')))) {
            http_response_code(401);  
            echo json_encode(array('codigo' => 401, "mensagem" => 'Sem autorização',  "sucesso" => false));
            exit;  
        } 

        //POST Payload
        $data        = file_get_contents("php://input");
        $payloadJson = $data;

        //Ambiente Bradesco
        $environment = isset($_GET['ENVIRONMENT']) ? $_GET['ENVIRONMENT'] : 'DEVELOPMENT';

        //Classe - WebService Bradesco
        $wbsBradesco = new WebServiceBradesco($payloadJson);
        $environment == 'PRODUCTION' ? $wbsBradesco->set_wsdl(getenv('WSDL_PROD')) : $wbsBradesco->set_wsdl(getenv('WSDL_HOMOL'));
        $wbsBradesco->set_certPfx(dirname(__DIR__ . '1') . getenv('CERT_PATH'));
        $wbsBradesco->set_certPass(getenv('CERT_PASS'));
        $respBradesco = $wbsBradesco->requestAPIBradesco(); 

        //Cobrança gerada
        http_response_code(200);         
        echo json_encode(array('codigo' => -1, "mensagem" => "Cobrança gerada com sucesso", "sucesso" => true, "resposta" => $respBradesco));
    }
    //Retornar exceção
    catch (Exception $e) {
        http_response_code(400);  
        echo json_encode(array(  
                "codigo" => $e->getCode(),
                "mensagem" => $e->getMessage(),
                "sucesso" => false
        ));
    }

?>
