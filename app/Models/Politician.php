<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Politician extends Model
{
    protected $fillable = [
        'tse_candidate_id', 'cpf_hash', 'name', 'social_name', 'photo_url',
        'birthdate', 'education_level', 'education_description', 'biography',
        'party', 'party_abbreviation', 'social_media',
        'institutional_email', 'institutional_phone', 'website',
    ];

    protected $casts = [
        'social_media' => 'array',
        'birthdate'    => 'date',
    ];

    protected $hidden = ['cpf_hash'];

    public function mayor()
    {
        return $this->hasOne(Mayor::class);
    }

    public function secretary()
    {
        return $this->hasOne(Secretary::class);
    }

    public function legislator()
    {
        return $this->hasOne(Legislator::class);
    }
}