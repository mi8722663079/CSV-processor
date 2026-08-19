<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'import_id',
        'id',
        'name',
        'price',
        'quantity'
    ];
    public function import(){
        return $this->belongsTo(Import::class);
    }
}
