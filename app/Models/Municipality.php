<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Municipality extends Model
{
    protected $fillable = [
        'ibge_code', 'name', 'uf', 'state_name', 'region',
        'latitude', 'longitude', 'population', 'gdp_per_capita', 'idh',
        'annual_budget', 'fiscal_health_status', 'transparency_score',
        'transparency_level', 'has_open_data_portal', 'transparency_portal_url',
        'available_modules',
    ];

    protected $casts = [
        'available_modules'    => 'array',
        'has_open_data_portal' => 'boolean',
        'latitude'             => 'float',
        'longitude'            => 'float',
    ];

    // ── Relacionamentos ───────────────────────────────────────────────

    public function mandates(): HasMany
    {
        return $this->hasMany(Mandate::class);
    }

    public function currentMandate(): HasOne
    {
        return $this->hasOne(Mandate::class)->where('is_current', true);
    }

    public function mayors(): HasMany
    {
        return $this->hasMany(Mayor::class);
    }

    public function currentMayor(): HasOne
    {
        return $this->hasOne(Mayor::class)->where('is_current', true)->with('politician');
    }

    public function secretaries(): HasMany
    {
        return $this->hasMany(Secretary::class);
    }

    public function currentSecretaries(): HasMany
    {
        return $this->hasMany(Secretary::class)->where('is_current', true)->with('politician');
    }

    public function legislators(): HasMany
    {
        return $this->hasMany(Legislator::class);
    }

    public function currentLegislators(): HasMany
    {
        return $this->hasMany(Legislator::class)->where('is_current', true)->with('politician');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function publicWorks(): HasMany
    {
        return $this->hasMany(PublicWork::class);
    }

    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function timelineSnapshots(): HasMany
    {
        return $this->hasMany(TimelineSnapshot::class)->orderBy('year')->orderBy('month');
    }

    public function transparencyRankings(): HasMany
    {
        return $this->hasMany(TransparencyRanking::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeByUf($query, string $uf)
    {
        return $query->where('uf', strtoupper($uf));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'ilike', "%{$term}%")
                     ->orWhere('ibge_code', 'like', "%{$term}%");
    }
}