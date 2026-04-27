<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legislator extends Model
{
    protected $fillable = [
        'politician_id', 'mandate_id', 'municipality_id',
        'votes_received', 'votes_percentage', 'gross_salary', 'office_budget',
        'number_of_aides', 'sessions_present', 'sessions_absent',
        'sessions_justified_absent', 'bills_submitted', 'bills_approved',
        'bills_rejected', 'bills_pending', 'executive_alignment_pct',
        'productivity_index', 'is_current',
    ];

    protected $casts = ['is_current' => 'boolean'];

    public function politician()    { return $this->belongsTo(Politician::class); }
    public function mandate()       { return $this->belongsTo(Mandate::class); }
    public function municipality()  { return $this->belongsTo(Municipality::class); }
    public function bills()         { return $this->hasMany(Bill::class); }

    /** Percentual de presença nas sessões */
    public function getAttendancePercentageAttribute(): ?float
    {
        $total = $this->sessions_present + $this->sessions_absent;
        return $total > 0 ? round($this->sessions_present / $total * 100, 1) : null;
    }
}