<?php

namespace App\Http\Controllers\DataTable;

use App\Plan;
use Illuminate\Http\Request;
use App\Http\Controllers\DataTable\DataTableController;

class PlanController extends DataTableController
{
    protected $allowCreation = true;

    protected $allowDeletion = true;

    public function builder()
    {
        return Plan::query();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'price' => 'required',
            'payment_id' => 'required',
            'active' => 'required',
        ]);

        $this->builder->create($request->only($this->getUpdatableColumns()));
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'price' => 'required',
            'payment_id' => 'required',
            'active' => 'required'
        ]);
        
        $this->builder->find($id)->update($request->only($this->getUpdatableColumns()));
    }
}
