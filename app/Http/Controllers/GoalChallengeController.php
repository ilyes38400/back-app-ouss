<?php

namespace App\Http\Controllers;

use App\DataTables\GoalChallengeDataTable;
use App\Models\GoalChallenge;
use App\Http\Requests\GoalChallengeRequest;

class GoalChallengeController extends Controller
{
    public function index(GoalChallengeDataTable $dt)
    {
        $pageTitle = __('message.list_form_title',['form'=>__('message.goal_challenge')]);
        $assets    = ['data-table'];
        $headerAction = '<a href="'.route('goal-challenges.create').'" class="btn btn-primary">'.__('message.add',[ 'form'=>__('message.goal_challenge')]).'</a>';

        return $dt->render('global.datatable', compact('pageTitle','assets','headerAction'));
    }

    public function create()
    {
        $pageTitle = __('message.add_form_title',['form'=>__('message.goal_challenge')]);
        return view('goal_challenges.form', compact('pageTitle'));
    }

    public function store(GoalChallengeRequest $req)
    {
        $gc = GoalChallenge::create($req->all());
        // aucun media pour l'instant ou eventuel
        return redirect()->route('goal-challenges.index')->withSuccess(__('message.save_form',['form'=>__('message.goal_challenge')]));
    }

    public function edit($id)
    {
        $data = GoalChallenge::findOrFail($id);
        $pageTitle = __('message.update_form_title',['form'=>__('message.goal_challenge')]);
        return view('goal_challenges.form', compact('data','id','pageTitle'));
    }

    public function update(GoalChallengeRequest $req, $id)
    {
        $gc = GoalChallenge::findOrFail($id);
        $gc->update($req->all());
        return redirect()->route('goal-challenges.index')->withSuccess(__('message.update_form',['form'=>__('message.goal_challenge')]));
    }

    public function destroy($id)
    {
        GoalChallenge::findOrFail($id)->delete();
        return response()->json(['status'=>true,'message'=>__('message.delete_form',['form'=>__('message.goal_challenge')])]);
    }
}
