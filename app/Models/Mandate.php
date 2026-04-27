<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mandate extends Model
{
    protected $fillable = [
        'municipality_id', 'level', 'election_year', 'start_date', 'end_date', 'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /** Retorna o mês do mandato (1-48) para uma data dada */
    public function getMandateMonth(\DateTimeInterface $date): int
    {
        $diff = $this->start_date->diffInMonths($date);
        return min(max(1, (int) $diff + 1), 48);
    }
}