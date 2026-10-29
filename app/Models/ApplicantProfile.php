<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ApplicantProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nik',
        'nickname',
        'birth_place',
        'birth_date',
        'gender',
        'photo_path',
        'address_ktp',
        'address_domicile',
        'same_as_ktp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'same_as_ktp' => 'boolean',
        ];
    }

    /**
     * URL publik foto profil jika berkas ada di storage publik.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/'.$this->photo_path);
        }

        return null;
    }

    /**
     * Akun user pemilik profil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Riwayat pendidikan pelamar.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(ApplicantEducation::class);
    }

    /**
     * Riwayat sertifikasi/pelatihan pelamar.
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(ApplicantCertification::class);
    }

    /**
     * Data surat tanda registrasi / lisensi profesi pelamar.
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(ApplicantLicense::class);
    }

    /**
     * Riwayat pengalaman kerja pelamar.
     */
    public function workExperiences(): HasMany
    {
        return $this->hasMany(ApplicantWorkExperience::class);
    }
}
