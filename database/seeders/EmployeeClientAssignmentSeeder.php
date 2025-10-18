<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeClientAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fieldAgents = EmployeeModel::whereHas('position', function($query) {
            $query->where('title', 'LIKE', '%Field Agent%');
        })->get();

        $portfolioManagers = EmployeeModel::whereHas('position', function($query) {
            $query->where('title', 'Portfolio Manager');
        })->get();

        // Get users who could be clients (excluding employees)
        $employeeUserIds = EmployeeModel::whereNotNull('user_id')->pluck('user_id')->toArray();
        $clients = User::whereNotIn('id', $employeeUserIds)
                      ->where('id', '>', 1) // Skip admin user
                      ->take(100)
                      ->get();

        if ($clients->isEmpty()) {
            // Create some sample client users if none exist
            $this->createSampleClients();
            $clients = User::whereNotIn('id', $employeeUserIds)
                          ->where('id', '>', 1)
                          ->take(100)
                          ->get();
        }

        // Assign clients to field agents (primary assignments)
        $this->assignClientsToFieldAgents($fieldAgents, $clients);

        // Assign some clients to portfolio managers (secondary assignments)
        $this->assignClientsToPortfolioManagers($portfolioManagers, $clients);
    }

    private function createSampleClients(): void
    {
        $sampleClients = [
            ['name' => 'John Mwamba', 'email' => 'john.mwamba@email.com'],
            ['name' => 'Alice Banda', 'email' => 'alice.banda@email.com'],
            ['name' => 'Charles Phiri', 'email' => 'charles.phiri@email.com'],
            ['name' => 'Grace Tembo', 'email' => 'grace.tembo.client@email.com'],
            ['name' => 'David Zulu', 'email' => 'david.zulu@email.com'],
            ['name' => 'Mary Chanda', 'email' => 'mary.chanda.client@email.com'],
            ['name' => 'Peter Mulenga', 'email' => 'peter.mulenga.client@email.com'],
            ['name' => 'Ruth Nyirenda', 'email' => 'ruth.nyirenda@email.com'],
            ['name' => 'Joseph Sakala', 'email' => 'joseph.sakala.client@email.com'],
            ['name' => 'Elizabeth Lungu', 'email' => 'elizabeth.lungu.client@email.com'],
        ];

        foreach ($sampleClients as $clientData) {
            User::create([
                'name' => $clientData['name'],
                'email' => $clientData['email'],
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
        }
    }

    private function assignClientsToFieldAgents($fieldAgents, $clients): void
    {
        $clientsPerAgent = ceil($clients->count() / max($fieldAgents->count(), 1));
        $clientIndex = 0;

        foreach ($fieldAgents as $agent) {
            $agentClients = $clients->slice($clientIndex, $clientsPerAgent);
            
            foreach ($agentClients as $client) {
                $assignmentDate = Carbon::now()->subDays(rand(30, 365));
                
                EmployeeClientAssignmentModel::create([
                    'employee_id' => $agent->id,
                    'user_id' => $client->id,
                    'assignment_type' => 'primary',
                    'assigned_date' => $assignmentDate->format('Y-m-d'),
                    'unassigned_date' => null,
                    'is_active' => true,
                    'notes' => "Primary field agent assignment for client acquisition and relationship management",
                ]);
            }
            
            $clientIndex += $clientsPerAgent;
        }
    }

    private function assignClientsToPortfolioManagers($portfolioManagers, $clients): void
    {
        // Assign 30% of clients to portfolio managers as secondary assignments
        $clientsForPortfolioManagers = $clients->random(min(30, $clients->count()));
        
        foreach ($clientsForPortfolioManagers as $client) {
            $portfolioManager = $portfolioManagers->random();
            $assignmentDate = Carbon::now()->subDays(rand(15, 180));
            
            // Check if this client already has a secondary assignment
            $existingSecondary = EmployeeClientAssignmentModel::where('user_id', $client->id)
                ->where('assignment_type', 'secondary')
                ->where('is_active', true)
                ->exists();
                
            if (!$existingSecondary) {
                EmployeeClientAssignmentModel::create([
                    'employee_id' => $portfolioManager->id,
                    'user_id' => $client->id,
                    'assignment_type' => 'secondary',
                    'assigned_date' => $assignmentDate->format('Y-m-d'),
                    'unassigned_date' => null,
                    'is_active' => true,
                    'notes' => "Portfolio management and investment advisory services",
                ]);
            }
        }

        // Create some support assignments for customer service
        $customerServiceReps = EmployeeModel::whereHas('position', function($query) {
            $query->where('title', 'LIKE', '%Customer Service%');
        })->get();

        if ($customerServiceReps->isNotEmpty()) {
            $clientsForSupport = $clients->random(min(20, $clients->count()));
            
            foreach ($clientsForSupport as $client) {
                $supportRep = $customerServiceReps->random();
                $assignmentDate = Carbon::now()->subDays(rand(7, 90));
                
                EmployeeClientAssignmentModel::create([
                    'employee_id' => $supportRep->id,
                    'user_id' => $client->id,
                    'assignment_type' => 'support',
                    'assigned_date' => $assignmentDate->format('Y-m-d'),
                    'unassigned_date' => null,
                    'is_active' => true,
                    'notes' => "Customer support and service assistance",
                ]);
            }
        }
    }
}