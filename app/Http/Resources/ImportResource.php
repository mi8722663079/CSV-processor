<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'Message' => $this->status === 'pending' 
                ? 'File successfully uploaded and is queued for processing.' 
                : "{$this->successful_records} has succeeded, {$this->failed_records} has failed",
                
            'check_status_url' => url("/api/import/{$this->id}"),
            'ID' => $this->id,
            'Status' => $this->status,
            'Total records' => $this->total_records,
            'Successful records' => $this->successful_records,
            'Failed records' => $this->failed_records,
            'Created at' => $this->created_at->toDateTimeString(),
        ];
    }
}
