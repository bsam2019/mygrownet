<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\AppointmentRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\AppointmentId;
use App\Domain\PrimeEdge\ValueObjects\AppointmentStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\DateTimeRange;
use App\Domain\PrimeEdge\Entities\Appointment;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentRepositoryInterface $appointmentRepository,
    ) {}

    public function create()
    {
        return Inertia::render('PrimeEdge/Appointments/Create');
    }

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $appointments = array_map(
            fn(Appointment $apt) => [
                'id' => $apt->id()->toString(),
                'title' => $apt->title(),
                'description' => $apt->description(),
                'scheduledAt' => $apt->dateTimeRange()->start()->format('Y-m-d H:i'),
                'durationMinutes' => $apt->dateTimeRange()->durationMinutes(),
                'status' => $apt->status()->value,
                'createdAt' => $apt->createdAt()->format('Y-m-d H:i'),
            ],
            $this->appointmentRepository->findByClientId(ClientId::fromString($clientId))
        );

        return Inertia::render('PrimeEdge/Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $start = new DateTimeImmutable($validated['scheduled_at']);
        $end = $start->modify('+' . $validated['duration_minutes'] . ' minutes');

        $appointment = Appointment::create(
            id: $this->appointmentRepository->nextId(),
            clientId: ClientId::fromString(auth()->guard('primeedge')->id()),
            title: $validated['title'],
            dateTimeRange: DateTimeRange::fromDateTimes($start, $end),
            description: $validated['notes'] ?? null,
        );

        $this->appointmentRepository->save($appointment);

        return redirect()->route('primeedge.appointments.index')
            ->with('success', 'Appointment scheduled successfully.');
    }
}
