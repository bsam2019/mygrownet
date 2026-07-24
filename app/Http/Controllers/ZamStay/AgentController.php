<?php

namespace App\Http\Controllers\ZamStay;

use App\Domain\ZamStay\Services\AgentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgentController extends Controller
{
    public function __construct(
        private readonly AgentService $agentService,
    ) {}

    public function index()
    {
        $agents = $this->agentService->findAllApproved();

        return Inertia::render('ZamStay/Agent/Index', [
            'agents' => $agents,
        ]);
    }

    public function show(int $id)
    {
        $agent = $this->agentService->findById($id);

        return Inertia::render('ZamStay/Agent/Show', [
            'agent' => $agent?->toArray(),
        ]);
    }

    public function dashboard(Request $request)
    {
        $agent = $this->agentService->findOrFailByUser($request->user()->id);
        $data = $this->agentService->getDashboard($agent->id);

        return Inertia::render('ZamStay/Agent/Dashboard', $data);
    }

    public function registerForm()
    {
        return Inertia::render('ZamStay/Agent/Register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
        ]);

        $this->agentService->register($request->user()->id, $validated);

        return redirect()->route('zamstay.agent.dashboard')
            ->with('success', 'Agent registration submitted. Awaiting approval.');
    }
}
