<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\User\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\Chat\Service;

class ChatController extends Controller
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $currentUser = auth()->user();
        $otherUsers = User::whereNot('id', $currentUser->id)->get();
        $otherUsersResource = UserResource::collection($otherUsers);

        $userChats = $currentUser
            ->chats()
            ->has('messages')
            ->withCount('unreadableMessageStatuses')
            ->get();
        $chatsResource = ChatResource::collection($userChats);

        return inertia('Chat/Index', [
            'users' => $otherUsersResource,
            'chats' => $chatsResource,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $chat = $this->service->store($request->validated());

        return redirect()->route('chats.show', $chat);
    }

    public function show(Chat $chat)
    {
        $messages = $chat->messages()->with('user')->get();

        $chat->unreadableMessageStatuses()->update(['is_read' => true]);

        return inertia('Chat/Show', [
            'users' => UserResource::collection($chat->users()->get()),
            'chat' => ChatResource::make($chat),
            'messages' => MessageResource::collection($messages),
        ],
        );
    }
}
