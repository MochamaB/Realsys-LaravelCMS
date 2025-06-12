<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'id_passport_number',
        'membership_number',
        'date_of_birth',
        'postal_address',
        'mobile_number',
        'gender',
        'ethnicity_id',
        'special_status_id',
        'ncpwd_number',
        'religion_id',
        'mobile_provider_id',
        'county_id',
        'constituency_id',
        'ward_id',
        'enlisting_date',
        'recruiting_person',
        'profile_type_id',
        'additional_data',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enlisting_date' => 'date',
        'additional_data' => 'array',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile type of the profile.
     */
    public function profileType(): BelongsTo
    {
        return $this->belongsTo(ProfileType::class);
    }

    /**
     * Get the ethnicity of the profile.
     */
    public function ethnicity(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class);
    }

    /**
     * Get the special status of the profile.
     */
    public function specialStatus(): BelongsTo
    {
        return $this->belongsTo(SpecialStatus::class);
    }

    /**
     * Get the religion of the profile.
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    /**
     * Get the mobile provider of the profile.
     */
    public function mobileProvider(): BelongsTo
    {
        return $this->belongsTo(MobileProvider::class);
    }

    /**
     * Get the county of the profile.
     */
    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    /**
     * Get the constituency of the profile.
     */
    public function constituency(): BelongsTo
    {
        return $this->belongsTo(Constituency::class);
    }

    /**
     * Get the ward of the profile.
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Get the membership record for this profile.
     */
    public function membership()
    {
        return $this->hasOne(Membership::class, 'user_id', 'user_id');
    }
}
