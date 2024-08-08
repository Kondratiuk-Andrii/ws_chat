<?php

namespace App\Services\Chat;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Service
{
    public function store($data)
    {
        $userIds = $this->getSortedUserIds($data['user_ids']);
        $userIdsString = implode('-', $userIds);

        try {
            DB::beginTransaction();

            // $data['title'] ??= $this->getChatTitle($userIds);

            $chat = Chat::firstOrCreate(
                ['user_ids' => $userIdsString, 'title' => $data['title']],
                ['title' => $data['title']]

            );
            $chat->users()->sync($userIds);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }

        return $chat;
    }

    private function getSortedUserIds(array $user_ids): array
    {
        $userIds = array_merge($user_ids, [auth()->id()]);
        sort($userIds);

        return $userIds;
    }

    // private function getChatTitle(array $userIds): string
    // {
    //     $users = User::whereIn('id', $userIds)->get(['name']);
    //     $userNames = $users->pluck('name')->toArray();

    //     if (count($userIds) > 2) {
    //         return 'Group Chat - '.implode(', ', array_slice($userNames, 0, 4)).'...';
    //     }

    //     return 'Chat - '.implode(', ', $userNames);
    // }
}
