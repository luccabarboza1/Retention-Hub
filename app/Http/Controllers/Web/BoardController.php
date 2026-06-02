<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\KanbanColumn;

class BoardController extends Controller
{
    public function index()
    {
        $columns = KanbanColumn::orderBy('order')->get();

        $cards = Card::with('customer', 'product')
            ->whereNull('deleted_at')
            ->orderBy('started_at', 'desc')
            ->get()
            ->groupBy('status');

        return view('board', compact('columns', 'cards'));
    }

    public function storeColumn()
    {
        $data = request()->validate([
            'name'  => 'required|string|max:100|unique:kanban_columns,name',
            'color' => 'required|in:gray,blue,yellow,green,red,purple,pink,indigo',
        ]);

        $data['order'] = KanbanColumn::max('order') + 1;
        KanbanColumn::create($data);

        return back()->with('success', 'Etapa criada.');
    }

    public function updateColumn(KanbanColumn $column)
    {
        $data = request()->validate([
            'name'  => 'required|string|max:100|unique:kanban_columns,name,' . $column->id,
            'color' => 'required|in:gray,blue,yellow,green,red,purple,pink,indigo',
        ]);

        $oldName = $column->name;
        $column->update($data);

        // Atualizar cards que usam o nome antigo
        if ($oldName !== $data['name']) {
            Card::where('status', $oldName)->update(['status' => $data['name']]);
        }

        return back()->with('success', 'Etapa atualizada.');
    }

    public function destroyColumn(KanbanColumn $column)
    {
        if ($column->cards()->count() > 0) {
            return back()->with('error', 'Não é possível excluir uma etapa com cards.');
        }

        $column->delete();
        return back()->with('success', 'Etapa removida.');
    }
}
