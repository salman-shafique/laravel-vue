<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\JsonResponse;

trait ResponseMapper
{

    // Holds the value of error
    protected $error = null;

    // Hold the data to be sent in case of success
    protected $payload = null;
    protected $message = null;
    protected $responseCode = null;

    /**
     * @param $data
     * Sends response to the request.
     * @return array
     *
     */
    public function setPagination($data)
    {
        return [
            "page" => $data->currentPage(),
            "pageSize" => $data->perPage(),
            "totalPage" => (int)($data->total() / $data->perPage()) + 1,
            "totalRecords" => $data->total(),
        ];
    }
    /**
     * Method to set API response data
     */
    public function setResponseData($message = null, $error = null, $responseCode = null, $data = [])
    {
        $this->message = $message;
        $this->error = $error;
        $this->responseCode = $responseCode;
        $this->payload = $data;
    }

    /**
     * Method to send API responses
     * @return JsonResponse
     */
    public function sendJsonResponse()
    {
        $this->responseCode = empty($this->responseCode) ? 200 : $this->responseCode;

        return response()->json([
            'success' => $this->responseCode == 200 ? true : false,
            'message' => $this->message,
            'data' => $this->payload,
            'error' => $this->error,
        ], $this->responseCode);
    }

    public function jsonResponse($message = null, $error = null, $responseCode = null, $data = [])
    {
        $responseCode = empty($responseCode) ? 200 : $responseCode;

        return response()->json([
            'success' => $responseCode == 200 ? true : false,
            'message' => $message,
            'data' => $data,
            'error' => $error,
        ], $responseCode);
    }
}
