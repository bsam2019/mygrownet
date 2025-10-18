<?php

namespace Database\Factories;

use App\Models\IdVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdVerificationFactory extends Factory
{
    protected $model = IdVerification::class;

    public function definition(): array
    {
        $documentType = $this->faker->randomElement([
            IdVerification::DOCUMENT_NATIONAL_ID,
            IdVerification::DOCUMENT_PASSPORT,
            IdVerification::DOCUMENT_DRIVERS_LICENSE,
        ]);

        return [
            'user_id' => User::factory(),
            'document_type' => $documentType,
            'document_number' => $this->generateDocumentNumber($documentType),
            'document_front_path' => 'id-verifications/' . $this->faker->uuid() . '_front.jpg',
            'document_back_path' => $this->faker->optional(0.7)->passthrough('id-verifications/' . $this->faker->uuid() . '_back.jpg'),
            'selfie_path' => $this->faker->optional(0.5)->passthrough('id-verifications/' . $this->faker->uuid() . '_selfie.jpg'),
            'status' => $this->faker->randomElement([
                IdVerification::STATUS_PENDING,
                IdVerification::STATUS_APPROVED,
                IdVerification::STATUS_REJECTED,
                IdVerification::STATUS_EXPIRED,
            ]),
            'rejection_reason' => null,
            'submitted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'expires_at' => null,
            'verification_data' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IdVerification::STATUS_PENDING,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'expires_at' => null,
            'rejection_reason' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IdVerification::STATUS_APPROVED,
            'reviewed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'reviewed_by' => User::factory(),
            'expires_at' => now()->addYears(2),
            'rejection_reason' => null,
            'verification_data' => [
                'confidence_score' => $this->faker->randomFloat(2, 0.8, 1.0),
                'face_match_score' => $this->faker->randomFloat(2, 0.85, 1.0),
                'document_quality' => 'high',
            ],
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IdVerification::STATUS_REJECTED,
            'reviewed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'reviewed_by' => User::factory(),
            'expires_at' => null,
            'rejection_reason' => $this->faker->randomElement([
                'Document image is not clear enough',
                'Document appears to be tampered with',
                'Selfie does not match document photo',
                'Document has expired',
                'Invalid document type for verification',
            ]),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IdVerification::STATUS_EXPIRED,
            'reviewed_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'reviewed_by' => User::factory(),
            'expires_at' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'rejection_reason' => null,
        ]);
    }

    public function nationalId(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => IdVerification::DOCUMENT_NATIONAL_ID,
            'document_number' => $this->generateDocumentNumber(IdVerification::DOCUMENT_NATIONAL_ID),
            'document_back_path' => 'id-verifications/' . $this->faker->uuid() . '_back.jpg',
        ]);
    }

    public function passport(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => IdVerification::DOCUMENT_PASSPORT,
            'document_number' => $this->generateDocumentNumber(IdVerification::DOCUMENT_PASSPORT),
            'document_back_path' => null, // Passports typically don't have a back image
        ]);
    }

    public function driversLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => IdVerification::DOCUMENT_DRIVERS_LICENSE,
            'document_number' => $this->generateDocumentNumber(IdVerification::DOCUMENT_DRIVERS_LICENSE),
            'document_back_path' => 'id-verifications/' . $this->faker->uuid() . '_back.jpg',
        ]);
    }

    public function withSelfie(): static
    {
        return $this->state(fn (array $attributes) => [
            'selfie_path' => 'id-verifications/' . $this->faker->uuid() . '_selfie.jpg',
        ]);
    }

    public function withoutSelfie(): static
    {
        return $this->state(fn (array $attributes) => [
            'selfie_path' => null,
        ]);
    }

    public function recentlySubmitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => $this->faker->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    public function oldSubmission(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => $this->faker->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }

    private function generateDocumentNumber(string $documentType): string
    {
        return match ($documentType) {
            IdVerification::DOCUMENT_NATIONAL_ID => sprintf(
                '%06d/%02d/%d',
                $this->faker->numberBetween(100000, 999999),
                $this->faker->numberBetween(10, 99),
                $this->faker->numberBetween(1, 9)
            ),
            IdVerification::DOCUMENT_PASSPORT => sprintf(
                '%s%07d',
                $this->faker->randomElement(['ZM', 'AB', 'CD', 'EF']),
                $this->faker->numberBetween(1000000, 9999999)
            ),
            IdVerification::DOCUMENT_DRIVERS_LICENSE => sprintf(
                'DL%08d',
                $this->faker->numberBetween(10000000, 99999999)
            ),
            default => $this->faker->bothify('??######'),
        };
    }
}