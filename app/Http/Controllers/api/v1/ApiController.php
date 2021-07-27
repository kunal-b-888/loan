<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    /**
     * The pagination length for all pagination data.
     *
     * @var int
     */
    public $pagination_length;

    /**
     * The offset for all pagination data.
     *
     * @var int
     */
    public $offset;

    /**
     * The cache expiration time for all cache request.
     *
     * @var int
     */
    public $cache_expiration_time;

    public $default_status;

    public $background_process_limit = 500;

    /**
     * The product default required attributes count.
     *
     * @var int
     */
    public $product_default_required_attrs_count = 5;

    public function __construct()
    {
        $this->pagination_length = 25;
        $this->offset = 0;
        $this->cache_expiration_time = 1800;
        $this->default_status = 'active';
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $meta_data = '')
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        if (!empty($meta_data)) {
            $response['meta_data'] = $meta_data;
        }
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error_msg, $code = 404, $errorMessages = [])
    {
        $response = [
            'success' => false,
            'message' => $error_msg,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendApiFailedError($error_msg, $code = 200, $errorMessages = [])
    {
        $response = [
            'success' => false,
            'message' => $error_msg,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendValidationError($error = '', $error_messages = [], $code = 422)
    {
        $defaul_msg = "";

        $response = [
            'success' => false,
            'message' => $error,
            'errors' => $error_messages,
        ];

        return response()->json($response, $code);
    }

    public function sendApiRequest($host, $username, $password, $headers, $payload, $method = 'get')
    {
        if (in_array($method, Config('constants.API_METHODS'))) {
            $response = Http::withHeaders($headers)
                ->withBasicAuth($username, $password)
                ->withoutVerifying()
                ->$method($host, $payload);
            return $response;

        } else {

        }

    }
}
