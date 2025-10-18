<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MentalPreparation;
use App\Http\Resources\MentalPreparationResource;
use App\Traits\ProgramAccessTrait;
use Illuminate\Http\Request;

class MentalPreparationController extends Controller
{
    use ProgramAccessTrait;
    // GET /api/mental-preparations
    public function index(Request $request)
    {
        $items = MentalPreparation::where('status', 'active');

        // Filtrer par type de programme si spécifié
        if ($request->has('program_type') && isset($request->program_type)) {
            $items = $items->where('program_type', $request->program_type);
        }

        $items = $items->get();

        $resource = MentalPreparationResource::collection($items);

        // Ajouter les informations d'accès pour chaque programme mental
        $userId = $request->input('user_id') ?? auth()->id();
        if ($userId) {
            $resource = $resource->map(function ($item) use ($userId) {
                $mental = MentalPreparation::find($item['id']);
                $accessInfo = $this->addAccessInfo($mental, $userId);
                $item['user_has_access'] = $accessInfo->user_has_access;
                $item['access_reason'] = $accessInfo->access_reason;
                $item['requires_purchase'] = $accessInfo->requires_purchase;
                $item['requires_subscription'] = $accessInfo->requires_subscription;
                return $item;
            });
        }

        return response()->json([
            'data' => $resource
        ]);
    }

    // GET /api/mental-preparations/{slug}
    public function show($slug, Request $request)
    {
        $item = MentalPreparation::where('slug', $slug)
                   ->where('status','active')
                   ->firstOrFail();

        // Vérifier l'accès utilisateur
        $userId = $request->input('user_id') ?? auth()->id();
        if ($userId && !$this->hasAccessToProgram($item, $userId)) {
            return response()->json([
                'status' => false,
                'message' => 'Accès refusé à ce programme mental',
                'access_required' => $item->program_type === 'paid' ? 'purchase' : 'subscription'
            ], 403);
        }

        $resource = new MentalPreparationResource($item);

        // Ajouter les informations d'accès
        if ($userId) {
            $accessInfo = $this->addAccessInfo($item, $userId);
            $item->user_has_access = $accessInfo->user_has_access;
            $item->access_reason = $accessInfo->access_reason;
            $item->requires_purchase = $accessInfo->requires_purchase;
            $item->requires_subscription = $accessInfo->requires_subscription;
        }

        return response()->json([
            'data' => $resource
        ]);
    }

    // GET /api/mental-preparations/{id}
    public function getDetail(Request $request)
    {
        $mental = MentalPreparation::where('id', $request->id)->first();

        if ($mental == null) {
            return json_message_response(__('message.not_found_entry', ['name' => 'Programme mental']));
        }

        // Vérifier l'accès utilisateur
        $userId = $request->input('user_id') ?? auth()->id();
        if ($userId && !$this->hasAccessToProgram($mental, $userId)) {
            return response()->json([
                'status' => false,
                'message' => 'Accès refusé à ce programme mental',
                'access_required' => $mental->program_type === 'paid' ? 'purchase' : 'subscription'
            ], 403);
        }

        $resource = new MentalPreparationResource($mental);

        // Ajouter les informations d'accès
        if ($userId) {
            $accessInfo = $this->addAccessInfo($mental, $userId);
            $mental->user_has_access = $accessInfo->user_has_access;
            $mental->access_reason = $accessInfo->access_reason;
            $mental->requires_purchase = $accessInfo->requires_purchase;
            $mental->requires_subscription = $accessInfo->requires_subscription;
        }

        return json_custom_response([
            'data' => $resource
        ]);
    }

    // GET /api/mental-preparations-detail-with-access - Version qui retourne les infos sans bloquer l'accès
    public function getDetailWithAccess(Request $request)
    {
        $mental = MentalPreparation::where('id', $request->id)->first();

        if ($mental == null) {
            return json_message_response(__('message.not_found_entry', ['name' => 'Programme mental']));
        }

        // Ajouter les informations d'accès SANS bloquer l'accès
        $userId = $request->input('user_id') ?? auth()->id();
        if ($userId) {
            $this->addAccessInfo($mental, $userId);
        }

        $resource = new MentalPreparationResource($mental);

        return json_custom_response([
            'data' => $resource
        ]);
    }

    // GET /api/mental-preparations/list - Version avec pagination
    public function getList(Request $request)
    {
        $mental = MentalPreparation::where('status', 'active');

        // Filtres
        $mental->when($request->title, function ($q) {
            return $q->where('title', 'LIKE', '%' . request('title') . '%');
        });

        if ($request->has('program_type') && isset($request->program_type)) {
            $mental = $mental->where('program_type', $request->program_type);
        }

        // Pagination
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page == -1) {
                $per_page = $mental->count();
            }
        }

        $mental = $mental->orderBy('title', 'asc')->paginate($per_page);

        // Ajouter les informations d'accès pour chaque programme mental
        $userId = $request->input('user_id') ?? auth()->id();
        if ($userId) {
            foreach ($mental as $mentalItem) {
                $accessInfo = $this->addAccessInfo($mentalItem, $userId);
                $mentalItem->user_has_access = $accessInfo->user_has_access;
                $mentalItem->access_reason = $accessInfo->access_reason;
                $mentalItem->requires_purchase = $accessInfo->requires_purchase;
                $mentalItem->requires_subscription = $accessInfo->requires_subscription;
            }
        }

        $items = MentalPreparationResource::collection($mental);

        $response = [
            'pagination' => json_pagination_response($mental),
            'data' => $items,
        ];

        return json_custom_response($response);
    }
}
