<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Message\MessageResource;
use App\Services\Message\Service;

class MessageController extends Controller
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $message = $this->service->store($data);

        return MessageResource::make($message);
    }
}
