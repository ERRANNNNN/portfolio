<?php

namespace App\Modules\Webservices\Services;

use App\Modules\Webservices\Repositories\WebserviceOperatorChatRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WebserviceOperatorService
{
    private string $link = "x"; //для примера убрано
    private int $operatorId = 0; //для примера убрано
    private WebserviceOperatorChatRepository $repository;

    public function __construct()
    {
        $this->repository = new WebserviceOperatorChatRepository();
    }

    /**
     * Отправка сообщения пользователю
     * @param string $botChatId
     * @param string $text
     * @param int $messageId
     * @throws GuzzleException
     */
    private function sendMessageToUser(string $botChatId, string $text, int $messageId): void
    {
        $client = new Client(['base_uri' => $this->link]);
        $data = [
            'id' => $messageId,
            'userId' => $botChatId,
            'type' => 'TextMessage',
            'text' => $text,
            'employee' => [
                'employeeId' => $this->operatorId,
                'firstName' => 'NONE', //для примера убрано
                'lastName' => 'NONE', //для примера убрано
                'avatarUrl' => null //для примера убрано
            ]
        ];
        $client->post('', ['json' => $data]);
    }

    /**
     * Сохранение сообщения
     * @param Request $request
     * @throws GuzzleException
     */
    public function saveMessage(Request $request): void
    {
        $values = [];
        if ($request->filled('chat_id')) {
            $values['is_operator'] = 1;
            $currentChatId = $request->post('chat_id');
        } else {
            $values['is_operator'] = 0;
            $currentChatId = $this->repository->getChat([['chat_id', '=', $request->post('userId')]])->id;
        }
        $text = $request->post('text');
        if (preg_match('/\/start/', $text)) {
            $text= "Пользователь открыл диалог";
        }
        $values['status'] = 1;
        $values['message'] = $text;
        $values['object_id'] = $currentChatId;
        $values['date_add'] = Carbon::now()->toDateTimeString();

        $messageId = $this->repository->saveMessage($values);

        if ($request->filled('chat_id')) {
            $currentChatBotId = $this->repository->getChat([['id', '=', $currentChatId]])->chat_id;
            $this->sendMessageToUser($currentChatBotId, $values['message'], $messageId);
        }
    }

    /**
     * Получение чатов
     * @return Collection
     */
    public function getChats(): Collection
    {
        if (CurrentUser::$permissions->has('operator_viewAll')) {
            $prefix = "";
        } else {
            $prefix = CurrentCompany::$prefix;
        }
        return $this->repository->getChats($prefix);
    }


    public function closeChat(Request $request)
    {
        if ($request->filled('chat_id')) {
            $chatId = $request->post('chat_id');
        } else {
            $chatId = $this->repository->getChat(['chat_id', '=', $request->post('userId')])->id;
        }
        $this->repository->updateChatByChatId($chatId, ['status' => 0]);
    }
}
