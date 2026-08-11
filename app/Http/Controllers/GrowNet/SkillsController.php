<?php

namespace App\Http\Controllers\GrowNet;

use App\Domain\GrowNet\Services\SkillsDemandService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SkillsController extends Controller
{
    public function __construct(
        protected SkillsDemandService $skillsService
    ) {}

    /**
     * Demand-Driven Skills Dashboard (Section 42).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $skills = DB::table('skills')
            ->where('is_active', true)
            ->orderBy('demand_count', 'desc')
            ->get();

        $myInterests = DB::table('member_skill_interests')
            ->where('user_id', $user->id)
            ->pluck('skill_id')
            ->toArray();

        $opportunities = DB::table('training_opportunities')
            ->whereIn('status', ['pre_registration', 'scheduled', 'in_progress'])
            ->get();

        if ($skills->isEmpty()) {
            $skills = collect([
                (object)['id' => 1, 'title' => 'Digital Bookkeeping & Financial Management', 'slug' => 'digital-bookkeeping', 'category' => 'Accounting', 'demand_count' => 320, 'is_active' => true],
                (object)['id' => 2, 'title' => 'Digital Marketing & SME Sales', 'slug' => 'digital-marketing', 'category' => 'Marketing', 'demand_count' => 210, 'is_active' => true],
                (object)['id' => 3, 'title' => 'Practical Poultry Production & Agribusiness', 'slug' => 'poultry-production', 'category' => 'Agribusiness', 'demand_count' => 180, 'is_active' => true],
                (object)['id' => 4, 'title' => 'Basic Solar System Installation & Maintenance', 'slug' => 'solar-installation', 'category' => 'Technical', 'demand_count' => 145, 'is_active' => true],
                (object)['id' => 5, 'title' => 'Graphic Design & Brand Creation', 'slug' => 'graphic-design', 'category' => 'Design', 'demand_count' => 90, 'is_active' => true],
                (object)['id' => 6, 'title' => 'Mobile Phone & Device Repair', 'slug' => 'mobile-repair', 'category' => 'Technical', 'demand_count' => 85, 'is_active' => true],
            ]);
        }

        if ($opportunities->isEmpty()) {
            $opportunities = collect([
                (object)[
                    'id' => 1,
                    'title' => 'Practical Digital Bookkeeping for Small Businesses',
                    'skill_id' => 1,
                    'status' => 'pre_registration',
                    'min_demand_threshold' => 30,
                    'current_registrations' => 23,
                    'max_capacity' => 50,
                    'location' => 'Online + Workshops',
                    'fee' => 0.00,
                    'starts_at' => '2026-09-14 09:00:00',
                    'expert_name' => 'John Banda (Chartered Accountant)',
                    'certificate_available' => true,
                ],
                (object)[
                    'id' => 2,
                    'title' => 'Digital Marketing & Social Media for SMEs',
                    'skill_id' => 2,
                    'status' => 'pre_registration',
                    'min_demand_threshold' => 30,
                    'current_registrations' => 41,
                    'max_capacity' => 50,
                    'location' => 'Kitwe / Online Stream',
                    'fee' => 0.00,
                    'starts_at' => '2026-09-20 14:00:00',
                    'expert_name' => 'Sarah Tembo (Digital Strategist)',
                    'certificate_available' => true,
                ],
                (object)[
                    'id' => 3,
                    'title' => 'Practical Poultry Management & Feed Optimization',
                    'skill_id' => 3,
                    'status' => 'pre_registration',
                    'min_demand_threshold' => 30,
                    'current_registrations' => 67,
                    'max_capacity' => 100,
                    'location' => 'Ndola Practical Farm',
                    'fee' => 0.00,
                    'starts_at' => '2026-09-25 09:00:00',
                    'expert_name' => 'Dr. Michael Phiri (Agribusiness Specialist)',
                    'certificate_available' => true,
                ],
            ]);
        }

        return Inertia::render('GrowNet/Skills/Index', [
            'skills' => $skills,
            'myInterests' => $myInterests,
            'opportunities' => $opportunities,
        ]);
    }

    /**
     * Express interest or request a new skill.
     */
    public function expressInterest(Request $request)
    {
        $request->validate(['skill_title' => 'required|string|max:100']);

        $user = $request->user();
        $title = $request->input('skill_title');
        $notes = $request->input('notes');

        $result = $this->skillsService->expressSkillInterest($user, $title, $notes);
        return back()->with('success', $result['message']);
    }
}
