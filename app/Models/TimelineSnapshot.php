<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineSnapshot extends Model
{
    protected $fillable = [
        'municipality_id', 'mandate_id', 'year', 'month', 'mandate_month',
        'total_revenue', 'total_expenditure', 'total_transfers',
        'works_in_progress', 'works_completed', 'works_delayed',
        'bills_approved', 'bills_submitted', 'highlights',
    ];

    protected $casts = ['highlights' => 'array'];

    public function municipality()  { return $this->belongsTo(Municipality::class); }
    public function mandate()       { return $this->belongsTo(Mandate::class); }
}