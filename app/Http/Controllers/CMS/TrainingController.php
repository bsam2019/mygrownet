<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\TrainingService;
use App\Infrastructure\Persistence\Eloquent\CMS\TrainingProgramModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TrainingSessionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SkillModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CertificationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingController extends Controller
{
    public function __construct(private TrainingService $trainingService)
    {
    }

    public function programs(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $programs = TrainingProgramModel::where('company_id', $companyId)
            ->with(['creator', 'sessions'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Training/Programs', [
            'programs' => $programs,
            'filters' => $request->only(['search', 'type', 'status']),
            'statistics' => $this->trainingService->getTrainingStatistics($companyId),
        ]);
    }

    public function createProgram(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:internal,external,online,workshop,certification,mentorship',
            'category' => 'required|in:technical,soft_skills,leadership,compliance,safety,product,sales,other',
            'level' => 'required|in:beginner,intermediate,advanced,expert',
            'duration_hours' => 'nullable|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'provider' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'prerequisites' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'materials' => 'nullable|array',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        $program = $this->trainingService->createProgram(
            $request->user()->company_id,
            array_merge($validated, ['created_by' => $request->user()->id])
        );

        return redirect()->route('cms.training.programs')
            ->with('success', 'Training program created successfully');
    }

    public function sessions(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $sessions = TrainingSessionModel::whereHas('program', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['program', 'trainer', 'enrollments'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Training/Sessions', [
            'sessions' => $sessions,
            'filters' => $request->only(['status']),
        ]);
    }

    public function skills(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $skills = SkillModel::where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Training/Skills', [
            'skills' => $skills,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function certifications(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $certifications = CertificationModel::whereHas('worker', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with('worker')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('issue_date', 'desc')
            ->paginate(20);

        $expiring = $this->trainingService->getExpiringCertifications($companyId);

        return Inertia::render('CMS/Training/Certifications', [
            'certifications' => $certifications,
            'expiring' => $expiring,
            'filters' => $request->only(['status']),
        ]);
    }
}
