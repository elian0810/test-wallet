<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Utils\Util;
use App\Rules\ValidAttribute;
use App\Utils\FormatResponse;

use Exception;

class CustomerController extends Controller
{

    /**
     * Retorna un listado de clientes registrados en la base de datos.
     * 
     * Esta función permite aplicar filtros opcionales de búsqueda por:
     * - Documento
     * - Nombre
     * - Correo electrónico
     * - Teléfono
     * 
     * También permite controlar la paginación de resultados mediante:
     * - `per_page`: número de registros por página (default: 10)
     * - `paginate`: booleano para decidir si se pagina o no (default: false)
     * 
     * @param  \Illuminate\Http\Request  $request
     *         Parámetros opcionales:
     *           - search: término a buscar en los campos document, name, email o phone
     *           - per_page: cantidad de registros por página
     *           - paginate: si se debe aplicar paginación
     * 
     * @return \Illuminate\Http\JsonResponse
     *         Respuesta con el listado de clientes (paginado o completo)
    */
    public function index(Request $request)
    { 
        try {

            $search    = $request->get('search');
            $per_page  = $request->get('per_page', 10);
            $paginate  = $request->get('paginate') === 'true';
       
            // Construyendo la consulta
            $customers = Customer::select('id', 'document', 'name', 'email', 'phone');
       
            // Filtro de búsqueda
            if ($search) {
                $searchLike = "%$search%";
                $customers = $customers->where(function ($q) use ($searchLike) {
                    $q->where('document', 'LIKE', $searchLike)
                      ->orWhere('name', 'LIKE', $searchLike)
                      ->orWhere('email', 'LIKE', $searchLike)
                      ->orWhere('phone', 'LIKE', $searchLike);
                });
            }
       
            // Orden por creación
            $customers->orderBy('created_at', 'DESC');
       
            // Obtener resultados (paginados o no)
            $customers = $paginate ? $customers->paginate($per_page) : $customers->get();
    
            return FormatResponse::successful("Listado de clientes", $customers);
        } catch (\Exception $e) {
            return FormatResponse::failed($e);
        }
    }

    /**
     * Crea un nuevo cliente en el sistema.
     *
     * Esta función valida los datos del request (documento, nombre, correo electrónico y teléfono),
     * y si pasan la validación, crea un nuevo registro en la base de datos para el cliente.
     * En caso de error en la validación o durante el proceso de creación, se captura la excepción
     * y se devuelve una respuesta formateada con el mensaje de error.
     *
     * @param  \Illuminate\Http\Request  $request  Datos enviados para crear el cliente.
     * @return \Illuminate\Http\JsonResponse       Respuesta formateada con el estado de la operación.
    */
    public function create(Request $request)
    {
        try {
            $rules = [
                'document'  => 'required|string|min:10|max:15',
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|max:255|unique:customers,email',
                'phone'     => 'required|string|size:10',
            ];

            $messages = [
                'document.required' => 'El documento es requerido.',
                'document.string'   => 'El documento debe ser un texto.',
                'document.min'      => 'El documento debe tener al menos 10 caracteres.',
                'document.max'      => 'El documento no debe exceder los 15 caracteres.',
            
                'name.required' => 'El nombre es requerido.',
                'name.string'   => 'El nombre debe ser un texto.',
                'name.max'      => 'El nombre no debe exceder los 255 caracteres.',
            
                'email.required' => 'El correo electrónico es requerido.',
                'email.email'    => 'El correo electrónico no tiene un formato válido.',
                'email.max'      => 'El correo electrónico no debe exceder los 255 caracteres.',
                'email.unique'   => 'Este correo electrónico ya está registrado.',
            
                'phone.required' => 'El teléfono es requerido.',
                'phone.string'   => 'El teléfono debe ser un texto.',
                'phone.size'     => 'El teléfono debe tener exactamente 10 caracteres.',
            ];

            $validator = \Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                Util::throwCustomException($validator->errors()->first());
            }

            $customer = Customer::create([
                'document' => $request->document,
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
            ]);
            
            return FormatResponse::successful("Cliente registrado con éxito.", $customer);
        } catch (\Exception $e) {
            return FormatResponse::failed($e);
        }
    }

}
