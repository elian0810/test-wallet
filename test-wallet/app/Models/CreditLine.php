<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Utils\Util;

class CreditLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'balance',
        'total_debt',
        'token',
        'timeout_token',
        'total_consumption',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    /**
     * Verifica si un cliente ya tiene una línea de crédito activa.
     *
     * Esta función realiza una consulta en la base de datos para verificar si el cliente con el ID
     * proporcionado ya tiene una línea de crédito registrada. Si existe una línea de crédito activa,
     * se lanza una excepción personalizada con el nombre del cliente.
     *
     * @param  int  $customer_id  El ID del cliente a verificar.
     * @throws \Exception Si el cliente ya tiene una línea de crédito activa, se lanza una excepción.
    */
    public static function checkCustomer($customer_id)
    {
        try {
            $credit_line = CreditLine::join('customers', 'customers.id', '=', 'credit_lines.customer_id')
                ->where('credit_lines.customer_id', $customer_id)
                ->select('customers.name')
                ->first();

            if ($credit_line) {
                Util::throwCustomException("El cliente ".$credit_line->name." ya tiene una línea de crédito activa.");
            }
        } catch (\Exception $e) {
            throw $e; // Re-lanzamos para que el método que lo llama lo capture y maneje
        }
    }


    /**
     * Verifica si existe una línea de crédito asociada a un cliente con los parámetros proporcionados.
     *
     * @param int $customer_id ID del cliente.
     * @param string $document Documento de identidad del cliente.
     * @param string $phone Teléfono del cliente.
     * @return CreditLine Retorna la línea de crédito encontrada.
     *
     * @throws \Exception Si no existe una línea de crédito que coincida con los parámetros,
     *                    o si ocurre un error durante la ejecución.
     */
    public static function checkCreditLine( $document, $phone)
    {
        try {

            $credit_line = CreditLine::join('customers', 'customers.id', '=', 'credit_lines.customer_id')
                ->where('customers.document', $document)
                ->where('customers.phone', $phone)
                ->select('credit_lines.*') // Aseguramos que se retornen los campos de credit_lines
                ->first();
        
            if (!$credit_line) {
                Util::throwCustomException("No existe una línea de crédito con esos parámetros.");
            }
        
            return $credit_line;
        } catch (\Exception $e) {
            throw $e; // Re-lanzamos para que el método que lo llama lo capture y maneje
        }
    }


}
