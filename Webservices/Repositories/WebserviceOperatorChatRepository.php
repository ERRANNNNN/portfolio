<?php

namespace App\Modules\Webservices\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WebserviceOperatorChatRepository
{
    /**
     * Получение чатов
     * @param string $prefix
     * @return Collection
     */
    public function getChats(string $prefix = ""): Collection
    {
        return DB::table('go.chat as goc')
            ->select(
                'goc.id',
                'goc.user_id',
                'goc.user_prefix',
                'goc.user_name',
                DB::raw('COUNT(goc_m.id) as count'),
                DB::raw('COUNT(CASE WHEN goc_m.status = 0 THEN 1 ELSE NULL END) as count_new'),
                'goc.status'
            )->leftJoin('gio.operator_chat_messages as goc_m', 'goc_m.object_id', '=', 'goc.id')
            ->when(!empty($prefix), function ($query) use ($prefix){
                $query->where('goc.user_prefix', '=', $prefix);
            })
            ->groupBy('goc.id')
            ->orderBy('goc.id')
            ->get();
    }

    /**
     * Получение чата
     * @param array $wheres
     * @return Model|Builder|object|null
     */
    public function getChat(array $wheres)
    {
        return DB::table('go.chat')
            ->select('*')
            ->where($wheres)
            ->first();
    }


    /**
     * Обновление данных чата по его id
     * @param int $chatId
     * @param array $values
     */
    public function updateChatByChatId(int $chatId, array $values)
    {
        DB::table('go.chat')
            ->where('id', '=', $chatId)
            ->update($values);
    }

    /**
     * Сохранение чата
     * @param array $values
     * @return int
     */
    public function saveChat(array $values): int
    {
        return DB::table('go.chat')
            ->insertGetId($values);
    }

    /**
     * Получение сообщений чата
     * @param int $chatId
     * @return Collection
     */
    public function getChatMessages(int $chatId): Collection
    {
        return DB::table('go.chat_messages as goc_m')
            ->select(
                'goc_m.date_add',
                'goc_m.id',
                'goc_m.message',
                'goc_m.object_id',
                'goc_m.status',
                DB::raw('IF(is_operator = 1, "Оператор", goc.user_name) as username'),
                DB::raw('IF(is_operator = 1, NULL, goc.user_id) as user_id')
            )
            ->leftJoin('go.chat as goc', 'goc.id', '=', 'goc_m.object_id')
            ->where('goc_m.object_id', '=', $chatId)
            ->orderBy('goc_m.date_add')
            ->get();
    }

    /**
     * Сохранение сообщения
     * @param array $values
     * @return int
     */
    public function saveMessage(array $values): int
    {
        return DB::table('go.chat_messages')->insertGetId($values);
    }

}
