<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'level', 'fee'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'fee' => 'decimal:2'];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(MembershipApplication::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
