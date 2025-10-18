<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\NutritionElementDataTable;
use App\Helpers\AuthHelper;
use App\Models\NutritionElement;
use App\Http\Requests\NutritionElementRequest;

class NutritionElementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  NutritionElementDataTable  $dataTable
     * @return \Illuminate\Http\Response
     */
    public function index(NutritionElementDataTable $dataTable)
    {
        $pageTitle   = __('message.list_form_title', ['form' => __('message.nutrition_element')]);
        $assets      = ['data-table'];
    
        // Bouton “Add” toujours affiché
        $headerAction = '<a href="'.route('nutrition-elements.create').'" class="btn btn-sm btn-primary" role="button">'
                      .__('message.add_form_title', ['form' => __('message.nutrition_element')])
                      .'</a>';
    
        return $dataTable->render(
            'global.datatable',
            compact('pageTitle', 'assets', 'headerAction')
        );
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if (! auth()->user()->can('nutrition-element-add')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $pageTitle = __('message.add_form_title', ['form' => __('message.nutrition_element')]);
        return view('nutrition_elements.form', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  NutritionElementRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NutritionElementRequest $request)
    {
        // if (! auth()->user()->can('nutrition-element-add')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $elem = NutritionElement::create($request->all());
        storeMediaFile($elem, $request->image, 'image');

        return redirect()->route('nutrition-elements.index')
                         ->withSuccess(__('message.save_form', ['form' => __('message.nutrition_element')]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return void
     */
    public function show($id)
    {
        $data = NutritionElement::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (! auth()->user()->can('nutrition-element-edit')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $data      = NutritionElement::findOrFail($id);
        $pageTitle = __('message.update_form_title', ['form' => __('message.nutrition_element')]);

        return view('nutrition_elements.form', compact('data', 'id', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  NutritionElementRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(NutritionElementRequest $request, $id)
    {
        // if (! auth()->user()->can('nutrition-element-edit')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $elem = NutritionElement::findOrFail($id);
        $elem->fill($request->all())->update();
        storeMediaFile($elem, $request->image, 'image');

        return redirect()->route('nutrition-elements.index')
                         ->withSuccess(__('message.update_form', ['form' => __('message.nutrition_element')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if (! auth()->user()->can('nutrition-element-delete')) {
        //     return redirect()->back()->withErrors(__('message.permission_denied_for_account'));
        // }

        $elem = NutritionElement::findOrFail($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.nutrition_element')]);

        if ($elem) {
            $elem->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.nutrition_element')]);
        }

        if (request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message]);
        }

        return redirect()->back()->with($status, $message);
    }
}
