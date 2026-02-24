<?php

namespace App\Http\Controllers;

use App\Models\TableSeat;
use App\Http\Requests\StoreTableSeatRequest;
use App\Http\Requests\UpdateTableSeatRequest;
use Illuminate\Support\Facades\DB;

class TableSeatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tables = TableSeat::all();

        return view('admin.tables.index', compact('tables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $typeColumn = DB::select("SHOW COLUMNS FROM table_seats WHERE Field = 'status'")[0]->Type;

        // Extract ENUM values
        preg_match('/^enum\((.*)\)$/', $typeColumn, $matches);
        $statuses = array_filter(
            array_map(fn($value) => trim($value, "'"), explode(',', $matches[1])),
            fn($status) => $status !== 'reserved'
        );

        return view('admin.tables.create', compact('statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTableSeatRequest $request)
    {
        TableSeat::create($request->safe()->all());

        return redirect()->route('tables.index')->with('success', 'Table Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TableSeat $table)
    {
        $reservations = $table->reservations;

        return view('admin.tables.show', compact('table', 'reservations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TableSeat $table)
    {
        return view('admin.tables.edit', compact('table'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTableSeatRequest $request, TableSeat $table)
    {
        $table->update($request->safe()->all());

        return redirect()->route('tables.index')->with('success', 'Table updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TableSeat $table)
    {
        // Check if the table is reserved
        if ($table->status === 'reserved') {
            return redirect()->route('tables.index')->with('error', 'The table is Reserved it cannot be deleted.');
        }

        $table->delete();

        return redirect()->route('tables.index')->with('success', 'Table deleted successfully!');
    }

    /**
     * Change the status of the table.
     */
    public function changeStatus(TableSeat $table)
    {
        $table->status = $table->status === 'available' ? 'unavailable' : 'available';

        $table->save();

        return redirect()->route('tables.index')->with('success', 'Table status updated successfully!');
    }
}
