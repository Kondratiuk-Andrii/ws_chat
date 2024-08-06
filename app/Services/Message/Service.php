<?php

namespace App\Services\Message;

use App\Events\StoreMessageEvent;
use App\Events\StoreMessageStatusEvent;
use App\Http\Resources\Message\MessageResource;
use App\Models\Message;
use App\Models\MessageStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class Service
{
    public function store($data): JsonResponse|MessageResource
    {
        try {
            DB::beginTransaction();

            $message = Message::create([
                'chat_id' => $data['chat_id'],
                'user_id' => auth()->id(),
                'body' => $data['body'],
            ]);
            foreach ($data['user_ids'] as $user_id) {
                MessageStatus::create([
                    'chat_id' => $data['chat_id'],
                    'message_id' => $message->id,
                    'user_id' => $user_id,
                ]);

                $count = MessageStatus::where('chat_id', $data['chat_id'])
                    ->where('user_id', $user_id)
                    ->where('is_read', false)
                    ->count();

                broadcast(new StoreMessageStatusEvent($count, $data['chat_id'], $user_id))->toOthers();
            }

            broadcast(new StoreMessageEvent($message))->toOthers();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }

        return MessageResource::make($message);
    }
}
