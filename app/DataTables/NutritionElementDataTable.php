<?php

namespace App\DataTables;

use App\Models\NutritionElement;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use App\Traits\DataTableTrait;

class NutritionElementDataTable extends DataTable
{
    use DataTableTrait;

    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // Badge statut identique à Diet
            ->editColumn('status', function ($item) {
                $badge = $item->status === 'active' ? 'primary' : 'warning';
                return '<span class="text-capitalize badge bg-'.$badge.'">'.$item->status.'</span>';
            })

            // Date “ago”
            ->editColumn('created_at', fn($item) => dateAgoFormate($item->created_at, true))
            ->editColumn('updated_at', fn($item) => dateAgoFormate($item->updated_at, true))

            // Colonne action via la view nutrition_elements.action
            ->addColumn('action', function ($item) {
                $id = $item->id;
                return view('nutrition_elements.action', compact('item','id'))->render();
            })

            // La même que addIndexColumn() de Diet
            ->addIndexColumn()

            // **Identique à DietDataTable** pour l’ordre
            ->order(function ($query) {
                if (request()->has('order')) {
                    $order        = request()->order[0];
                    $column_index = $order['column'];

                    // par défaut on trie sur id DESC pour l’index
                    $column_name = 'id';
                    $direction   = 'desc';
                    if ($column_index != 0) {
                        $column_name = request()->columns[$column_index]['data'];
                        $direction   = $order['dir'];
                    }

                    $query->orderBy($column_name, $direction);
                }
            })

            ->rawColumns(['status','action']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(NutritionElement $model)
    {
        return $this->applyScopes(
            $model->newQuery()->select([
                'id', 'title', 'slug', 'status', 'created_at', 'updated_at'
            ])
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
                  ->searchable(false)
                  ->orderable(false),

            Column::make('title')->title(__('message.title')),
            Column::make('slug')->title(__('Slug')),
            Column::make('status')->title(__('message.status')),
            Column::make('created_at')->title(__('message.created_at')),
            Column::make('updated_at')->title(__('message.updated_at')),

            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center hide-search')
                  ->title(__('message.action')),
        ];
    }

    /**
     * Filename for export.
     */
    protected function filename(): string
    {
        return 'NutritionElement_' . date('YmdHis');
    }
}
