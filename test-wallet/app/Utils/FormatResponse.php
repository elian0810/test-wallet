<?php

namespace App\Utils;

use Exception;
use Illuminate\Http\Response as HttpResponse;
use SimpleXMLElement;

class FormatResponse
{
    /**
     * Funcion que permite personalizar el mensaje de validacion y persolaizarlo siempre y cuando se utlize el bloque try catch
     * @param $error , hace referencia a el error capturado por un catch, dentro de un bloque try catch.
     * @return  $custom_message , hace referencia a el mensaje personalalizado que devolvera el api o endpoint.
     */
    public static function throwExceptionMessage(Exception $error): string
    {
        $wordMessage = "Excep : ";
        $message = $error->getMessage();

        /// Verificamos si estamos en produccion o en local
        $_env = env('APP_ENV');
        if ($_env == "local" || !$_env) {
            /// Entra aqui si estamos en local
            return $message;
        }

        /// Si estamos en produccion formateamos el mensaje
        if (strpos($message,  $wordMessage) !== false) {
            /// Si entra en esta condicion es por que el error si contiene la palabra clave
            $custome_message = str_replace($wordMessage, "", $message);
            return $custome_message;
        } else {
            /// Si entra en esta condicion es por que es un error interno por lo cual se
            /// respondera con un mensaje generico
            return "Error interno en el servidor";
        }
    }

    /**
     * Funcion que permite responder de manera fallida a una solicitud http
     * @param $error , hace referencia a el error capturado por un catch, dentro de un bloque try catch.
     *@return \Illuminate\Http\JsonResponse
    */
    public static function failed(Exception $error,$data=null)
    {
        return response(
            [
                'success' => false,
                'messages' => [self::throwExceptionMessage($error)],
                'data' => $data ?? []
            ],
            HttpResponse::HTTP_BAD_REQUEST
        );
    }

    /**
     * Funcion que permite responder de manera exitosa a una solicitud http
     * @param $message , hace referencia a el mensaje personalizado con el cual se le respondera a el host cliente
     * @param $data , hace referencia a el payload o data que se le proporcionara a el host cliente.
     *@return \Illuminate\Http\JsonResponse
     */
    public static function successful($message = "Proceso realizado con éxito", $data = [])
    {
        return response(
            [
                'success' => true,
                'messages' => [$message],
                'data' => $data
            ],
            HttpResponse::HTTP_OK
        );
    }

    /**
     * Funcion que permite responder de manera exitosa a una solicitud http
     * @param $message , hace referencia a el mensaje personalizado con el cual se le respondera a el host cliente
     * @param $data , hace referencia a el payload o data que se le proporcionara a el host cliente.
     *@return \Illuminate\Http\JsonResponse
     */
    public static function error($message = "Proceso fallido", $data = [])
    {
        return response(
            [
                'success' => false,
                'messages' => [$message],
                'data' => $data
            ],
            HttpResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Función que convierte un array en un objeto SimpleXMLElement (XML).
     * @param array $data El array que se desea convertir a XML.
     * @param SimpleXMLElement $xml El objeto SimpleXMLElement que será utilizado para construir el XML.
     * @return SimpleXMLElement El objeto SimpleXMLElement que contiene la representación XML del array.
     * @throws \Exception Lanza una excepción en caso de error durante el proceso de conversión.
     */
    public static function arrayToXml($data, SimpleXMLElement $xml)
    {
        try {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // Si es un array, llamamos recursivamente
                    self::arrayToXml($value, $xml->addChild($key));
                } else {
                    // Convertimos el valor a texto
                    $xml->addChild($key, htmlspecialchars($value));
                }
            }
            return $xml;
        } catch (\Exception $e) {
            throw $e; 
        }
    }


}