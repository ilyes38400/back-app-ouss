<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function generateCaption(Request $request)
    {
        $request->validate([
            'image' => 'required|image', // max 4MB
        ]);

        $apiKey = env('GOOGLE_API_KEY');

        // Lire et encoder l'image en base64
        $image = $request->file('image');
        $mimeType = $image->getMimeType();
        $base64Data = base64_encode(file_get_contents($image->getRealPath()));

        $payload = [
            "contents" => [[
                "parts" => [
                    [
                        "text" => "Analyse cette image de plat et retourne uniquement un JSON avec les champs suivants :
        
        {
          \"titre\": \"titre du plat analysé\",
          \"description\": \"description courte, avec types d'aliments, cuisson et quantités approximatives\",
          \"valeurs_nutritionnelles\": {
            \"calories\": \"valeur kcal\",
            \"proteines\": \"valeur g\",
            \"lipides\": \"valeur g\",
            \"glucides\": \"valeur g\",
            \"fibres\": \"valeur g\"
          }
        }
        
        Sois concis et précis, retourne uniquement ce JSON sans rien ajouter autour."
                    ],
                    [
                        "inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $base64Data
                        ]
                    ]
                ]
            ]]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", $payload);

        return response()->json($response->json(), $response->status());
    }
}
