<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'file_name',
        'status',
        'total_records',
        'successful_records',
        'failed_records'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
