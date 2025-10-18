<?php
// app/Http/Controllers/API/HomeInformationController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HomeInformation;
use App\Http\Resources\HomeInformationResource;

class HomeInformationController extends Controller
{
    /**
     * GET /api/home-information
     * Renvoie une seule HomeInformation (la plus récente).
     */
    public function show()
    {
        // Si vous préférez la toute première :
        // $item = HomeInformation::firstOrFail();

        // Ou bien la plus récente :
        $item = HomeInformation::latest('id')->firstOrFail();

        return new HomeInformationResource($item);
    }
}
