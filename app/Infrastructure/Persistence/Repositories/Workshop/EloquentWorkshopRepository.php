<?php

namespace App\Infrastructure\Persistence\Repositories\Workshop;

use App\Domain\Workshop\Entities\Workshop;
use App\Domain\Workshop\Repositories\WorkshopRepository;
use App\Domain\Workshop\ValueObjects\WorkshopCategory;
use App\Domain\Workshop\ValueObjects\DeliveryFormat;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopModel;
use DateTimeImmutable;

class EloquentWorkshopRepository implements WorkshopRepository
{
    public function save(Workshop $workshop): Workshop
    {
        $data = [
            'title' => $workshop->title(),
            'slug' => $workshop->slug(),
            'description' => $workshop->description(),
            'category' => $workshop->category()->value(),
            'delivery_format' => $workshop->deliveryFormat()->value(),
            'price' => $workshop->price(),
            'max_participants' => $workshop->maxParticipants(),
            'lp_reward' => $workshop->lpReward(),
            'bp_reward' => $workshop->bpReward(),
            'start_date' => $workshop->startDate()->format('Y-m-d H:i:s'),
            'end_date' => $workshop->endDate()->format('Y-m-d H:i:s'),
            'location' => $workshop->location(),
            'meeting_link' => $workshop->meetingLink(),
            'status' => $workshop->status(),
        ];

        if ($workshop->id()) {
            $model = WorkshopModel::findOrFail($workshop->id());
            $model->update($data);
        } else {
            $model = WorkshopModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?Workshop
    {
        $model = WorkshopModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Workshop
    {
        $model = WorkshopModel::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return WorkshopModel::orderBy('start_date', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findPublished(): array
    {
        return WorkshopModel::where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByCategory(string $category): array
    {
        return WorkshopModel::where('category', $category)
            ->where('status', 'published')
            ->orderBy('start_date')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findUpcoming(): array
    {
        return WorkshopModel::where('status', 'published')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->limit(10)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    private function toDomainEntity(WorkshopModel $model): Workshop
    {
        return new Workshop(
            id: $model->id,
            title: $model->title,
            slug: $model->slug,
            description: $model->description,
            category: WorkshopCategory::fromString($model->category),
            deliveryFormat: DeliveryFormat::fromString($model->delivery_format),
            price: $model->price,
            maxParticipants: $model->max_participants,
            lpReward: $model->lp_reward,
            bpReward: $model->bp_reward,
            startDate: new DateTimeImmutable($model->start_date),
            endDate: new DateTimeImmutable($model->end_date),
            location: $model->location,
            meetingLink: $model->meeting_link,
            requirements: $model->requirements,
            learningOutcomes: $model->learning_outcomes,
            instructorName: $model->instructor_name,
            instructorBio: $model->instructor_bio,
            featuredImage: $model->featured_image,
            status: $model->status,
            createdBy: $model->created_by,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
