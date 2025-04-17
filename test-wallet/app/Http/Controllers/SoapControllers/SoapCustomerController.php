<?php

namespace App\Http\Controllers\SoapControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\App;
use App\Utils\FormatResponse;
use SimpleXMLElement;

class SoapCustomerController
{
    /**
     * Servicio SOAP que crea un cliente llamando al controlador REST.
     * Este método recibe una solicitud SOAP que contiene los datos del cliente en formato XML,
     * los procesa y luego llama al controlador REST para registrar al cliente. La respuesta se
     * convierte en formato XML y se devuelve como respuesta SOAP.
     *
     * @param Request $request La solicitud entrante que contiene los datos del cliente en formato XML.
     *
     * @return \Illuminate\Http\Response Respuesta en formato XML que contiene el resultado de la creación del cliente.
     * 
     * @throws \Exception Lanza una excepción en caso de error durante el proceso de creación del cliente.
    */
    public function soapCustomer(Request $request)
    {
        try {
            $xmlRaw = $request->getContent();
            $xml = simplexml_load_string($xmlRaw);
            $xml->registerXPathNamespace('cust', 'http://example.com/customer');
            $node = $xml->xpath('//cust:createCustomer')[0];

            // Construir array con los datos SOAP
            $data = [
                'document' => (string) $node->document,
                'name'     => (string) $node->name,
                'email'    => (string) $node->email,
                'phone'    => (string) $node->phone,
            ];

            // Crear un nuevo Request con esos datos
            $fake_request = new Request($data);

            // Llamar al método create del CustomerController
            $controller = App::make(CustomerController::class);
            $json_response = $controller->create($fake_request);

            // Convertir el JSON de respuesta en array
            $response_array = json_decode($json_response->getContent(), true);

            // Asegúrate de que 'success' sea un valor booleano o numérico adecuado
            $response_array['success'] = $response_array['success'] ? 1 : 0; // Convertir a 1 (true) o 0 (false)

            // Convertir el array a XML
            $xml_response = FormatResponse::arrayToXml($response_array, new SimpleXMLElement('<response/>'));

            return response($xml_response->asXML(), 200) ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            throw $e; // Re-lanzamos para que el método que lo llama lo capture y maneje
        }
    }


    /**
     * Servicio SOAP que lista los clientes usando el controlador REST.
     *
     * Este método procesa la solicitud SOAP para obtener una lista de clientes,
     * llama al método REST `index` y convierte su respuesta JSON en XML.
     *
     * @param Request $request La solicitud SOAP con parámetros opcionales (search, paginate, per_page).
     * @return \Illuminate\Http\Response Respuesta XML con el listado de clientes.
     *
     * @throws \Exception Si ocurre un error durante el proceso.
     */
    public function soapIndexCustomer(Request $request)
    {
        try {
            // Extraer parámetros desde el XML si es necesario, o desde query string
            $search    = $request->input('search');
            $per_page  = $request->input('per_page', 10);
            $paginate  = $request->input('paginate', 'false');

            // Crear un nuevo request con esos parámetros
            $fake_request = new Request([
                'search'   => $search,
                'per_page' => $per_page,
                'paginate' => $paginate,
            ]);

            // Llamar al método index del CustomerController
            $controller = App::make(CustomerController::class);
            $json_response = $controller->index($fake_request);

            // Decodificar la respuesta JSON a array
            $response_array = json_decode($json_response->getContent(), true);

            // Convertir booleano de success a 1/0
            $response_array['success'] = $response_array['success'] ? 1 : 0;

            // Convertir array a XML
            $xml_response = FormatResponse::arrayToXml($response_array, new \SimpleXMLElement('<response/>'));

            return response($xml_response->asXML(), 200)->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            throw $e;
        }
    }


}
