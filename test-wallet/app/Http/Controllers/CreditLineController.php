<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\CreditLine;
use App\Models\Token;
use App\Utils\Util;
use App\Mail\GenericEmail;
use App\Mail\GenericEmailDebtCredit;
use App\Rules\ValidAttribute;
use App\Utils\FormatResponse;
use Illuminate\Http\Request;
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
            $search    = $request->get('search')?? null;
            $per_page  = $request->get('per_page', 10);
            $document    = $request->get('document')?? null;
            $phone    = $request->get('phone')?? null;
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
            if ($document && $phone) {
                $credit_lines = $credit_lines
                    ->where('customers.phone', $phone)
                    ->where('customers.document', $document);
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


    /**
     * Genera un token temporal de 6 dígitos para autorizar el pago de una deuda.
     * El token tiene una validez máxima de 20 minutos. Se almacena en la línea de crédito,
     * y se envía al correo del cliente. También se genera un ID de sesión único para confirmar la transacción.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateTokenTotalDebt(Request $request)
    {
        try {
            $rules = [
                'document'   => 'required|string|min:10|max:15',
                'total_debt' => 'required|numeric|min:0',
                'phone'      => 'required|string|size:10',
                'email'      => 'required|email|max:255',
            ];

            $messages = [
                'document.required' => 'El documento es requerido.',
                'document.string'   => 'El documento debe ser un texto.',
                'document.min'      => 'El documento debe tener al menos 10 caracteres.',
                'document.max'      => 'El documento no debe exceder los 15 caracteres.',

                'total_debt.required' => 'El total a pagar es obligatorio.',
                'total_debt.numeric'  => 'El total a pagar debe ser un número.',
                'total_debt.min'      => 'El total a pagar no puede ser negativo.',

                'phone.required' => 'El teléfono es requerido.',
                'phone.string'   => 'El teléfono debe ser un texto.',
                'phone.size'     => 'El teléfono debe tener exactamente 10 caracteres.',

                'email.required' => 'El correo electrónico a notificar es requerido.',
                'email.email'    => 'El correo electrónico no tiene un formato válido.',
                'email.max'      => 'El correo electrónico no debe exceder los 255 caracteres.',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                Util::throwCustomException($validator->errors()->first());
            }

            // Verificar si la línea de crédito existe con los datos proporcionados
            $credit_line = CreditLine::checkCreditLine($request->document, $request->phone);
            if ($request->total_debt > $credit_line->balance) {
                Util::throwCustomException("El monto a pagar no puede ser mayor al saldo disponible en su Línea de crédito");
            }
            $credit_line->total_debt+= $request->total_debt; 
            $credit_line->save();
            DB::beginTransaction();

            // Generar token de 6 dígitos
            $token = random_int(100000, 999999);

            // Tiempo de expiración del token (20 minutos desde ahora)
            $timeout = now()->addMinutes(5);

            $session_id = Str::uuid()->toString();
            // Guardar token y timeout en la línea de crédito
            Token::create([
                'credit_line_id' => $credit_line->id,
                'token'          => $token,
                'value'          => $request->total_debt,
                'timeout_token'  =>  $timeout,
                'uuid'=>$session_id
            ]);
            
            $data = [
                'subject' => 'Confirmación de Pago',
                'name'=> 'Jhon Doe',
                'token'=> $token
            ];

            Mail::to($request->email)->send(new GenericEmail($data, [], []));
            // Crear un session_id único (puedes guardarlo si lo necesitas)
            DB::commit();
            return FormatResponse::successful("Se ha enviado un código de confirmación al correo.", [
                'session_id' => $session_id,
                'token'=>$token
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return FormatResponse::failed($e);
        }
    }
    
    /**
     * Realiza el pago de una deuda en la línea de crédito asociada a un token y sesión.
     *
     * Este método valida el token y la sesión recibidos, verifica que la deuda no sea mayor
     * al saldo disponible, y en caso de ser válido, descuenta la deuda del saldo, la suma al
     * total de consumo, y reinicia el valor de la deuda a 0.
     *
     * @param Request $request Contiene 'session_id' y 'token' para validar la operación.
     * @return \Illuminate\Http\JsonResponse Respuesta formateada indicando éxito o error.
    */
    public function debtCreditLine(Request $request)
    {
        try {
            $rules = [
                'session_id' => 'required|string',
                'token'      => 'required|numeric',
            ];
            
            $messages = [
                'session_id.required' => 'El ID de sesión es obligatorio.',
                'session_id.string'  => 'El ID de sesió debe ser un un texto.',
                'token.required'      => 'El token es obligatorio.',
                'token.numeric'  => 'El token debe ser un número.',
            ];
            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                Util::throwCustomException($validator->errors()->first());
            }
    
            // Verificar si la Token existe con los datos proporcionados
            $token_model = Token::checkToken($request->session_id, $request->token);
            DB::beginTransaction();
            
            if ($token_model instanceof \Illuminate\Http\Response) {
                $response_data = json_decode($token_model->getContent(), true);
            
                $data = [
                    'subject' => 'Notificación de pago',
                    'status'  => $response_data['success'],
                    'message' => $response_data['messages'][0] ?? 'Error desconocido'
                ];
            
                Mail::to($request->email)->send(new GenericEmailDebtCredit($data));
            
                return $token_model; // Retornas el error como está
            }
            
            if ($token_model) {
                $credit_line = CreditLine::where('id',$token_model->credit_line_id)->first();
                if ($token_model->value > $credit_line->balance) {
                    Util::throwCustomException("El monto a pagar no puede ser mayor al saldo disponible en su Línea de crédito");
                }else{
                    $credit_line->balance -= $credit_line->total_debt;
                    $credit_line->total_consumption += $credit_line->total_debt;
                    $credit_line->total_debt -=$token_model->value;
                    $token_model->value = 0;
                    $token_model->save();
                    $credit_line->save();
                }

            };
            // Si todo fue bien, enviamos el correo exitoso
            $data = [
                'subject' => 'Notificación de pago',
                'status'  => true,
                'message' => 'El pago se procesó correctamente.'
            ];
            Mail::to($request->email)->send(new GenericEmailDebtCredit($data, [], []));
            // Crear un session_id único (puedes guardarlo si lo necesitas)
            DB::commit();
            return FormatResponse::successful("Pago realizado exitosamente");
        } catch (\Exception $e) {
            DB::rollBack();
            return FormatResponse::failed($e);
        }
    }
}
