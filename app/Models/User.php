<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_APPLICANT = 'applicant';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'registration_number',
        'phone',
        'is_active',
        'deactivated_at',
        'terms_accepted_at',
        'privacy_accepted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    /**
     * Cek apakah user memiliki hak akses Super User / Admin HRD.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Cek apakah user adalah pelamar.
     */
    public function isApplicant(): bool
    {
        return $this->role === self::ROLE_APPLICANT;
    }

    /**
     * Profil lengkap pelamar (1-to-1).
     */
    public function applicantProfile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    /**
     * URL foto profil pelamar jika tersedia.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->applicantProfile?->photo_url;
    }

    /**
     * Riwayat lamaran pekerjaan yang diajukan oleh user.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Notifikasi in-app untuk user.
     */
    public function recruitmentNotifications(): HasMany
    {
        return $this->hasMany(RecruitmentNotification::class)->latest();
    }
}
