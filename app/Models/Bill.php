<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'municipality_id', 'legislator_id', 'external_id', 'number', 'year',
        'title', 'description', 'category', 'status',
        'submitted_at', 'voted_at', 'votes_for', 'votes_against', 'abstentions',
        'source_url',
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'voted_at'     => 'date',
    ];

    public function municipality()  { return $this->belongsTo(Municipality::class); }
    public function legislator()    { return $this->belongsTo(Legislator::class); }
}