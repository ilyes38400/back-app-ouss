<?php

namespace App\DataTables;

use App\Models\HomeInformation;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use App\Traits\DataTableTrait;

class HomeInformationDataTable extends DataTable
{
    use DataTableTrait;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('status', function($hi) {
                $c = $hi->status === 'active' ? 'primary' : 'warning';
                return "<span class='badge bg-$c text-capitalize'>{$hi->status}</span>";
            })
            ->addColumn('action', function($hi) {
                return view('home-informations.action', ['id' => $hi->id])->render();
            })
            ->rawColumns(['status','action'])
            ->addIndexColumn();
    }

    public function query(HomeInformation $model)
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
            Column::make('video_url')->title(__('message.video_url')),
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
        return 'HomeInformation_' . date('YmdHis');
    }
}
