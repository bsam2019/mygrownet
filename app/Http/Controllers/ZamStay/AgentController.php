<?php

namespace App\Http\Controllers\ZamStay;

use App\Http\Controllers\Controller;
use App\Models\ZamStay\ZamStayAgent;
use App\Models\ZamStay\ZamStayBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = ZamStayAgent::approved()->with('user')->paginate(20);

        return Inertia::render('ZamStay/Agent/Index', [
            'agents' => $agents,
        ]);
    }

    public function show(ZamStayAgent $agent)
    {
        $agent->load('user');

        return Inertia::render('ZamStay/Agent/Show', [
            'agent' => $agent,
        ]);
    }

    public function dashboard(Request $request)
    {
        $agent = ZamStayAgent::where('user_id', $request->user()->id)->firstOrFail();
        $agent->load('user');

        $bookings = ZamStayBooking::where('agent_id', $agent->id)
            ->with(['property', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_bookings' => ZamStayBooking::where('agent_id', $agent->id)->count(),
            'confirmed_bookings' => ZamStayBooking::where('agent_id', $agent->id)->where('status', 'confirmed')->count(),
            'pending_bookings' => ZamStayBooking::where('agent_id', $agent->id)->where('status', 'pending')->count(),
            'commission_rate' => $agent->commission_rate,
        ];

        return Inertia::render('ZamStay/Agent/Dashboard', [
            'agent' => $agent,
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }

    public function registerForm()
    {
        $existing = ZamStayAgent::where('user_id', request()->user()->id)->first();

        return Inertia::render('ZamStay/Agent/Register', [
            'existing' => $existing,
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
        ]);

        $existing = ZamStayAgent::where('user_id', $request->user()->id)->first();

        if ($existing) {
            return back()->withErrors(['business_name' => 'You are already registered as an agent.']);
        }

        ZamStayAgent::create([
            'user_id' => $request->user()->id,
            'business_name' => $validated['business_name'],
            'license_number' => $validated['license_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()->route('zamstay.agent.dashboard')
            ->with('success', 'Agent registration submitted. Awaiting approval.');
    }
}
