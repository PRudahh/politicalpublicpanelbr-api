<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    protected $fillable = [
        'politician_id', 'municipality_id', 'secretariat_name', 'secretariat_acronym',
        'appointment_date', 'employment_type', 'gross_salary', 'is_current',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'is_current'        => 'boolean',
    ];

    public function politician()    { return $this->belongsTo(Politician::class); }
    public function municipality()  { return $this->belongsTo(Municipality::class); }
}