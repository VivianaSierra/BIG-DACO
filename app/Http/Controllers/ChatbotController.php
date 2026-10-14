<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __invoke(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['array', 'max:20'],
            'history.*.role' => ['required', 'in:user,model'],
            'history.*.text' => ['required', 'string', 'max:4000'],
        ]);

        return response()->json([
            'reply' => $chatbot->ask($data['message'], $data['history'] ?? []),
        ]);
    }
}
