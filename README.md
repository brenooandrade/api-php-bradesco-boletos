# PHP API - Bradesco boleto online
![Badge](https://img.shields.io/badge/PHP-API+Bradesco%339933?style=for-the-badge&logo=ghost)
--------------
Simples API escrita em PHP para registro de cobranças (boletos) utilizando o webservice online do Bradesco.

## Informações:
O WebService do Bradesco requer que o payload (JSON) enviado esteja assinado através do certificado digital da empresa titular da cobrança, utilizando o padrão PKCS#7 para assinatura do arquivo .JSON.

## Objetivo:
Essa API tem como objetivo receber o payload conforme padrão da documentação (**Manual_Técnico_Layout_Registro_OnLine.pdf**), realizar a assinatura junto ao certificado digital da conta titular no padrão pkcs#7 e requisitar o registro da cobrança (boleto) no webservice online do Bradesco, retornando ao cliente a resposta do banco.

## Como usar:

### 1. Instalação / Setup
#### 1.1 Instalação de apache/PHP e módulos via Ubuntu 20.04
🐧 O projeto em questão foi homologado utilizando o Ubuntu versão 20.04 + Apache 2.4 + PHP 7.4, portanto se você deseja rodar a API direto de uma instalação Ubuntu, será preciso realizar a instalação dos pacotes necessários:
```sh
sudo apt-get update
sudo apt-get install apache2
```
🐧 Será necessário também instalar o php e os módulos utilizados para a API são: php-curl e php-xml
```sh
sudo apt install php libapache2-mod-php
sudo add-apt-repository ppa:ondrej/php
sudo apt-get install php7.4-curl
sudo apt-get install php7.4-xml
sudo systemctl restart apache2
```

#### 1.1 Instalação via Docker
Você também pode optar por subir a API via Docker, disponilizei um arquivo Dockerfile para que a imagem possa ser criada, lembre-se que é importante antes de realizar o build [colocar o arquivo do certificado]([here](#place-2)) e [configurar as variáveis de ambiente corretamente]([here](#place-3))
```sh
docker build -t api-php-bradesco-boletos .
docker run -p 80:80 api-php-bradesco-boletos
```

### 2. Configuração de certificado digital / senha do certificado da empresa titular
⚠️ Como explicado, é premissa que o payload request (JSON) seja assinado em PKCS#7 com o certificado digital (A1) da empresa titular, portanto será necessário copia-lo para o dirtório ./config/certificado.pfx no exemplo em questão utilizei um certificado fake que é inválido, **substitua pelo certificado digital da empresa titular**.

### 3. Configuração de parâmetros para funcionamento correto da API para
🛑 No arquivo ./config/.env estão os parâmetros necessários para funcionamento da API, configure de acordo com o seu cenário:
```sh
AUTH_USER=empresa -> "Usuário para Basic Authorization da API"
AUTH_PW=S3nh4S3cre3T4 -> "Senha para Basic Authorization da API"
WSDL_PROD=https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulo -> "URI produção Bradesco"
WSDL_HOMOL=https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulohomologacao -> "URI homologação Bradesco"
CERT_PATH=/config/certificado_fake.pfx -> "Path do certificado digital (A1) da empresa titular"
CERT_PASS=123456 -> "Senha do certificado digital (A1) da empresa titular"
```

### 4. Authorization
🔑 A API possui autenticação básica e para isso ao enviar a requisição do registro da cobrança é preciso enviar um Header na requisição contendo as credenciais, como em qualquer outra autenticação básica, será preciso converter as credenciais da seguinte forma: Authorization Basic empresa:S3nh4S3cre3T4 (criptografida em base64)
|Header Name|Header Value|
|-------------|------------|
|Authorization|Basic ZW1wcmVzYTpTM25oNFMzY3JlM1Q0|
|Content-Type|application/json|

### 6. Request - Cobrança
⚡ Para realizar a solicitação de cobrança é preciso utilizar os seguintes parâmetros:

**Método:** POST

**URI:** http://127.0.0.1/api/reqBradesco.php/?ENVIRONMENT=HOMOLOG

**Payload:**
```sh
   {
      "nuCPFCNPJ": "2000001",
      "filialCPFCNPJ": "0001",
      "ctrlCPFCNPJ": "20",
      "cdTipoAcesso": "2",
      "clubBanco": "2000651",
      "cdTipoContrato": "48",
      "nuSequenciaContrato": "0",
      "idProduto": "09",
      "nuNegociacao": "051000000000400000",
      "cdBanco": "237",
      "eNuSequenciaContrato": "0",
      "tpRegistro": "1",
      "cdProduto": "0",
      "nuCliente": "E00000009",
      "dtEmissaoTitulo": "11.02.2022",
      "dtVencimentoTitulo": "12.03.2022",
      "tpVencimento": "0",
      "vlNominalTitulo": "41218",
      "cdEspecieTitulo": "02",
      "tpProtestoAutomaticoNegativacao": "0",
      "prazoProtestoAutomaticoNegativacao": "0",
      "controleParticipante": "",
      "cdPagamentoParcial": "",
      "qtdePagamentoParcial": "0",
      "percentualJuros": "00040000",
      "vlJuros": "0",
      "qtdeDiasJuros": "2",
      "percentualMulta": "00200000",
      "vlMulta": "0",
      "qtdeDiasMulta": "2",
      "percentualDesconto1": "0",
      "vlDesconto1": "0",
      "dataLimiteDesconto1": "",
      "percentualDesconto2": "0",
      "vlDesconto2": "0",
      "dataLimiteDesconto2": "",
      "percentualDesconto3": "0",
      "vlDesconto3": "0",
      "dataLimiteDesconto3": "",
      "prazoBonificacao": "0",
      "percentualBonificacao": "0",
      "vlBonificacao": "0",
      "dtLimiteBonificacao": "",
      "vlAbatimento": "0",
      "vlIOF": "0",
      "nomePagador": "BRENO NOVAIS ANDRADE",
      "logradouroPagador": "Rua qualquer",
      "nuLogradouroPagador": "150",
      "cepPagador": "37000",
      "complementoCepPagador": "000",
      "bairroPagador": "Qualquer bairro",
      "municipioPagador": "BELO HORIZONTE",
      "ufPagador": "MG",
      "cdIndCpfcnpjPagador": "1",
      "nuCpfcnpjPagador": "00000000000000",
      "endEletronicoPagador": "",
      "nomeSacadorAvalista": "",
      "logradouroSacadorAvalista": "",
      "nuLogradouroSacadorAvalista": "0",
      "complementoLogradouroSacadorAvalista": "",
      "cepSacadorAvalista": "0",
      "complementoCepSacadorAvalista": "0",
      "bairroSacadorAvalista": "",
      "municipioSacadorAvalista": "",
      "ufSacadorAvalista": "",
      "cdIndCpfcnpjSacadorAvalista": "0",
      "nuCpfcnpjSacadorAvalista": "0",
      "endEletronicoSacadorAvalista": ""
   }
```

## Referências / Tecnologias:
- [PHP](https://www.php.net/)
