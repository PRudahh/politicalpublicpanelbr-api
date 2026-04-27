<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mayor extends Model
{
    protected $fillable = [
        'politician_id', 'mandate_id', 'municipality_id',
        'votes_received', 'votes_percentage', 'coalition',
        'gross_salary', 'number_of_aides', 'is_current',
    ];

    protected $casts = ['is_current' => 'boolean'];

    public function politician()    { return $this->belongsTo(Politician::class); }
    public function mandate()       { return $this->belongsTo(Mandate::class); }
    public function municipality()  { return $this->belongsTo(Municipality::class); }
}