<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'municipality_id', 'source_sphere', 'source_entity', 'transfer_type',
        'program_name', 'agreement_number', 'object',
        'authorized_value', 'transferred_value', 'execution_percentage',
        'year', 'month', 'source_url',
    ];

    public function municipality() { return $this->belongsTo(Municipality::class); }
}