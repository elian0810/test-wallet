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


}
