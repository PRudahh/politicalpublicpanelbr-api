<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicWork extends Model
{
    protected $fillable = [
        'municipality_id', 'name', 'description', 'category',
        'neighborhood', 'address', 'latitude', 'longitude',
        'contracted_value', 'executed_value', 'execution_percentage',
        'contractor_name', 'contractor_cnpj', 'contract_number', 'bidding_number',
        'start_date', 'expected_end_date', 'actual_end_date',
        'status', 'funding_source', 'source_url',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'expected_end_date' => 'date',
        'actual_end_date'   => 'date',
        'latitude'          => 'float',
        'longitude'         => 'float',
    ];

    public function municipality() { return $this->belongsTo(Municipality::class); }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}