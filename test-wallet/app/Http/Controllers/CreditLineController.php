<?php

namespace App\Http\Controllers;

use App\Models\CreditLine;
use Illuminate\Http\Request;
use App\Utils\Util;
use App\Rules\ValidAttribute;
use App\Utils\FormatResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class CreditLineController extends Controller
{

    
    /**
     * Obtiene un listado de líneas de crédito junto con la información del cliente asociado.
     *
     * Permite aplicar búsqueda por documento, nombre, correo o teléfono del cliente.
     * También admite paginación opcional.
     *
     * @param \Illuminate\Http\Request $request
     *  - search (string, opcional): texto a buscar en campos del cliente.
     *  - per_page (int, opcional): cantidad de resultados por página (por defecto: 10).
     *  - paginate (bool, opcional): si se desea paginar la respuesta (por defecto: false).
     * 
     * @return \Illuminate\Http\JsonResponse
    */
    public function index(Request $request)
    {
        try {
            $search    = $request->get('search');
            $per_page  = $request->get('per_page', 10);
            $paginate  = $request->get('paginate') === 'true';
    
            // Consulta base con JOIN a customers
            $credit_lines= creditLine::select(
                    'credit_lines.id',
                    'credit_lines.balance',
                    'credit_lines.total_debt',
                    'credit_lines.total_consumption',
                    'credit_lines.created_at',
                    'customers.id as customer_id',
                    'customers.document',
                    'customers.name',
                    'customers.email',
                    'customers.phone'
                )
                ->join('customers', 'customers.id', '=', 'credit_lines.customer_id');
    
            // Filtro de búsqueda
            if ($search) {
                $search_like = "%$search%";
                $credit_lines->where(function ($q) use ($search_like) {
                    $q->where('customers.document', 'LIKE', $search_like)
                      ->orWhere('customers.name', 'LIKE', $search_like)
                      ->orWhere('customers.email', 'LIKE', $search_like)
                      ->orWhere('customers.phone', 'LIKE', $search_like);
                });
            }
    
            // Ordenar por fecha de creación
            $credit_lines->orderBy('credit_lines.created_at', 'DESC');
    
            // Devolver resultados paginados o no
            $result = $paginate ? $credit_lines->paginate($per_page) : $credit_lines->get();
    
            return FormatResponse::successful("Listado de líneas de crédito", $result);
        } catch (\Exception $e) {
            return FormatResponse::failed($e);
        }
    }
        
    

    /**
     * Crea una nueva línea de crédito para un cliente específico.
     *
     * Valida que el cliente exista y que el saldo inicial sea un valor numérico no negativo.
     * Inicializa la deuda y consumo total en 0.
     *
     * @param \Illuminate\Http\Request $request
     *  - customer_id (int, requerido): ID del cliente al que se le abrirá la línea de crédito.
     *  - balance (float, requerido): saldo inicial de la línea de crédito.
     * 
     * @return \Illuminate\Http\JsonResponse
    */
    public function openCreditLine(Request $request)
    {
        try {
            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'balance'     => 'required|numeric|min:0',
            ];
    
            $messages = [
                'customer_id.required' => 'El ID del cliente es obligatorio.',
                'customer_id.exists'   => 'El cliente no existe.',
                'balance.required'     => 'El saldo inicial es obligatorio.',
                'balance.numeric'      => 'El saldo debe ser un número.',
                'balance.min'          => 'El saldo no puede ser negativo.',
            ];
    
            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                Util::throwCustomException($validator->errors()->first());
            }

            // Validar que el cliente NO tenga una línea de crédito ya registrada
            CreditLine::checkCustomer($request->customer_id);

            DB::beginTransaction();
            $credit_line = CreditLine::create([
                'customer_id'        => $request->customer_id,
                'balance'            => $request->balance,
                'total_debt'         => 0,
                'total_consumption'  => 0,
            ]);
            DB::commit();
            return FormatResponse::successful("Línea de crédito abierta correctamente.", $credit_line);
        } catch (\Exception $e) {
            DB::rollBack();
            return FormatResponse::failed($e);
        }
    }

    /**
     * Incrementa el saldo de una línea de crédito existente 
     * a partir del documento y teléfono del cliente.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendBalane(Request $request)
    {
        try {
            $rules = [
                'document'  => 'required|string|min:10|max:15',
                'balance'     => 'required|numeric|min:0',
                'phone'     => 'required|string|size:10',
            ];

            $messages = [
                'document.required' => 'El documento es requerido.',
                'document.string'   => 'El documento debe ser un texto.',
                'document.min'      => 'El documento debe tener al menos 10 caracteres.',
                'document.max'      => 'El documento no debe exceder los 15 caracteres.',

                'balance.required'     => 'El saldo inicial es obligatorio.',
                'balance.numeric'      => 'El saldo debe ser un número.',
                'balance.min'          => 'El saldo no puede ser negativo.',

                'phone.required' => 'El teléfono es requerido.',
                'phone.string'   => 'El teléfono debe ser un texto.',
                'phone.size'     => 'El teléfono debe tener exactamente 10 caracteres.',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                Util::throwCustomException($validator->errors()->first());
            }
            // Validar que el cliente NO tenga una línea de crédito ya registrada
            $credit_line =  CreditLine::checkCreditLine($request->document, $request->phone);
            DB::beginTransaction();
            $credit_line->balance +=  $request->balance;
            $credit_line->save();
            
            DB::commit();
            return FormatResponse::successful("Saldo aumentado con éxito.", []);
        } catch (\Exception $e) {
            DB::rollBack();
            return FormatResponse::failed($e);
        }
    }

}
