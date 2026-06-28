<?php

namespace App\Domain\GrowBiz\Repositories;

use App\Domain\GrowBiz\ValueObjects\AppointmentStatus;
use Carbon\Carbon;

interface AppointmentRepositoryInterface
{
    // Services
    public function getServices(int $userId, bool $activeOnly = true): array;
    public function getServiceById(int $id, int $userId): ?array;
    public function createService(array $data): array;
    public function updateService(int $id, int $userId, array $data): ?array;
    public function deleteService(int $id, int $userId): bool;
    public function getServiceCategories(int $userId): array;

    // Service Providers
    public function getProviders(int $userId, bool $activeOnly = true): array;
    public function getProviderById(int $id, int $userId): ?array;
    public function createProvider(array $data): array;
    public function updateProvider(int $id, int $userId, array $data): ?array;
    public function deleteProvider(int $id, int $userId): bool;
    public function assignServicesToProvider(int $providerId, array $serviceIds): void;

    // Appointments
    public function getAppointments(int $userId, array $filters = []): array;
    public function getAppointmentById(int $id, int $userId): ?array;
    public function getAppointmentByReference(string $reference): ?array;
    public function createAppointment(array $data): array;
    public function updateAppointment(int $id, int $userId, array $data): ?array;
    public function updateAppointmentStatus(int $id, int $userId, AppointmentStatus $status, ?string $reason = null): bool;
    public function deleteAppointment(int $id, int $userId): bool;
    public function getAppointmentsForDate(int $userId, Carbon $date, ?int $providerId = null): array;
    public function getAppointmentsForDateRange(int $userId, Carbon $startDate, Carbon $endDate, ?int $providerId = null): array;
    public function getUpcomingAppointments(int $userId, int $limit = 10): array;
    public function getTodayAppointments(int $userId): array;

    // Customers
    public function getCustomers(int $userId, ?string $search = null): array;
    public function getCustomerById(int $id, int $userId): ?array;
    public function createCustomer(array $data): array;
    public function updateCustomer(int $id, int $userId, array $data): ?array;
    public function deleteCustomer(int $id, int $userId): bool;
    public function findOrCreateCustomer(int $userId, array $data): array;

    // Availability
    public function getSchedule(int $userId, ?int $providerId = null): array;
    public function saveSchedule(int $userId, ?int $providerId, array $schedule): void;
    public function getExceptions(int $userId, ?int $providerId, Carbon $startDate, Carbon $endDate): array;
    public function createException(array $data): array;
    public function deleteException(int $id, int $userId): bool;
    public function getAvailableSlots(int $userId, int $serviceId, Carbon $date, ?int $providerId = null): array;

    // Settings
    public function getSettings(int $userId): ?array;
    public function saveSettings(int $userId, array $data): array;

    // Statistics
    public function getStatistics(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array;
}
