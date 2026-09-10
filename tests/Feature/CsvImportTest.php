<?php

use App\Jobs\ProcessCsvImports;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('it processes csv and skips bad rows', function () {
    Storage::fake('local');
    $csvData = "id,producer,name,quantity,price
\n"
         . "19,FitGear,Noise Cancelling Earbuds,385,138.99\n"
         . "20,iphone,mobile phone,385,1238.99\n"
         . "21,earbuds,Noise Cancelling Earbuds,111,invalid\n"
         . "22,laptop,just some laptop,201,1038.99\n";
    Storage::put('test_import.csv', $csvData);
    $import = Import::create([
        'file_name' => 'test_import.csv',
        'status' => 'pending',
        'total_records' => 0,
        'successful_records' => 0,
        'failed_records' => 0,
    ]);
    ProcessCsvImports::dispatchSync($import);
    $this->assertDatabaseCount('products', 3);
    $this->assertDatabaseHas('products', [
        'external_id' => 22,
        'producer' => 'laptop',
        'name' => 'just some laptop',
        'quantity' => 201,
        'price' => '1038.99',

    ]);
    $this->assertDatabaseHas('imports', [
        'id' => $import->id,
        'status' => 'completed',
        'total_records' => 4,
        'successful_records' => 3,
        'failed_records' => 1,
    ]);
});

test('it rejects files that are not csv', function (){

    $badFile = UploadedFile::fake()->create("document.pdf", 100,"application/pdf");

    $response = $this->postJson('/api/import.store',[
        'csv_file'=> $badFile,
    ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['csv_file']);

});


test('it accepts csv files and patches a job to the queue', function (){
    Queue::fake();
    $positiveFile = UploadedFile::fake()->create("document.csv", 100,"application/csv");

    $response = $this->postJson('/api/import.store',[
        'csv_file'=> $positiveFile,
    ]);

    $this->assertDatabaseHas('imports', [
        'status'=> 'pending',
    ]);

    $response->assertStatus(202);

    Queue::assertPushed(ProcessCsvImports::class);

});
