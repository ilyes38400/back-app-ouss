<?php

namespace App\DataTables;

use App\Models\MentalPreparation;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use App\Traits\DataTableTrait;

class MentalPreparationDataTable extends DataTable
{
    use DataTableTrait;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('status', function($mp) {
                $c = $mp->status === 'active' ? 'primary' : 'warning';
                return "<span class='badge bg-$c text-capitalize'>{$mp->status}</span>";
            })
            ->addColumn('action', function($mp) {
                return view('mental_preparations.action', ['id' => $mp->id])->render();
            })
            ->rawColumns(['status','action'])
            ->addIndexColumn();
    }

    public function query(MentalPreparation $model)
    {
        return $this->applyScopes($model->newQuery());
    }

    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')
                  ->title(__('message.srno'))
                  ->orderable(false)
                  ->searchable(false),

            Column::make('title')->title(__('message.title')),
            Column::make('slug')->title(__('message.slug')),
            Column::make('status')->title(__('message.status')),

            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->addClass('text-center')
                  ->title(__('message.action')),
        ];
    }

    protected function filename(): string
    {
        return 'MentalPreparation_' . date('YmdHis');
    }
}
