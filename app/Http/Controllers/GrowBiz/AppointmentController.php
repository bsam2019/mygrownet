<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\AppointmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService $appointmentService
    ) {}

    // Main appointments page
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filters = $request->only(['status', 'provider_id', 'service_id', 'date_from', 'date_to', 'search']);

        return Inertia::render('GrowBiz/Appointments/Index', [
            'appointments' => $this->appointmentService->getAppointments($userId, $filters),
            'services' => $this->appointmentService->getServices($userId),
            'providers' => $this->appointmentService->getProviders($userId),
            'statistics' => $this->appointmentService->getStatistics($userId),
            'filters' => $filters,
        ]);
    }

    // Calendar view
    public function calendar(Request $request)
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Calendar', [
            'services' => $this->appointmentService->getServices($userId),
            'providers' => $this->appointmentService->getProviders($userId),
            'settings' => $this->appointmentService->getSettings($userId),
        ]);
    }

    // Get calendar events (JSON)
    public function calendarEvents(Request $request)
    {
        $userId = auth()->id();
        $startDate = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end', now()->endOfMonth()->format('Y-m-d'));
        $providerId = $request->get('provider_id');

        return response()->json(
            $this->appointmentService->getCalendarData($userId, $startDate, $endDate, $providerId)
        );
    }

    // Show single appointment
    public function show(int $id)
    {
        $userId = auth()->id();
        $appointment = $this->appointmentService->getAppointmentById($id, $userId);

        if (!$appointment) {
            return redirect()->route('growbiz.appointments.index')
                ->with('error', 'Appointment not found');
        }

        return Inertia::render('GrowBiz/Appointments/Show', [
            'appointment' => $appointment,
            'services' => $this->appointmentService->getServices($userId),
            'providers' => $this->appointmentService->getProviders($userId),
        ]);
    }

    // Store new appointment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer',
            'provider_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:5',
            'price' => 'nullable|numeric|min:0',
            'customer_notes' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:1000',
            'booking_source' => 'nullable|in:online,phone,walk_in,app,referral',
        ]);

        try {
            $appointment = $this->appointmentService->createAppointment(auth()->id(), $validated);

            if ($request->wantsJson()) {
                return response()->json($appointment, 201);
            }

            return redirect()->route('growbiz.appointments.index')
                ->with('success', 'Appointment booked successfully');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    // Update appointment
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'service_id' => 'sometimes|integer',
            'provider_id' => 'nullable|integer',
            'appointment_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:5',
            'price' => 'nullable|numeric|min:0',
            'customer_notes' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:1000',
            'payment_status' => 'nullable|in:unpaid,partial,paid,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $appointment = $this->appointmentService->updateAppointment($id, auth()->id(), $validated);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($appointment);
        }

        return back()->with('success', 'Appointment updated');
    }

    // Update status
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,in_progress,completed,cancelled,no_show',
            'reason' => 'nullable|string|max:500',
        ]);

        $userId = auth()->id();
        $success = match ($validated['status']) {
            'confirmed' => $this->appointmentService->confirmAppointment($id, $userId),
            'in_progress' => $this->appointmentService->startAppointment($id, $userId),
            'completed' => $this->appointmentService->completeAppointment($id, $userId),
            'cancelled' => $this->appointmentService->cancelAppointment($id, $userId, $validated['reason'] ?? null),
            'no_show' => $this->appointmentService->markNoShow($id, $userId),
            default => false,
        };

        if ($request->wantsJson()) {
            return response()->json(['success' => $success]);
        }

        return back()->with($success ? 'success' : 'error', $success ? 'Status updated' : 'Failed to update status');
    }

    // Reschedule appointment
    public function reschedule(Request $request, int $id)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
        ]);

        $newAppointment = $this->appointmentService->rescheduleAppointment(
            $id,
            auth()->id(),
            $validated['appointment_date'],
            $validated['start_time']
        );

        if (!$newAppointment) {
            return response()->json(['error' => 'Failed to reschedule'], 422);
        }

        if ($request->wantsJson()) {
            return response()->json($newAppointment);
        }

        return redirect()->route('growbiz.appointments.show', $newAppointment['id'])
            ->with('success', 'Appointment rescheduled');
    }

    // Delete appointment
    public function destroy(int $id)
    {
        $deleted = $this->appointmentService->deleteAppointment($id, auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => $deleted]);
        }

        return redirect()->route('growbiz.appointments.index')
            ->with($deleted ? 'success' : 'error', $deleted ? 'Appointment deleted' : 'Failed to delete');
    }

    // Get available slots
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer',
            'date' => 'required|date',
            'provider_id' => 'nullable|integer',
        ]);

        $slots = $this->appointmentService->getAvailableSlots(
            auth()->id(),
            $validated['service_id'],
            $validated['date'],
            $validated['provider_id'] ?? null
        );

        return response()->json($slots);
    }

    // Today's appointments
    public function today()
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Today', [
            'appointments' => $this->appointmentService->getTodayAppointments($userId),
            'statistics' => $this->appointmentService->getStatistics($userId, now()->format('Y-m-d'), now()->format('Y-m-d')),
        ]);
    }

    // Upcoming appointments
    public function upcoming()
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Upcoming', [
            'appointments' => $this->appointmentService->getUpcomingAppointments($userId, 50),
        ]);
    }

    // === SERVICES ===
    public function services()
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Services', [
            'services' => $this->appointmentService->getServices($userId, false),
            'categories' => $this->appointmentService->getServiceCategories($userId),
            'providers' => $this->appointmentService->getProviders($userId),
        ]);
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'buffer_minutes' => 'nullable|integer|min:0|max:60',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'allow_online_booking' => 'boolean',
        ]);

        $service = $this->appointmentService->createService(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($service, 201);
        }

        return back()->with('success', 'Service created');
    }

    public function updateService(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'sometimes|integer|min:5|max:480',
            'buffer_minutes' => 'nullable|integer|min:0|max:60',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'allow_online_booking' => 'boolean',
        ]);

        $service = $this->appointmentService->updateService($id, auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($service);
        }

        return back()->with('success', 'Service updated');
    }

    public function destroyService(int $id)
    {
        $deleted = $this->appointmentService->deleteService($id, auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => $deleted]);
        }

        return back()->with($deleted ? 'success' : 'error', $deleted ? 'Service deleted' : 'Failed to delete');
    }

    // === PROVIDERS ===
    public function providers()
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Providers', [
            'providers' => $this->appointmentService->getProviders($userId, false),
            'services' => $this->appointmentService->getServices($userId),
        ]);
    }

    public function storeProvider(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'accept_online_bookings' => 'boolean',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer',
        ]);

        $provider = $this->appointmentService->createProvider(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($provider, 201);
        }

        return back()->with('success', 'Staff member added');
    }

    public function updateProvider(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'accept_online_bookings' => 'boolean',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer',
        ]);

        $provider = $this->appointmentService->updateProvider($id, auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($provider);
        }

        return back()->with('success', 'Staff member updated');
    }

    public function destroyProvider(int $id)
    {
        $deleted = $this->appointmentService->deleteProvider($id, auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => $deleted]);
        }

        return back()->with($deleted ? 'success' : 'error', $deleted ? 'Staff member removed' : 'Failed to remove');
    }

    // === CUSTOMERS ===
    public function customers(Request $request)
    {
        $userId = auth()->id();
        $search = $request->get('search');

        return Inertia::render('GrowBiz/Appointments/Customers', [
            'customers' => $this->appointmentService->getCustomers($userId, $search),
            'search' => $search,
        ]);
    }

    public function showCustomer(int $id)
    {
        $customer = $this->appointmentService->getCustomerById($id, auth()->id());

        if (!$customer) {
            return redirect()->route('growbiz.appointments.customers')
                ->with('error', 'Customer not found');
        }

        return Inertia::render('GrowBiz/Appointments/CustomerShow', [
            'customer' => $customer,
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = $this->appointmentService->createCustomer(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($customer, 201);
        }

        return back()->with('success', 'Customer added');
    }

    public function updateCustomer(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = $this->appointmentService->updateCustomer($id, auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($customer);
        }

        return back()->with('success', 'Customer updated');
    }

    public function destroyCustomer(int $id)
    {
        $deleted = $this->appointmentService->deleteCustomer($id, auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => $deleted]);
        }

        return back()->with($deleted ? 'success' : 'error', $deleted ? 'Customer deleted' : 'Failed to delete');
    }

    public function searchCustomers(Request $request)
    {
        $search = $request->get('q', '');
        $customers = $this->appointmentService->getCustomers(auth()->id(), $search);

        return response()->json($customers);
    }

    // === AVAILABILITY ===
    public function availability()
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Availability', [
            'schedule' => $this->appointmentService->getSchedule($userId),
            'providers' => $this->appointmentService->getProviders($userId),
            'exceptions' => $this->appointmentService->getExceptions($userId, null, now()->format('Y-m-d'), now()->addMonths(3)->format('Y-m-d')),
        ]);
    }

    public function saveSchedule(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|integer',
            'schedule' => 'required|array',
            'schedule.*.start_time' => 'required|date_format:H:i',
            'schedule.*.end_time' => 'required|date_format:H:i',
            'schedule.*.is_available' => 'boolean',
        ]);

        $this->appointmentService->saveSchedule(
            auth()->id(),
            $validated['provider_id'] ?? null,
            $validated['schedule']
        );

        return back()->with('success', 'Schedule saved');
    }

    public function storeException(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|integer',
            'date' => 'required|date',
            'type' => 'required|in:closed,modified_hours,extra_availability',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'reason' => 'nullable|string|max:255',
        ]);

        $exception = $this->appointmentService->createException(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($exception, 201);
        }

        return back()->with('success', 'Exception added');
    }

    public function destroyException(int $id)
    {
        $deleted = $this->appointmentService->deleteException($id, auth()->id());

        if (request()->wantsJson()) {
            return response()->json(['success' => $deleted]);
        }

        return back()->with($deleted ? 'success' : 'error', $deleted ? 'Exception removed' : 'Failed to remove');
    }

    // === SETTINGS ===
    public function settings()
    {
        $userId = auth()->id();

        return Inertia::render('GrowBiz/Appointments/Settings', [
            'settings' => $this->appointmentService->getSettings($userId),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'booking_page_description' => 'nullable|string|max:1000',
            'booking_page_slug' => 'nullable|string|max:100|alpha_dash',
            'timezone' => 'nullable|string|max:50',
            'slot_duration_minutes' => 'nullable|integer|min:5|max:120',
            'min_booking_notice_hours' => 'nullable|integer|min:0|max:168',
            'max_booking_advance_days' => 'nullable|integer|min:1|max:365',
            'require_approval' => 'boolean',
            'allow_cancellation' => 'boolean',
            'cancellation_notice_hours' => 'nullable|integer|min:0|max:168',
            'send_confirmation_email' => 'boolean',
            'send_reminder_sms' => 'boolean',
            'send_reminder_whatsapp' => 'boolean',
            'reminder_timings' => 'nullable|array',
            'cancellation_policy' => 'nullable|string|max:2000',
            'primary_color' => 'nullable|string|max:7',
        ]);

        $settings = $this->appointmentService->saveSettings(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($settings);
        }

        return back()->with('success', 'Settings saved');
    }

    // === STATISTICS ===
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        return response()->json(
            $this->appointmentService->getStatistics(auth()->id(), $startDate, $endDate)
        );
    }
}
