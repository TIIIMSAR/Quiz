<?php

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected $statusCode;

    public function respondSuccess(string $message, $data)
    {
        return $this->setStatusCode(200)->respond($message, true, $data);
    }
 
    public function respondInternalError(string $message, $data = null)
    {
        return $this->setStatusCode(500)->respond($message, false, $data);
    }

    public function respondNotFound(string $message)
    {
        return $this->setStatusCode(404)->respond($message);
    }

    public function respondCreated(string $message, $data)
    {
        return $this->setStatusCode(201)->respond($message, true, $data);
    }

    private function respond(string $message = '', bool $isSuccess = false, $data = null)
    {
        $responseData = [
            'success' => $isSuccess,
            'message' => $message,
            'data' => $data,    
        ];

        return response()->json($responseData)->setStatusCode($this->getStatusCode());
    }

    private function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;  
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}