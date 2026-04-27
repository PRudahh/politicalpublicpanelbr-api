<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $fillable = [
        'municipality_id', 'year', 'month', 'type', 'category', 'subcategory',
        'budgeted_value', 'committed_value', 'liquidated_value', 'paid_value', 'source',
    ];

    public function municipality() { return $this->belongsTo(Municipality::class); }
}