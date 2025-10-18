<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NutritionElement;
use App\Http\Resources\NutritionElementResource;

class NutritionElementController extends Controller
{
    /**
     * GET  /api/nutrition-elements
     */
    public function index()
    {
        $items = NutritionElement::where('status','active')->get();

        $collection = NutritionElementResource::collection($items);

        return json_custom_response([
            'data' => $collection,
        ]);
    }

    /**
     * GET  /api/nutrition-elements/{slug}
     */
// app/Http/Controllers/API/NutritionElementController.php

public function show($slug)
{
    $item = NutritionElement::where('slug', $slug)
             ->where('status','active')
             ->first();

    if (! $item) {
        return json_message_response(
            __('message.not_found_entry', ['name' => __('message.nutrition_element')])
        );
    }

    // ★ On enveloppe le resource DANS un array ★
    $response = [
        'data' => [ new NutritionElementResource($item) ]
    ];

    return json_custom_response($response);
}

}
