<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Http\Resources\ExerciseResource;
use App\Http\Resources\ExerciseDetailResource;

class ExerciseController extends Controller
{
    public function getList(Request $request)
    {
        $exercise = Exercise::where('status', 'active');

        $exercise->when(request('title'), function ($q) {
            return $q->where('title', 'LIKE', '%' . request('title') . '%');
        });

        $exercise->when(request('equipment_id'), function ($q) {
            return $q->where('equipment_id', request('equipment_id'));
        });

        $exercise->when(request('level_id'), function ($q) {
            $level_ids = explode(',', request('level_id'));
            return $q->whereIn('level_id', $level_ids);
        });

        $exercise->when(request('equipment_ids'), function ($q) {
            $equipment_ids = explode(',', request('equipment_ids'));
            return $q->whereIn('equipment_id', $equipment_ids);
        });

        $exercise->when(request('level_ids'), function ($q) {
            $level_ids = explode(',', request('level_ids'));
            return $q->whereIn('level_id', $level_ids);
        });

        $exercise->when(request('bodypart_id'), function ($q) {
            return $q->whereJsonContains('bodypart_ids', request('bodypart_id'));
        });
        
        if( $request->has('is_premium') && isset($request->is_premium) ) {
            $exercise = $exercise->where('is_premium', request('is_premium'));
        }
                
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)) {
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $exercise->count();
            }
        }

        $exercise = $exercise->orderBy('title', 'asc')->paginate($per_page);

        $items = ExerciseResource::collection($exercise);

        $response = [
            'pagination'    => json_pagination_response($items),
            'data'          => $items,
        ];
        
        return json_custom_response($response);
    }

    public function getDetail(Request $request)
    {
        $exercise = Exercise::where('id',request('id'))->first();
           
        if( $exercise == null )
        {
            return json_message_response( __('message.not_found_entry',['name' => __('message.exercise') ]) );
        }

        $exercise_data = new ExerciseDetailResource($exercise);
            $response = [
                'data' => $exercise_data,
            ];
             
        return json_custom_response($response);
    }
}
