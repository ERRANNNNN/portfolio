<?php

namespace App\Modules\Webservices;

use App\Modules\Webservices\Services\WebserviceOperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebserviceController
{

    /**
     * Сохранение сообщения от пользователя или оператора
     * @param Request $request
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function saveOperatorMessage(Request $request)
    {
        (new WebserviceOperatorService())->saveMessage($request);
    }

    /**
     * Получение чатов оператора
     * @return JsonResponse
     */
    public function getOperatorChats(): JsonResponse
    {
        return response()->json((new WebserviceOperatorService())->getChats());
    }
}
