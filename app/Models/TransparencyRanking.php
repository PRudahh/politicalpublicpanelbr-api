<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransparencyRanking extends Model
{
    protected $fillable = [
        'municipality_id', 'year', 'month', 'national_rank', 'state_rank',
        'total_score', 'electoral_data_score', 'finance_data_score',
        'works_data_score', 'legislative_data_score', 'executive_data_score', 'portal_score',
    ];

    public function municipality() { return $this->belongsTo(Municipality::class); }
}