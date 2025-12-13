<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\GrowBiz\Repositories\AppointmentRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\AppointmentStatus;
use App\Infrastructure\Persistence\Eloquent\GrowBizServiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizServiceProviderModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizAppointmentModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizBookingCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizAvailabilityScheduleModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizAvailabilityExceptionModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizBookingSettingsModel;
use Carbon\Carbon;

class EloquentAppointmentRepository implements AppointmentRepositoryInterface
{
    // Services
    public function getServices(int $userId, bool $activeOnly = true): array
    {
        $query = GrowBizServiceModel::where('user_id', $userId)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get()->map(fn($s) => $this->mapService($s))->toArray();
    }

    public function getServiceById(int $id, int $userId): ?array
    {
        $service = GrowBizServiceModel::where('id', $id)
            ->where('user_id', $userId)
            ->with('providers')
            ->first();

        return $service ? $this->mapService($service) : null;
    }

    public function createService(array $data): array
    {
        $service = GrowBizServiceModel::create($data);
        return $this->mapService($service);
    }

    public function updateService(int $id, int $userId, array $data): ?array
    {
        $service = GrowBizServiceModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$service) return null;

        $service->update($data);
        return $this->mapService($service->fresh());
    }

    public function deleteService(int $id, int $userId): bool
    {
        return GrowBizServiceModel::where('id', $id)->where('user_id', $userId)->delete() > 0;
    }

    public function getServiceCategories(int $userId): array
    {
        return GrowBizServiceModel::where('user_id', $userId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    // Service Providers
    public function getProviders(int $userId, bool $activeOnly = true): array
    {
        $query = GrowBizServiceProviderModel::where('user_id', $userId)
            ->with('services')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get()->map(fn($p) => $this->mapProvider($p))->toArray();
    }

    public function getProviderById(int $id, int $userId): ?array
    {
        $provider = GrowBizServiceProviderModel::where('id', $id)
            ->where('user_id', $userId)
            ->with('services')
            ->first();

        return $provider ? $this->mapProvider($provider) : null;
    }

    public function createProvider(array $data): array
    {
        $provider = GrowBizServiceProviderModel::create($data);
        return $this->mapProvider($provider);
    }

    public function updateProvider(int $id, int $userId, array $data): ?array
    {
        $provider = GrowBizServiceProviderModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$provider) return null;

        $provider->update($data);
        return $this->mapProvider($provider->fresh());
    }

    public function deleteProvider(int $id, int $userId): bool
    {
        return GrowBizServiceProviderModel::where('id', $id)->where('user_id', $userId)->delete() > 0;
    }

    public function assignServicesToProvider(int $providerId, array $serviceIds): void
    {
        $provider = GrowBizServiceProviderModel::find($providerId);
        if ($provider) {
            $provider->services()->sync($serviceIds);
        }
    }

    // Appointments
    public function getAppointments(int $userId, array $filters = []): array
    {
        $query = GrowBizAppointmentModel::where('user_id', $userId)
            ->with(['service', 'provider', 'customer']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (!empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('appointment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('appointment_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('appointment_date', 'desc')->orderBy('start_time', 'desc');

        return $query->get()->map(fn($a) => $this->mapAppointment($a))->toArray();
    }

    public function getAppointmentById(int $id, int $userId): ?array
    {
        $appointment = GrowBizAppointmentModel::where('id', $id)
            ->where('user_id', $userId)
            ->with(['service', 'provider', 'customer', 'reminders'])
            ->first();

        return $appointment ? $this->mapAppointment($appointment) : null;
    }

    public function getAppointmentByReference(string $reference): ?array
    {
        $appointment = GrowBizAppointmentModel::where('booking_reference', $reference)
            ->with(['service', 'provider', 'customer'])
            ->first();

        return $appointment ? $this->mapAppointment($appointment) : null;
    }

    public function createAppointment(array $data): array
    {
        $appointment = GrowBizAppointmentModel::create($data);
        return $this->mapAppointment($appointment->load(['service', 'provider', 'customer']));
    }

    public function updateAppointment(int $id, int $userId, array $data): ?array
    {
        $appointment = GrowBizAppointmentModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$appointment) return null;

        $appointment->update($data);
        return $this->mapAppointment($appointment->fresh(['service', 'provider', 'customer']));
    }

    public function updateAppointmentStatus(int $id, int $userId, AppointmentStatus $status, ?string $reason = null): bool
    {
        $appointment = GrowBizAppointmentModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$appointment) return false;

        $updateData = ['status' => $status];

        if ($status === AppointmentStatus::CANCELLED) {
            $updateData['cancelled_at'] = now();
            $updateData['cancelled_by'] = auth()->id();
            $updateData['cancellation_reason'] = $reason;
        } elseif ($status === AppointmentStatus::CONFIRMED) {
            $updateData['confirmed_at'] = now();
        } elseif ($status === AppointmentStatus::COMPLETED) {
            $updateData['completed_at'] = now();
        }

        $appointment->update($updateData);

        // Update customer stats
        if ($appointment->customer_id) {
            $customer = GrowBizBookingCustomerModel::find($appointment->customer_id);
            if ($customer) {
                if ($status === AppointmentStatus::COMPLETED) {
                    $customer->incrementBookings();
                } elseif ($status === AppointmentStatus::NO_SHOW) {
                    $customer->incrementNoShows();
                } elseif ($status === AppointmentStatus::CANCELLED) {
                    $customer->incrementCancellations();
                }
            }
        }

        return true;
    }

    public function deleteAppointment(int $id, int $userId): bool
    {
        return GrowBizAppointmentModel::where('id', $id)->where('user_id', $userId)->delete() > 0;
    }

    public function getAppointmentsForDate(int $userId, Carbon $date, ?int $providerId = null): array
    {
        $query = GrowBizAppointmentModel::where('user_id', $userId)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->with(['service', 'provider', 'customer']);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        return $query->orderBy('start_time')->get()->map(fn($a) => $this->mapAppointment($a))->toArray();
    }

    public function getAppointmentsForDateRange(int $userId, Carbon $startDate, Carbon $endDate, ?int $providerId = null): array
    {
        $query = GrowBizAppointmentModel::where('user_id', $userId)
            ->whereBetween('appointment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with(['service', 'provider', 'customer']);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        return $query->orderBy('appointment_date')->orderBy('start_time')->get()->map(fn($a) => $this->mapAppointment($a))->toArray();
    }

    public function getUpcomingAppointments(int $userId, int $limit = 10): array
    {
        return GrowBizAppointmentModel::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('appointment_date', '>', now()->format('Y-m-d'))
                    ->orWhere(function ($q2) {
                        $q2->where('appointment_date', now()->format('Y-m-d'))
                            ->where('start_time', '>=', now()->format('H:i:s'));
                    });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['service', 'provider', 'customer'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get()
            ->map(fn($a) => $this->mapAppointment($a))
            ->toArray();
    }

    public function getTodayAppointments(int $userId): array
    {
        return $this->getAppointmentsForDate($userId, now());
    }

    // Customers
    public function getCustomers(int $userId, ?string $search = null): array
    {
        $query = GrowBizBookingCustomerModel::where('user_id', $userId)->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(fn($c) => $this->mapCustomer($c))->toArray();
    }

    public function getCustomerById(int $id, int $userId): ?array
    {
        $customer = GrowBizBookingCustomerModel::where('id', $id)
            ->where('user_id', $userId)
            ->with(['appointments' => fn($q) => $q->latest('appointment_date')->limit(10)])
            ->first();

        return $customer ? $this->mapCustomer($customer) : null;
    }

    public function createCustomer(array $data): array
    {
        $customer = GrowBizBookingCustomerModel::create($data);
        return $this->mapCustomer($customer);
    }

    public function updateCustomer(int $id, int $userId, array $data): ?array
    {
        $customer = GrowBizBookingCustomerModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$customer) return null;

        $customer->update($data);
        return $this->mapCustomer($customer->fresh());
    }

    public function deleteCustomer(int $id, int $userId): bool
    {
        return GrowBizBookingCustomerModel::where('id', $id)->where('user_id', $userId)->delete() > 0;
    }

    public function findOrCreateCustomer(int $userId, array $data): array
    {
        $customer = GrowBizBookingCustomerModel::where('user_id', $userId)
            ->where(function ($q) use ($data) {
                if (!empty($data['email'])) {
                    $q->where('email', $data['email']);
                }
                if (!empty($data['phone'])) {
                    $q->orWhere('phone', $data['phone']);
                }
            })
            ->first();

        if ($customer) {
            // Update name if provided
            if (!empty($data['name']) && $customer->name !== $data['name']) {
                $customer->update(['name' => $data['name']]);
            }
            return $this->mapCustomer($customer);
        }

        return $this->createCustomer(array_merge($data, ['user_id' => $userId]));
    }

    // Availability
    public function getSchedule(int $userId, ?int $providerId = null): array
    {
        $query = GrowBizAvailabilityScheduleModel::where('user_id', $userId);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        } else {
            $query->whereNull('provider_id');
        }

        $schedules = $query->orderBy('day_of_week')->get();

        // Return as array indexed by day_of_week
        $result = [];
        foreach ($schedules as $schedule) {
            $result[$schedule->day_of_week] = [
                'id' => $schedule->id,
                'day_of_week' => $schedule->day_of_week,
                'day_name' => $schedule->day_name,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'is_available' => $schedule->is_available,
                'formatted_hours' => $schedule->formatted_hours,
            ];
        }

        return $result;
    }

    public function saveSchedule(int $userId, ?int $providerId, array $schedule): void
    {
        foreach ($schedule as $dayOfWeek => $hours) {
            GrowBizAvailabilityScheduleModel::updateOrCreate(
                [
                    'user_id' => $userId,
                    'provider_id' => $providerId,
                    'day_of_week' => $dayOfWeek,
                ],
                [
                    'start_time' => $hours['start_time'] ?? '09:00',
                    'end_time' => $hours['end_time'] ?? '17:00',
                    'is_available' => $hours['is_available'] ?? true,
                ]
            );
        }
    }

    public function getExceptions(int $userId, ?int $providerId, Carbon $startDate, Carbon $endDate): array
    {
        $query = GrowBizAvailabilityExceptionModel::where('user_id', $userId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($providerId) {
            $query->where(function ($q) use ($providerId) {
                $q->where('provider_id', $providerId)->orWhereNull('provider_id');
            });
        } else {
            $query->whereNull('provider_id');
        }

        return $query->orderBy('date')->get()->map(fn($e) => [
            'id' => $e->id,
            'date' => $e->date->format('Y-m-d'),
            'type' => $e->type,
            'type_label' => $e->getTypeLabel(),
            'start_time' => $e->start_time,
            'end_time' => $e->end_time,
            'reason' => $e->reason,
        ])->toArray();
    }

    public function createException(array $data): array
    {
        $exception = GrowBizAvailabilityExceptionModel::create($data);
        return [
            'id' => $exception->id,
            'date' => $exception->date->format('Y-m-d'),
            'type' => $exception->type,
            'start_time' => $exception->start_time,
            'end_time' => $exception->end_time,
            'reason' => $exception->reason,
        ];
    }

    public function deleteException(int $id, int $userId): bool
    {
        return GrowBizAvailabilityExceptionModel::where('id', $id)->where('user_id', $userId)->delete() > 0;
    }

    public function getAvailableSlots(int $userId, int $serviceId, Carbon $date, ?int $providerId = null): array
    {
        $service = GrowBizServiceModel::find($serviceId);
        if (!$service) return [];

        $settings = $this->getSettings($userId);
        $slotDuration = $settings['slot_duration_minutes'] ?? 30;
        $serviceDuration = $service->duration_minutes + $service->buffer_minutes;

        // Get schedule for this day
        $dayOfWeek = $date->dayOfWeek;
        $schedule = GrowBizAvailabilityScheduleModel::where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($providerId) {
                if ($providerId) {
                    $q->where('provider_id', $providerId)->orWhereNull('provider_id');
                } else {
                    $q->whereNull('provider_id');
                }
            })
            ->where('is_available', true)
            ->first();

        if (!$schedule) return [];

        // Check for exceptions
        $exception = GrowBizAvailabilityExceptionModel::where('user_id', $userId)
            ->where('date', $date->format('Y-m-d'))
            ->where(function ($q) use ($providerId) {
                if ($providerId) {
                    $q->where('provider_id', $providerId)->orWhereNull('provider_id');
                } else {
                    $q->whereNull('provider_id');
                }
            })
            ->first();

        if ($exception && $exception->type === 'closed') {
            return [];
        }

        $startTime = $exception && $exception->start_time ? $exception->start_time : $schedule->start_time;
        $endTime = $exception && $exception->end_time ? $exception->end_time : $schedule->end_time;

        // Get existing appointments for this date
        $existingAppointments = GrowBizAppointmentModel::where('user_id', $userId)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->when($providerId, fn($q) => $q->where('provider_id', $providerId))
            ->get();

        // Generate time slots
        $slots = [];
        $current = Carbon::parse($date->format('Y-m-d') . ' ' . $startTime);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $endTime);

        while ($current->copy()->addMinutes($serviceDuration)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($serviceDuration);
            $isAvailable = true;

            // Check if slot conflicts with existing appointments
            foreach ($existingAppointments as $apt) {
                $aptStart = Carbon::parse($date->format('Y-m-d') . ' ' . $apt->start_time);
                $aptEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $apt->end_time);

                if ($current->lt($aptEnd) && $slotEnd->gt($aptStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            // Check minimum booking notice
            $minNotice = $settings['min_booking_notice_hours'] ?? 2;
            if ($current->lt(now()->addHours($minNotice))) {
                $isAvailable = false;
            }

            $slots[] = [
                'time' => $current->format('H:i'),
                'formatted' => $current->format('g:i A'),
                'available' => $isAvailable,
            ];

            $current->addMinutes($slotDuration);
        }

        return $slots;
    }

    // Settings
    public function getSettings(int $userId): ?array
    {
        $settings = GrowBizBookingSettingsModel::where('user_id', $userId)->first();

        if (!$settings) {
            // Return defaults
            return [
                'business_name' => null,
                'booking_page_description' => null,
                'booking_page_slug' => null,
                'timezone' => 'Africa/Lusaka',
                'slot_duration_minutes' => 30,
                'min_booking_notice_hours' => 2,
                'max_booking_advance_days' => 60,
                'require_approval' => false,
                'allow_cancellation' => true,
                'cancellation_notice_hours' => 24,
                'send_confirmation_email' => true,
                'send_reminder_sms' => false,
                'send_reminder_whatsapp' => false,
                'reminder_timings' => ['24_hours'],
                'collect_payment_online' => false,
                'deposit_percentage' => null,
                'cancellation_policy' => null,
                'custom_fields' => null,
                'logo' => null,
                'primary_color' => '#3b82f6',
                'booking_url' => url("/book/{$userId}"),
            ];
        }

        return [
            'id' => $settings->id,
            'business_name' => $settings->business_name,
            'booking_page_description' => $settings->booking_page_description,
            'booking_page_slug' => $settings->booking_page_slug,
            'timezone' => $settings->timezone,
            'slot_duration_minutes' => $settings->slot_duration_minutes,
            'min_booking_notice_hours' => $settings->min_booking_notice_hours,
            'max_booking_advance_days' => $settings->max_booking_advance_days,
            'require_approval' => $settings->require_approval,
            'allow_cancellation' => $settings->allow_cancellation,
            'cancellation_notice_hours' => $settings->cancellation_notice_hours,
            'send_confirmation_email' => $settings->send_confirmation_email,
            'send_reminder_sms' => $settings->send_reminder_sms,
            'send_reminder_whatsapp' => $settings->send_reminder_whatsapp,
            'reminder_timings' => $settings->reminder_timings,
            'collect_payment_online' => $settings->collect_payment_online,
            'deposit_percentage' => $settings->deposit_percentage,
            'cancellation_policy' => $settings->cancellation_policy,
            'custom_fields' => $settings->custom_fields,
            'logo' => $settings->logo,
            'primary_color' => $settings->primary_color,
            'booking_url' => $settings->booking_url,
        ];
    }

    public function saveSettings(int $userId, array $data): array
    {
        $settings = GrowBizBookingSettingsModel::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        return $this->getSettings($userId);
    }

    // Statistics
    public function getStatistics(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $appointments = GrowBizAppointmentModel::where('user_id', $userId)
            ->whereBetween('appointment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        $total = (clone $appointments)->count();
        $completed = (clone $appointments)->where('status', 'completed')->count();
        $cancelled = (clone $appointments)->where('status', 'cancelled')->count();
        $noShows = (clone $appointments)->where('status', 'no_show')->count();
        $pending = (clone $appointments)->where('status', 'pending')->count();
        $confirmed = (clone $appointments)->where('status', 'confirmed')->count();

        $revenue = (clone $appointments)->where('status', 'completed')->sum('price');
        $collected = (clone $appointments)->where('status', 'completed')->sum('amount_paid');

        $todayCount = GrowBizAppointmentModel::where('user_id', $userId)
            ->where('appointment_date', now()->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        $upcomingCount = GrowBizAppointmentModel::where('user_id', $userId)
            ->where('appointment_date', '>', now()->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // Popular services
        $popularServices = GrowBizAppointmentModel::where('user_id', $userId)
            ->whereBetween('appointment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('service_id, COUNT(*) as count')
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(5)
            ->with('service')
            ->get()
            ->map(fn($a) => [
                'service_id' => $a->service_id,
                'service_name' => $a->service?->name ?? 'Unknown',
                'count' => $a->count,
            ])
            ->toArray();

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'no_shows' => $noShows,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'cancellation_rate' => $total > 0 ? round(($cancelled / $total) * 100, 1) : 0,
            'no_show_rate' => $total > 0 ? round(($noShows / $total) * 100, 1) : 0,
            'revenue' => $revenue,
            'collected' => $collected,
            'outstanding' => $revenue - $collected,
            'today_count' => $todayCount,
            'upcoming_count' => $upcomingCount,
            'popular_services' => $popularServices,
        ];
    }

    // Mapping helpers
    private function mapService($service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration_minutes' => $service->duration_minutes,
            'buffer_minutes' => $service->buffer_minutes,
            'total_duration' => $service->total_duration,
            'price' => (float) $service->price,
            'currency' => $service->currency,
            'formatted_price' => $service->formatted_price,
            'formatted_duration' => $service->formatted_duration,
            'category' => $service->category,
            'color' => $service->color,
            'is_active' => $service->is_active,
            'allow_online_booking' => $service->allow_online_booking,
            'max_bookings_per_slot' => $service->max_bookings_per_slot,
            'sort_order' => $service->sort_order,
            'providers' => $service->relationLoaded('providers') 
                ? $service->providers->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->toArray() 
                : [],
        ];
    }

    private function mapProvider($provider): array
    {
        return [
            'id' => $provider->id,
            'name' => $provider->name,
            'email' => $provider->email,
            'phone' => $provider->phone,
            'bio' => $provider->bio,
            'avatar' => $provider->avatar,
            'color' => $provider->color,
            'initials' => $provider->initials,
            'is_active' => $provider->is_active,
            'accept_online_bookings' => $provider->accept_online_bookings,
            'employee_id' => $provider->employee_id,
            'services' => $provider->relationLoaded('services')
                ? $provider->services->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray()
                : [],
        ];
    }

    private function mapAppointment($appointment): array
    {
        return [
            'id' => $appointment->id,
            'booking_reference' => $appointment->booking_reference,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'formatted_date' => $appointment->formatted_date,
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'formatted_time' => $appointment->formatted_time,
            'duration_minutes' => $appointment->duration_minutes,
            'status' => $appointment->status->value,
            'status_label' => $appointment->status->label(),
            'status_color' => $appointment->status->color(),
            'status_bg_color' => $appointment->status->bgColor(),
            'price' => (float) $appointment->price,
            'currency' => $appointment->currency,
            'formatted_price' => $appointment->formatted_price,
            'payment_status' => $appointment->payment_status,
            'amount_paid' => (float) $appointment->amount_paid,
            'balance_due' => $appointment->balance_due,
            'customer_notes' => $appointment->customer_notes,
            'internal_notes' => $appointment->internal_notes,
            'booking_source' => $appointment->booking_source?->value,
            'booking_source_label' => $appointment->booking_source?->label(),
            'cancellation_reason' => $appointment->cancellation_reason,
            'is_upcoming' => $appointment->is_upcoming,
            'is_today' => $appointment->is_today,
            'service' => $appointment->service ? [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
                'color' => $appointment->service->color,
            ] : null,
            'provider' => $appointment->provider ? [
                'id' => $appointment->provider->id,
                'name' => $appointment->provider->name,
                'color' => $appointment->provider->color,
                'initials' => $appointment->provider->initials,
            ] : null,
            'customer' => $appointment->customer ? [
                'id' => $appointment->customer->id,
                'name' => $appointment->customer->name,
                'email' => $appointment->customer->email,
                'phone' => $appointment->customer->phone,
                'initials' => $appointment->customer->initials,
            ] : null,
            'created_at' => $appointment->created_at->toISOString(),
        ];
    }

    private function mapCustomer($customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'notes' => $customer->notes,
            'initials' => $customer->initials,
            'total_bookings' => $customer->total_bookings,
            'no_shows' => $customer->no_shows,
            'cancellations' => $customer->cancellations,
            'reliability_score' => $customer->reliability_score,
            'last_visit_at' => $customer->last_visit_at?->toISOString(),
            'recent_appointments' => $customer->relationLoaded('appointments')
                ? $customer->appointments->map(fn($a) => $this->mapAppointment($a))->toArray()
                : [],
        ];
    }
}
