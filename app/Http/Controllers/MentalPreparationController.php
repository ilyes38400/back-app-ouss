<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\MentalPreparationDataTable;
use App\Models\MentalPreparation;
use App\Helpers\AuthHelper;
use App\Http\Requests\MentalPreparationRequest;
use PHPUnit\Framework\Test;

use function Laravel\Prompts\text;

class MentalPreparationController extends Controller
{
    public function index(MentalPreparationDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title', ['form' => __('message.mental_preparation')]);
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('mental-preparation-list')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }
        $assets = ['data-table'];

        $headerAction = '<a href="'.route('mental-preparations.create').'" class="btn btn-sm btn-primary" role="button">'
              .__('message.add_form_title', ['form' => __('message.mental_preparation')]).'</a>';

        return $dataTable->render('global.datatable', compact('pageTitle', 'auth_user', 'assets', 'headerAction'));
    }

    public function create()
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('mental-preparation-add')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $pageTitle = __('message.add_form_title', ['form' => __('message.mental_preparation')]);
        return view('mental_preparations.form', compact('pageTitle'));
    }

    public function store(MentalPreparationRequest $request)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('mental-preparation-add')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        // 1) Créer le modèle avec les champs fillable (incluant video_type & video_url)
        $mp = MentalPreparation::create($request->only([
            'title',
            'slug',
            'description',
            'status',
            'video_type',
            'video_url',
            'program_type',
            'price',
        ]));

        // 2) Stocker l’image (collection « mental_image »)
        if ($request->hasFile('mental_image')) {
            storeMediaFile($mp, $request->mental_image, 'mental_image');
        }
        // 3) Stocker la vidéo (collection « mental_video ») si upload
        if ($request->video_type === 'upload' && $request->hasFile('mental_video')) {
            storeMediaFile($mp, $request->mental_video, 'mental_video');
        } else {
            // si on passe en externe, on vide toute vidéo uploadée
            $mp->clearMediaCollection('mental_video');
        }

        return redirect()
            ->route('mental-preparations.index')
            ->withSuccess(__('message.save_form', ['form' => __('message.mental_preparation')]));
    }

    public function edit($id)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('mental-preparation-edit')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $data      = MentalPreparation::findOrFail($id);
        $pageTitle = __('message.update_form_title', ['form' => __('message.mental_preparation')]);
        return view('mental_preparations.form', compact('data', 'id', 'pageTitle'));
    }

    public function update(MentalPreparationRequest $request, $id)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('mental-preparation-edit')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $mp = MentalPreparation::findOrFail($id);

        // 1) Mettre à jour les champs (incluant video_type & video_url)
        $mp->update($request->only([
            'title',
            'slug',
            'description',
            'status',
            'video_type',
            'video_url',
            'program_type',
            'price',
        ]));

        // 2) Remplacer l’image si fourni
        if ($request->hasFile('mental_image')) {
            $mp->clearMediaCollection('mental_image');
            storeMediaFile($mp, $request->mental_image, 'mental_image');
        }

        // 3) Gérer upload vs externe pour la vidéo
        if ($request->video_type === 'upload') {
            // remplace l’upload si un nouveau fichier est fourni
            if ($request->hasFile('mental_video')) {
                $mp->clearMediaCollection('mental_video');
                storeMediaFile($mp, $request->mental_video, 'mental_video');
            }
        } else {
            // on passe en URL externe → on vide l’ancien upload
            $mp->clearMediaCollection('mental_video');
        }

        return redirect()
            ->route('mental-preparations.index')
            ->withSuccess(__('message.update_form', ['form' => __('message.mental_preparation')]));
    }

    public function destroy($id)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('mental-preparation-delete')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $mp = MentalPreparation::findOrFail($id);
        $mp->delete();

        if (request()->ajax()) {
            return response()->json([
                'status'  => true,
                'message' => __('message.delete_form', ['form' => __('message.mental_preparation')])
            ]);
        }

        return redirect()
            ->route('mental-preparations.index')
            ->withSuccess(__('message.delete_form', ['form' => __('message.mental_preparation')]));
    }
}
