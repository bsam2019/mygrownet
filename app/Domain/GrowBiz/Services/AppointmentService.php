<?php

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Repositories\AppointmentRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\AppointmentStatus;
use Carbon\Carbon;

class AppointmentService
{
    public function __construct(
        private AppointmentRepositoryInterface $repository
    ) {}

    // Services
    public function getServices(int $userId, bool $activeOnly = true): array
    {
        return $this->repository->getServices($userId, $activeOnly);
    }

    public function getServiceById(int $id, int $userId): ?array
    {
        return $this->repository->getServiceById($id, $userId);
    }

    public function createService(int $userId, array $data): array
    {
        $data['user_id'] = $userId;
        return $this->repository->createService($data);
    }

    public function updateService(int $id, int $userId, array $data): ?array
    {
        return $this->repository->updateService($id, $userId, $data);
    }

    public function deleteService(int $id, int $userId): bool
    {
        return $this->repository->deleteService($id, $userId);
    }

    public function getServiceCategories(int $userId): array
    {
        return $this->repository->getServiceCategories($userId);
    }

    // Providers
    public function getProviders(int $userId, bool $activeOnly = true): array
    {
        return $this->repository->getProviders($userId, $activeOnly);
    }

    public function getProviderById(int $id, int $userId): ?array
    {
        return $this->repository->getProviderById($id, $userId);
    }

    public function createProvider(int $userId, array $data): array
    {
        $data['user_id'] = $userId;
        $provider = $this->repository->createProvider($data);

        if (!empty($data['service_ids'])) {
            $this->repository->assignServicesToProvider($provider['id'], $data['service_ids']);
        }

        return $this->repository->getProviderById($provider['id'], $userId);
    }

    public function updateProvider(int $id, int $userId, array $data): ?array
    {
        $provider = $this->repository->updateProvider($id, $userId, $data);

        if ($provider && isset($data['service_ids'])) {
            $this->repository->assignServicesToProvider($id, $data['service_ids']);
            return $this->repository->getProviderById($id, $userId);
        }

        return $provider;
    }

    public function deleteProvider(int $id, int $userId): bool
    {
        return $this->repository->deleteProvider($id, $userId);
    }

    // Appointments
    public function getAppointments(int $userId, array $filters = []): array
    {
        return $this->repository->getAppointments($userId, $filters);
    }

    public function getAppointmentById(int $id, int $userId): ?array
    {
        return $this->repository->getAppointmentById($id, $userId);
    }

    public function getAppointmentByReference(string $reference): ?array
    {
        return $this->repository->getAppointmentByReference($reference);
    }

    public function createAppointment(int $userId, array $data): array
    {
        // Get service details
        $service = $this->repository->getServiceById($data['service_id'], $userId);
        if (!$service) {
            throw new \InvalidArgumentException('Service not found');
        }

        // Calculate end time
        $startTime = Carbon::parse($data['appointment_date'] . ' ' . $data['start_time']);
        $duration = $data['duration_minutes'] ?? $service['duration_minutes'];
        $endTime = $startTime->copy()->addMinutes($duration);

        // Find or create customer
        $customer = null;
        if (!empty($data['customer_name']) || !empty($data['customer_email']) || !empty($data['customer_phone'])) {
            $customer = $this->repository->findOrCreateCustomer($userId, [
                'name' => $data['customer_name'] ?? 'Guest',
                'email' => $data['customer_email'] ?? null,
                'phone' => $data['customer_phone'] ?? null,
            ]);
        }

        $appointmentData = [
            'user_id' => $userId,
            'service_id' => $data['service_id'],
            'provider_id' => $data['provider_id'] ?? null,
            'customer_id' => $customer['id'] ?? $data['customer_id'] ?? null,
            'appointment_date' => $data['appointment_date'],
            'start_time' => $data['start_time'],
            'end_time' => $endTime->format('H:i:s'),
            'duration_minutes' => $duration,
            'price' => $data['price'] ?? $service['price'],
            'currency' => $data['currency'] ?? $service['currency'],
            'customer_notes' => $data['customer_notes'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'booking_source' => $data['booking_source'] ?? 'online',
            'status' => $data['status'] ?? 'pending',
        ];

        $appointment = $this->repository->createAppointment($appointmentData);

        // Auto-confirm if settings allow
        $settings = $this->repository->getSettings($userId);
        if (!$settings['require_approval']) {
            $this->confirmAppointment($appointment['id'], $userId);
            $appointment = $this->repository->getAppointmentById($appointment['id'], $userId);
        }

        return $appointment;
    }

    public function updateAppointment(int $id, int $userId, array $data): ?array
    {
        // Recalculate end time if start_time or duration changed
        if (isset($data['start_time']) || isset($data['duration_minutes'])) {
            $appointment = $this->repository->getAppointmentById($id, $userId);
            if ($appointment) {
                $date = $data['appointment_date'] ?? $appointment['appointment_date'];
                $startTime = $data['start_time'] ?? $appointment['start_time'];
                $duration = $data['duration_minutes'] ?? $appointment['duration_minutes'];

                $start = Carbon::parse($date . ' ' . $startTime);
                $data['end_time'] = $start->copy()->addMinutes($duration)->format('H:i:s');
            }
        }

        return $this->repository->updateAppointment($id, $userId, $data);
    }

    public function confirmAppointment(int $id, int $userId): bool
    {
        return $this->repository->updateAppointmentStatus($id, $userId, AppointmentStatus::CONFIRMED);
    }

    public function startAppointment(int $id, int $userId): bool
    {
        return $this->repository->updateAppointmentStatus($id, $userId, AppointmentStatus::IN_PROGRESS);
    }

    public function completeAppointment(int $id, int $userId): bool
    {
        return $this->repository->updateAppointmentStatus($id, $userId, AppointmentStatus::COMPLETED);
    }

    public function cancelAppointment(int $id, int $userId, ?string $reason = null): bool
    {
        return $this->repository->updateAppointmentStatus($id, $userId, AppointmentStatus::CANCELLED, $reason);
    }

    public function markNoShow(int $id, int $userId): bool
    {
        return $this->repository->updateAppointmentStatus($id, $userId, AppointmentStatus::NO_SHOW);
    }

    public function rescheduleAppointment(int $id, int $userId, string $newDate, string $newTime): ?array
    {
        $appointment = $this->repository->getAppointmentById($id, $userId);
        if (!$appointment) return null;

        // Mark old as rescheduled
        $this->repository->updateAppointmentStatus($id, $userId, AppointmentStatus::RESCHEDULED);

        // Create new appointment
        return $this->createAppointment($userId, [
            'service_id' => $appointment['service']['id'],
            'provider_id' => $appointment['provider']['id'] ?? null,
            'customer_id' => $appointment['customer']['id'] ?? null,
            'appointment_date' => $newDate,
            'start_time' => $newTime,
            'duration_minutes' => $appointment['duration_minutes'],
            'price' => $appointment['price'],
            'customer_notes' => $appointment['customer_notes'],
            'internal_notes' => "Rescheduled from {$appointment['booking_reference']}",
            'booking_source' => $appointment['booking_source'],
        ]);
    }

    public function deleteAppointment(int $id, int $userId): bool
    {
        return $this->repository->deleteAppointment($id, $userId);
    }

    public function getAppointmentsForDate(int $userId, string $date, ?int $providerId = null): array
    {
        return $this->repository->getAppointmentsForDate($userId, Carbon::parse($date), $providerId);
    }

    public function getAppointmentsForDateRange(int $userId, string $startDate, string $endDate, ?int $providerId = null): array
    {
        return $this->repository->getAppointmentsForDateRange(
            $userId,
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            $providerId
        );
    }

    public function getUpcomingAppointments(int $userId, int $limit = 10): array
    {
        return $this->repository->getUpcomingAppointments($userId, $limit);
    }

    public function getTodayAppointments(int $userId): array
    {
        return $this->repository->getTodayAppointments($userId);
    }

    // Customers
    public function getCustomers(int $userId, ?string $search = null): array
    {
        return $this->repository->getCustomers($userId, $search);
    }

    public function getCustomerById(int $id, int $userId): ?array
    {
        return $this->repository->getCustomerById($id, $userId);
    }

    public function createCustomer(int $userId, array $data): array
    {
        $data['user_id'] = $userId;
        return $this->repository->createCustomer($data);
    }

    public function updateCustomer(int $id, int $userId, array $data): ?array
    {
        return $this->repository->updateCustomer($id, $userId, $data);
    }

    public function deleteCustomer(int $id, int $userId): bool
    {
        return $this->repository->deleteCustomer($id, $userId);
    }

    // Availability
    public function getSchedule(int $userId, ?int $providerId = null): array
    {
        return $this->repository->getSchedule($userId, $providerId);
    }

    public function saveSchedule(int $userId, ?int $providerId, array $schedule): void
    {
        $this->repository->saveSchedule($userId, $providerId, $schedule);
    }

    public function getExceptions(int $userId, ?int $providerId, string $startDate, string $endDate): array
    {
        return $this->repository->getExceptions(
            $userId,
            $providerId,
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );
    }

    public function createException(int $userId, array $data): array
    {
        $data['user_id'] = $userId;
        return $this->repository->createException($data);
    }

    public function deleteException(int $id, int $userId): bool
    {
        return $this->repository->deleteException($id, $userId);
    }

    public function getAvailableSlots(int $userId, int $serviceId, string $date, ?int $providerId = null): array
    {
        return $this->repository->getAvailableSlots($userId, $serviceId, Carbon::parse($date), $providerId);
    }

    // Settings
    public function getSettings(int $userId): ?array
    {
        return $this->repository->getSettings($userId);
    }

    public function saveSettings(int $userId, array $data): array
    {
        return $this->repository->saveSettings($userId, $data);
    }

    // Statistics
    public function getStatistics(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->repository->getStatistics(
            $userId,
            $startDate ? Carbon::parse($startDate) : null,
            $endDate ? Carbon::parse($endDate) : null
        );
    }

    // Calendar data
    public function getCalendarData(int $userId, string $startDate, string $endDate, ?int $providerId = null): array
    {
        $appointments = $this->getAppointmentsForDateRange($userId, $startDate, $endDate, $providerId);

        // Format for calendar display
        return array_map(function ($apt) {
            return [
                'id' => $apt['id'],
                'title' => $apt['customer']['name'] ?? 'Guest',
                'start' => $apt['appointment_date'] . 'T' . $apt['start_time'],
                'end' => $apt['appointment_date'] . 'T' . $apt['end_time'],
                'backgroundColor' => $apt['service']['color'] ?? '#3b82f6',
                'borderColor' => $apt['service']['color'] ?? '#3b82f6',
                'extendedProps' => [
                    'booking_reference' => $apt['booking_reference'],
                    'service_name' => $apt['service']['name'] ?? '',
                    'provider_name' => $apt['provider']['name'] ?? '',
                    'status' => $apt['status'],
                    'status_label' => $apt['status_label'],
                ],
            ];
        }, $appointments);
    }
}
