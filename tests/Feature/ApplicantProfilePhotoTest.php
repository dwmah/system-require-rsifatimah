<?php

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function createApplicantUser(array $userAttributes = [], array $profileAttributes = []): User
    {
        static $sequence = 1;
        $seq = $sequence++;

        $user = User::create(array_merge([
            'name' => 'Pelamar Foto '.$seq,
            'email' => "pelamar.foto{$seq}@example.com",
            'phone' => sprintf('62812345%05d', $seq),
            'registration_number' => sprintf('PLM-2026-%06d', $seq),
            'password' => 'Password123',
            'is_active' => true,
        ], $userAttributes));

        ApplicantProfile::create(array_merge([
            'user_id' => $user->id,
            'nik' => sprintf('3510123456%06d', $seq),
            'same_as_ktp' => false,
        ], $profileAttributes));

        return $user->fresh(['applicantProfile']);
    }

    public function test_guest_cannot_upload_profile_photo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post(route('profile.photo.update'), [
            'photo' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_applicant_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = $this->createApplicantUser();

        $file = UploadedFile::fake()->image('pas_foto.jpg', 300, 400);

        $response = $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => $file,
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Pas foto profil berhasil diperbarui.');

        $user->refresh();
        $this->assertNotNull($user->applicantProfile->photo_path);
        Storage::disk('public')->assertExists($user->applicantProfile->photo_path);
        $this->assertNotNull($user->profile_photo_url);
    }

    public function test_photo_upload_requires_valid_image(): void
    {
        Storage::fake('public');

        $user = $this->createApplicantUser();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => $file,
        ]);

        $response->assertSessionHasErrors(['photo']);
        $user->refresh();
        $this->assertNull($user->applicantProfile->photo_path);
    }

    public function test_photo_upload_rejects_files_larger_than_5mb(): void
    {
        Storage::fake('public');

        $user = $this->createApplicantUser();

        // 6MB image (exceeds 5MB)
        $file = UploadedFile::fake()->image('huge.png')->size(6144);

        $response = $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => $file,
        ]);

        $response->assertSessionHasErrors(['photo']);
        $user->refresh();
        $this->assertNull($user->applicantProfile->photo_path);
    }

    public function test_uploading_new_photo_deletes_old_photo(): void
    {
        Storage::fake('public');

        $user = $this->createApplicantUser();

        $oldFile = UploadedFile::fake()->image('old_photo.jpg');
        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => $oldFile,
        ]);

        $user->refresh();
        $oldPath = $user->applicantProfile->photo_path;
        Storage::disk('public')->assertExists($oldPath);

        // Upload new photo
        $newFile = UploadedFile::fake()->image('new_photo.png');
        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => $newFile,
        ]);

        $user->refresh();
        $newPath = $user->applicantProfile->photo_path;

        $this->assertNotEquals($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_applicant_can_delete_profile_photo(): void
    {
        Storage::fake('public');

        $user = $this->createApplicantUser();

        $file = UploadedFile::fake()->image('my_photo.jpg');
        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => $file,
        ]);

        $user->refresh();
        $photoPath = $user->applicantProfile->photo_path;
        Storage::disk('public')->assertExists($photoPath);

        // Delete photo
        $response = $this->actingAs($user)->delete(route('profile.photo.destroy'));

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'Foto profil berhasil dihapus.');

        $user->refresh();
        $this->assertNull($user->applicantProfile->photo_path);
        Storage::disk('public')->assertMissing($photoPath);
    }
}
