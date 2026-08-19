<?php

namespace App\Http\Controllers;

use App\Http\Requests\Import\Store;
use App\Http\Resources\ImportResource;
use App\Jobs\ProcessCsvImports;
use App\Models\Import;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function store(Store $request){
        $request->validated();
        $file  = $request->file('csv_file')->store('imports');
        $import = Import::create([
            'file_name' => $file,
            'total_records' => 0,
            'successful_records' => 0,
            'failed_records' => 0,
            'status' => 'pending',
        ]);
        ProcessCsvImports::dispatch($import);
        return response()->json([
            'Import Data' => new ImportResource($import)
        ],202);
    }

    public function show(Import $import){
        
        return response()->json([
            'Import Data' => new ImportResource($import)

        ],200);
    }
    
}
