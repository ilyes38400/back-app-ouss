<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ProgramController extends Controller
{
    public function fetchUserPrograms(Request $request)
    {
         $userId = $request->all()["id"];
        // Vérifier si l'utilisateur est authentifié
        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
        // Appeler l'API du site pour récupérer les programmes
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('API_KEY'),
        ])->withoutVerifying()
        ->get(env('API_BASE_URL') . '/user-programs', [
            'user_id' => $userId,
        ]);
       
        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la récupération des programmes.'], 500);
        }
    
        // Récupérer les données de programmes
        $programs = $response->json(); // La réponse de l'API est une liste de programmes
    
        // Formater la réponse comme dans l'exemple donné
        $responseData = [
            'data' => $programs,
        ];
    
        // Appeler la fonction json_custom_response pour retourner la réponse formatée
        return json_custom_response($responseData);
    }


    public function fetchUserProgramsFree(Request $request)
    {
        // Appeler l'API du site pour récupérer les programmes
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('API_KEY'),
        ])->withoutVerifying()
        ->get(env('API_BASE_URL') . '/user-programs-free'
        );
       
        if ($response->failed()) {
            return response()->json(['error' => 'Erreur lors de la récupération des programmes.'], 500);
        }
    
        // Récupérer les données de programmes
        $programs = $response->json(); // La réponse de l'API est une liste de programmes
    
        // Formater la réponse comme dans l'exemple donné
        $responseData = [
            'data' => $programs,
        ];
    
        // Appeler la fonction json_custom_response pour retourner la réponse formatée
        return json_custom_response($responseData);
    }
    
    public function getVideoStream(Request $request)
    {
        $userId = $request->input('user_id', 31); // Défaut : 31 pour tester
        $filename = $request->input('filename');
    
        // Vérifier si l'utilisateur est authentifié
        if (!$userId) {

            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        // Générer l'URL pour accéder à la vidéo
        $videoUrl = env('API_BASE_URL') . '/videoProgramme?filename=' . urlencode($filename) . '&user_id=' . $userId;
 $videoUrl = stripslashes($videoUrl);    

        $responseData = [
            'videoUrl' => $videoUrl,
        ];
    
        // Appeler la fonction json_custom_response pour retourner la réponse formatée
        return json_custom_response($responseData);
    }

    public function getVideoStreamFree(Request $request)
    {
        $userId = $request->input('user_id', 31); // Défaut : 31 pour tester
        $filename = $request->input('filename');
    
        // Vérifier si l'utilisateur est authentifié
        if (!$userId) {

            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        // Générer l'URL pour accéder à la vidéo
        $videoUrl = env('API_BASE_URL') . '/videoProgrammeFree?filename=' . urlencode($filename) . '&user_id=' . $userId;
 $videoUrl = stripslashes($videoUrl);    

        $responseData = [
            'videoUrl' => $videoUrl,
        ];
    
        // Appeler la fonction json_custom_response pour retourner la réponse formatée
        return json_custom_response($responseData);
    }
    
    
    
    


    // public function getVideoUrl(Request $request){
    //     // $userId = auth()->id();
    //     //$userId = $request->input('user_id');
    //     $userId = 31;
    //     $filename = $request->input('filename');
    
    //     // Vérifier si l'utilisateur est authentifié
    //     if (!$userId) {
    //         return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    //     }
    //     // Appeler l'API du site pour récupérer les programmes
    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . env('API_KEY'),
    //     ])->withoutVerifying()
    //     ->get(env('API_BASE_URL') . '/videoProgramme', [
    //         'user_id' => $userId,
    //         'filename' => $filename
    //     ]);
    //     if ($response->failed()) {
    //         return response()->json(['error' => 'Erreur stream.'], 500);
    //     }
    //     // Récupérer les données de programmes
    //     $programs = $response->json(); // La réponse de l'API est une liste de programmes
    
    //     // Formater la réponse comme dans l'exemple donné
    //     $responseData = [
    //         'data' => $programs,
    //     ];
    
    //     // Appeler la fonction json_custom_response pour retourner la réponse formatée
    //     return json_custom_response($responseData);

    // }



    // public function getPrograms()
    // {
    //     // URL de base de l'API externe et clé API
    //     $apiUrl = env('API_BASE_URL');
    //     $apiKey = env('API_KEY');
    //     // Effectuer l'appel à l'API externe pour récupérer les programmes
    //     // $response = Http::get("{$apiUrl}/programs", [
    //     // ]);
    //     // dd($response);
    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $apiKey,
    //     ])->withoutVerifying() // Ignore la vérification SSL
    //       ->get("{$apiUrl}/programs");

    //     // Vérifier la réponse
    //     if ($response->successful()) {
    //         // Si la réponse est OK, retourner les données en JSON
    //         return response()->json([
    //             'data' => $response->json(),  // Retourne les données JSON de l'API externe
    //         ]);
    //     } else {
    //         // Si une erreur se produit, retourner une erreur
    //         return response()->json([
    //             'error' => 'Échec de la récupération des programmes externes.',
    //             'message' => $response->body(),
    //         ], $response->status());
    //     }
    // }


    
}