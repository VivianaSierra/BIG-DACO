<?php

use App\Models\Producto;
use Illuminate\Support\Facades\Http;

test('chatbot calls a database function for inventory questions', function () {
    Producto::create([
        'nombre' => 'Arroz',
        'categoria' => 'Granos',
        'precio' => 1500,
        'stock' => 12,
    ]);

    Http::fakeSequence()
        ->push([
            'candidates' => [[
                'content' => [
                    'role' => 'model',
                    'parts' => [[
                        'functionCall' => [
                            'name' => 'resumen_inventario',
                            'args' => new stdClass,
                        ],
                    ]],
                ],
            ]],
        ])
        ->push([
            'candidates' => [[
                'content' => [
                    'role' => 'model',
                    'parts' => [['text' => 'Hay 1 producto y 12 unidades.']],
                ],
            ]],
        ]);

    $this->withSession([
        'firebase_user' => ['id' => 'firebase-test', 'name' => 'Test', 'email' => 'test@example.com'],
    ])->postJson('/chatbot', [
        'message' => 'How many products and units are in inventory?',
    ])->assertOk()->assertJson([
        'reply' => 'Hay 1 producto y 12 unidades.',
    ]);

    Http::assertSent(function ($request) {
        return data_get($request->data(), 'toolConfig.functionCallingConfig.mode') === 'ANY';
    });

    Http::assertSent(function ($request) {
        $functions = data_get($request->data(), 'contents.2.parts', []);

        return data_get($functions, '0.functionResponse.response.result.total_productos') === 1
            && data_get($functions, '0.functionResponse.response.result.unidades_en_stock') === 12;
    });
});
