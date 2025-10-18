<?php

namespace App\Http\Controllers\API;

use App\Models\UserWeight;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class WeightController extends Controller
{
    public function getWeightHistory()
    {
        $user_id = auth()->id();
    
        $entries = UserWeight::where('user_id', $user_id)
            ->orderBy('date')
            ->get();
    
        return json_custom_response([
            'data' => $entries
        ]);
    }
    
    

    public function storeWeight(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric',
            'date' => 'nullable|date',
        ]);
    
        $user_id = auth()->id();
    
        $entry = UserWeight::create([
            'user_id' => $user_id,
            'weight' => $request->weight,
            'date' => $request->date ?? now(),
        ]);
    
        return json_custom_response([
            'message' => 'Poids enregistré avec succès.',
            'data' => $entry,
        ]);
    }
    


}
