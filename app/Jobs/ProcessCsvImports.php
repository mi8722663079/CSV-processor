<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

class ProcessCsvImports implements ShouldQueue
{
    use Queueable;
    private int $successfulRecords = 0;
    private int $failedRecords = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Import $import,
    )
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SimpleExcelReader::create(Storage::path($this->import->file_name))->getRows()->chunk(500)->each(function ($Chunk) {
            $this->processChunk($Chunk);
        });
        $this->import->update([
            'status' => 'completed',
            'successful_records' => $this->successfulRecords,
            'failed_records' => $this->failedRecords,
            'total_records' => $this->successfulRecords + $this->failedRecords,
        ]);
    }       
    private function processChunk($chunk): void
    {
            $data = [];
            foreach ($chunk as $row) {
                $validator = Validator::make($row,[
                    'id' => 'required|integer',
                    'producer' => 'required|string',
                    'name' => 'required|string',
                    'quantity' => 'required|integer',
                'price' => 'required|numeric',
            ]);
                if ($validator->fails()) {
                    $this->failedRecords += 1;
                    continue;
                }
                $this->successfulRecords += 1; 
                $validated = $validator->validated();
                $data[] = [
                    'external_id' => $validated['id'],
                    'producer' => $validated['producer'],
                    'name' => $validated['name'],
                    'quantity' => $validated['quantity'],
                    'price' => $validated['price'],
                    'imports_id' => $this->import->id,
                ];
            }
            if (!empty($data)) {
                Product::insert($data);
            }
    }
}   