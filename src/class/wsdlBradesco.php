<?php
   class WebServiceBradesco {
      public $certPfx;
      public $certPass;
      public $wsdl;
      private $jsonStr;
      
      function __construct($jsonPayload){
         $this->jsonStr = $jsonPayload;
      }

      function set_certPfx($certPfx) {
         $this->certPfx = $certPfx;
       }

       function set_certPass($certPass) {
         $this->certPass = $certPass;
       }

       function set_wsdl($wsdl){
         $this->wsdl = $wsdl;
       }

      public function requestAPIBradesco() {
         try {   

            $certificado_pfx = file_get_contents($this->certPfx);
            if (!openssl_pkcs12_read($certificado_pfx, $result, $this->certPass)) {
               throw new Exception('Não foi possível ler o certificado .pfx');
            }
            
            // Caminho absoluto para salvar o arquivo
            $path = sprintf('%s%sdata%stmp%s', realpath('.'), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
            if (@mkdir($path, 0777, true) && !is_dir($path)) {
               throw new \Exception('Falha ao criar diretório de assinatura no PHP, entre em contato com o administrador');
            }
            
            $jsonFile   = $path . uniqid('jsonFile', true);
            $signedFile = $path . uniqid('signedFile', true);
            
            // Exclui os arquivos temporários após a execução do script
            register_shutdown_function(function () use ($jsonFile, $signedFile) {
               file_exists($jsonFile) && unlink($jsonFile);
               file_exists($signedFile) && unlink($signedFile);
            });	

            file_put_contents($jsonFile, $this->jsonStr);
            $certKey = openssl_x509_read($result['cert']);
            $privKey = openssl_pkey_get_private($result['pkey'], $this->certPass);
            openssl_pkcs7_sign($jsonFile, $signedFile, $certKey, $privKey, [], PKCS7_BINARY | PKCS7_TEXT);
            
            $signature = file_get_contents($signedFile);
            $parts = preg_split("#\n\s*\n#Uis", $signature);
            $mensagem_assinada_base64 = $parts[1];
            $ch = curl_init($this->wsdl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $mensagem_assinada_base64);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $retorno = curl_exec($ch);
            if (curl_errno($ch)) {
               $info = curl_getinfo($ch);
               throw new Exception('Não foi possível registrar o boleto. ' . 'Erro:' . curl_errno($ch) . '.<br>' . $info);
            }
  
            //Payload retornado
            $doc = new DOMDocument();
            $doc->loadXML($retorno);
            $retorno = $doc->getElementsByTagName('return')->item(0)->nodeValue;
            $retorno = preg_replace('/, }/i', '}', $retorno); 
            $retorno = json_decode($retorno);         
            if (!empty($retorno->cdErro)) {
               throw new Exception('Não foi possível registrar o boleto. ' . $retorno->msgErro);
            }   

            return $retorno;    
         } catch (Exception $e) {
            throw new Exception($e->getMessage());
         }    
      }  
   }
?>