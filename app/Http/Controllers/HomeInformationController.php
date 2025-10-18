<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\HomeInformationDataTable;
use App\Models\HomeInformation;
use App\Helpers\AuthHelper;
use Illuminate\Support\Str;
use App\Http\Requests\HomeInformationRequest;
use PHPUnit\Framework\Test;

class HomeInformationController extends Controller
{
    public function index(HomeInformationDataTable $dataTable)
    {
        $pageTitle  = __('message.list_form_title', ['form' => __('message.home_information')]);
        $auth_user  = AuthHelper::authSession();
        // if (! $auth_user->can('home-information-list')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }
        $assets = ['data-table'];

        $headerAction = /* $auth_user->can('home-information-add') ? */ 
            '<a href="'.route('home-informations.create').'" class="btn btn-sm btn-primary" role="button">'
            .__('message.add_form_title', ['form' => __('message.home_information')])
            .'</a>'
            /* : '' */;

        return $dataTable->render('global.datatable', compact(
            'pageTitle', 'auth_user', 'assets', 'headerAction'
        ));
    }

    public function create()
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('home-information-add')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $pageTitle = __('message.add_form_title', ['form' => __('message.home_information')]);
        return view('home-informations.form', compact('pageTitle'));
    }

    public function store(HomeInformationRequest $request)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('home-information-add')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        // 1) Crée le modèle avec les champs requis
        $data         = $request->only(['title', 'status', 'video_url']);

        $homeInfo = HomeInformation::create($data);
        
        // 2) Stocke la vidéo (collection « home_video ») si on upload
        if ($request->hasFile('home_video')) {
            $test = storeMediaFile($homeInfo, $request->home_video, 'home_video');
        } else {
            // Si on est en URL externe, on vide toute vidéo uploadée précédente
            $homeInfo->clearMediaCollection('home_video');
        }

        return redirect()
            ->route('home-informations.index')
            ->withSuccess(__('message.save_form', ['form' => __('message.home_information')]));
    }

    public function edit($id)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('home-information-edit')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $data      = HomeInformation::findOrFail($id);
        $pageTitle = __('message.update_form_title', ['form' => __('message.home_information')]);
        return view('home-informations.form', compact('data', 'id', 'pageTitle'));
    }

    public function update(HomeInformationRequest $request, $id)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('home-information-edit')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $homeInfo    = HomeInformation::findOrFail($id);
        $data        = $request->only(['title','slug','video_url','status']);
        $data['slug'] = Str::slug($data['title']);

        $homeInfo->update($data);

        return redirect()
            ->route('home-informations.index')
            ->withSuccess(__('message.update_form', ['form' => __('message.home_information')]));
    }

    public function destroy($id)
    {
        $auth_user = AuthHelper::authSession();
        // if (! $auth_user->can('home-information-delete')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $homeInfo = HomeInformation::findOrFail($id);
        $homeInfo->delete();

        if (request()->ajax()) {
            return response()->json([
                'status'  => true,
                'message' => __('message.delete_form', ['form' => __('message.home_information')])
            ]);
        }

        return redirect()
            ->back()
            ->withSuccess(__('message.delete_form', ['form' => __('message.home_information')]));
    }
}
