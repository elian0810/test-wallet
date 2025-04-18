<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Utils\Util; 
use App\Utils\FormatResponse;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'uuid', 
        'value',
        'timeout_token',
        'credit_line_id'
    ];

    /**
     * Verifica si existe un token con un session_id asociados en la base de datos
     * y que no haya expirado (timeout_token > fecha actual).
     *
     * @param string $token       Token enviado por el cliente
     * @param string $session_id  Identificador de sesión (uuid)
     * @return \App\Models\Token  Modelo de Token válido
     * @throws \Exception         Si el token no existe, no coincide o ha expirado
     */
    public static function checkToken( $session_id,$token)
    {
        try {
            $token_model = Token::select('tokens.*')
            ->where('token', $token)
            ->where('uuid', $session_id)
            ->first();
        
            if (!$token_model) {
                return FormatResponse::error("El token o el ID de sesión no son válidos o no coinciden.", []); // HTTP 400 Bad Request
            }
        
            if ($token_model->timeout_token < now()) {
                return FormatResponse::error("El token ha expirado.", []); // HTTP 400 Bad Request
            }
            if ( $token_model->value == 0) {
                return FormatResponse::error("El pago ya fue aplicado.", []); // HTTP 400 Bad Request
            }
            return $token_model;
        } catch (\Exception $e) {
            throw $e; // Re-lanzamos para que el método que lo llama lo capture y maneje
        }
    }

}
