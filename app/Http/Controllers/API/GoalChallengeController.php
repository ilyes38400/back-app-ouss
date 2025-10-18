<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GoalChallenge;
use App\Http\Resources\GoalChallengeResource;

class GoalChallengeController extends Controller
{
    public function listByTheme($theme)
    {
        $items = GoalChallenge::where('theme',$theme)
                              ->where('status','active')
                              ->where('valid_from','<=',now())
                              ->where('valid_until','>=',now())
                              ->get();

        return response()->json(['data'=>GoalChallengeResource::collection($items)]);
    }
}
