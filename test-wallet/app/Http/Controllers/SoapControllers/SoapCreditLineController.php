<?php

namespace App\Http\Controllers\SoapControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CreditLineController;
use Illuminate\Support\Facades\App;
use App\Utils\FormatResponse;
use SimpleXMLElement;

class SoapCreditLineController
{

    /**
     * Servicio SOAP que lista las líneas de crédito usando el controlador REST.
     *
     * Este método procesa una solicitud SOAP para obtener líneas de crédito asociadas
     * a clientes, permite aplicar filtros como documento y teléfono, y devuelve la respuesta en XML.
     *
     * @param Request $request La solicitud SOAP con parámetros opcionales (search, document, phone, paginate, per_page).
     * @return \Illuminate\Http\Response Respuesta XML con el listado de líneas de crédito.
     *
     * @throws \Exception Si ocurre un error durante el proceso.
    */
    public function soapIndexCreditLine(Request $request)
    {
        try {
            // Extraer parámetros desde el request SOAP
            $search    = $request->get('search') ?? null;
            $per_page  = $request->get('per_page', 10);
            $document  = $request->get('document') ?? null;
            $phone     = $request->get('phone') ?? null;
            $paginate  = $request->get('paginate') === 'true';
            
            // Crear un nuevo request con los parámetros simulados
            $fake_request = new Request([
                'search'   => $search,
                'document' => $document,
                'phone'    => $phone,
                'per_page' => $per_page,
                'paginate' => $paginate,
            ]); 

            // Llamar al método index del CreditLineController
            $controller = App::make(CreditLineController::class);
            $json_response = $controller->index($fake_request); 

            // Decodificar la respuesta JSON
            $response_array = json_decode($json_response->getContent(), true);  

            // Asegurarse que success sea 1 o 0
            $response_array['success'] = $response_array['success'] ? 1 : 0;    

            // Convertir el array a XML
            $xml_response = FormatResponse::arrayToXml($response_array, new \SimpleXMLElement('<response/>'));   

            return response($xml_response->asXML(), 200)->header('Content-Type', 'application/xml');    

        } catch (\Exception $e) {
            throw $e;
        }
    }   


    /**
     * Servicio SOAP que aumenta el saldo de una línea de crédito
     * utilizando los datos recibidos en formato XML, y llama al método REST correspondiente.
     *
     * Espera una estructura XML con los campos: document, balance y phone.
     * Devuelve una respuesta en formato XML con el estado de la operación.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response (XML)
     */
    public function soapSendBalane(Request $request)
    {
        try {
            $xmlRaw = $request->getContent();
            $xml = simplexml_load_string($xmlRaw);
            $xml->registerXPathNamespace('cred', 'http://example.com/creditline');
            $node = $xml->xpath('//cred:sendBalane')[0];

            // Construir los datos como si vinieran de un formulario
            $data = [
                'document' => (string) $node->document,
                'balance'  => (float) $node->balance,
                'phone'    => (string) $node->phone,
            ];

            // Simular un Request
            $fake_request = new Request($data);

            // Llamar al método REST
            $controller = App::make(CreditLineController::class);
            $json_response = $controller->sendBalane($fake_request);

            // Parsear la respuesta y convertirla en XML
            $response_array = json_decode($json_response->getContent(), true);
            $response_array['success'] = $response_array['success'] ? 1 : 0;

            $xml_response = FormatResponse::arrayToXml($response_array, new \SimpleXMLElement('<response/>'));
            return response($xml_response->asXML(), 200)->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            throw $e;
        }
    }


     /**
     * Servicio SOAP que genera un token de confirmación de pago.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response (XML)
     */
    public function soapGenerateTokenTotalDebt(Request $request)
    {
        try {
            $xmlRaw = $request->getContent();
            $xml = simplexml_load_string($xmlRaw);
            $xml->registerXPathNamespace('cred', 'http://example.com/creditline');
            $node = $xml->xpath('//cred:generateTokenTotalDebt')[0];

            // Construir array con los datos SOAP
            $data = [
                'document'   => (string) $node->document,
                'total_debt' => (float) $node->total_debt,
                'phone'      => (string) $node->phone,
                'email'      => (string) $node->email,
            ];

            // Crear un nuevo Request con esos datos
            $fake_request = new Request($data);

            // Llamar al método generateTokenTotalDebt del controlador
            $controller = App::make(CreditLineController::class);
            $json_response = $controller->generateTokenTotalDebt($fake_request);

            // Convertir el JSON de respuesta en array
            $response_array = json_decode($json_response->getContent(), true);

            // Asegúrate de que 'success' sea un valor booleano o numérico adecuado
            $response_array['success'] = $response_array['success'] ? 1 : 0;

            // Convertir la respuesta a XML
            $xml_response = FormatResponse::arrayToXml($response_array, new SimpleXMLElement('<response/>'));

            return response($xml_response->asXML(), 200)
                ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Servicio SOAP para aplicar un pago a la línea de crédito usando un token de sesión.
     *
     * Este método recibe un `session_id` y un `token` enviados desde una petición SOAP,
     * los valida y luego llama al método REST `debtCreditLine` para procesar el pago.
     * 
     * Retorna la respuesta como XML SOAP.
     */
    public function soapDebtCreditLine(Request $request)
    {
        try {
            // Obtener y parsear el XML recibido desde la petición SOAP
            $xmlRaw = $request->getContent();
            $xml = simplexml_load_string($xmlRaw);
            $xml->registerXPathNamespace('cred', 'http://example.com/creditline');
            $node = $xml->xpath('//cred:debtCreditLine')[0];    

            // Extraer los datos del XML SOAP
            $data = [
                'session_id' => (string) $node->session_id,
                'token'      => (int) $node->token,
                'email'      => (string) $node->email,
            ];  

            // Crear un nuevo request Laravel con los datos extraídos
            $fake_request = new Request($data); 

            // Llamar al controlador original REST
            $controller = App::make(CreditLineController::class);
            $json_response = $controller->debtCreditLine($fake_request);    

            // Convertir respuesta JSON en array PHP
            $response_array = json_decode($json_response->getContent(), true);
            $response_array['success'] = $response_array['success'] ? 1 : 0;    

            // Convertir array a XML
            $xml_response = FormatResponse::arrayToXml($response_array, new \SimpleXMLElement('<response/>'));  

            return response($xml_response->asXML(), 200)
                ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            throw $e;
        }
    }

}