<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipApplication extends Model
{
    protected $fillable = ['membership_category_id', 'membership_level_id', 'user_id', 'reference_no', 'full_name', 'email', 'phone', 'address', 'latitude', 'longitude', 'status', 'verification_sent_at'];

    protected function casts(): array
    {
        return ['verification_sent_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }
}
