<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Factory akun pelanggan untuk automated test.
 * Tidak dipakai untuk mengisi data tampilan.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Hash kata sandi yang dipakai ulang agar test lebih cepat.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Akun yang dibuat lewat "Masuk dengan Google": terhubung ke Google dan belum punya kata sandi.
     */
    public function lewatGoogle(string $idGoogle = '109876543210987654321'): static
    {
        return $this->state(fn () => ['password_hash' => null])
            ->afterMaking(fn (User $user) => $user->forceFill(['google_id' => $idGoogle]));
    }
}
