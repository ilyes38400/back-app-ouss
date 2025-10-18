<?php

namespace App\DataTables;

use App\Models\GoalChallenge;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use App\Traits\DataTableTrait;

class GoalChallengeDataTable extends DataTable
{
    use DataTableTrait;

    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            // Affiche “Physique” au lieu de “physique”
            ->editColumn('theme', fn($m) => ucfirst($m->theme))
            // Badge de statut (= bleu si active, orange sinon)
            ->editColumn('status', function($m) {
                $c = $m->status === 'active' ? 'primary' : 'warning';
                return "<span class='badge bg-{$c}'>{$m->status}</span>";
            })
            // On passe à la vue d’action à la fois l’objet “$gc” et son “id”
            ->addColumn('action', function($m) {
                return view('goal_challenges.action', [
                    'gc' => $m,
                    'id' => $m->id
                ])->render();
            })
            ->rawColumns(['status', 'action'])
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     */
    public function query(GoalChallenge $model)
    {
        return $this->applyScopes(
            $model->newQuery()
        );
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')
                  ->title(__('message.srno'))
                  ->orderable(false)
                  ->searchable(false),

            Column::make('theme')
                  ->title(__('message.theme')),

            Column::make('title')
                  ->title(__('message.title')),

            Column::make('valid_from')
                  ->title(__('message.valid_from')),

            Column::make('valid_until')
                  ->title(__('message.valid_until')),

            Column::make('status')
                  ->title(__('message.status')),

            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->addClass('text-center hide-search')
                  ->title(__('message.action'))
                  ->width(60),
        ];
    }

    /**
     * Filename for export.
     */
    protected function filename(): string
    {
        return 'GoalChallenge_' . date('YmdHis');
    }
}
