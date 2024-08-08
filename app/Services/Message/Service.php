<?php

namespace App\Services\Message;

use App\Events\StoreMessageEvent;
use App\Http\Resources\Message\MessageResource;
use App\Jobs\StoreMessageStatusJob;
use App\Models\Message;

class Service
{
    public function store($data): MessageResource
    {
        $message = Message::create([
            'chat_id' => $data['chat_id'],
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        StoreMessageStatusJob::dispatch($data, $message)->onQueue('store_messages');

        broadcast(new StoreMessageEvent($message))->toOthers();

        return MessageResource::make($message);
    }
}
