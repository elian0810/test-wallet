<?php

namespace App\Utils;
use Illuminate\Support\Str;
use Exception;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\Cast\Bool_;
use Location\Coordinate;
use Location\Distance\Vincenty;
class Util
{

    /**
     * Funcion que permite lanazar una excepcion personalizada, siempre y cuando se utlize el bloque try catch
     * @param $error , hace referencia a el error capturado por un catch, dentro de un bloque try catch.
     * @return  $custom_message , hace referencia a el mensaje personalalizado que devolvera el api o endpoint.
     */
    public static function throwCustomException(string $message): string
    {
        throw new Exception("Excep : ". $message);
    }

}