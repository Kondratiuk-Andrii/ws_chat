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
            ->with(['lastMessage', 'chatWith'])
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
        $page = request('page') ?? 1;

        $users = $chat->getUsers();
        $messages = $chat->getMessagesWithPagination($page);

        $chat->readMessages();

        $isLastPage = $messages->onLastPage();

        $messages = MessageResource::collection($messages)->resolve();

        if ($page > 1) {

            return response()->json([
                'isLastPage' => $isLastPage,
                'messages' => $messages,
            ]);
        }

        return inertia('Chat/Show', [
            'users' => UserResource::collection($users),
            'chat' => ChatResource::make($chat),
            'messages' => $messages,
            'isLastPage' => $isLastPage,
        ],
        );
    }
}
