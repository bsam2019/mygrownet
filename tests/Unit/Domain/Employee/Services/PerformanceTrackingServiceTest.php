<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Services;

use App\Domain\Employee\Services\PerformanceTrackingService;
use App\Domain\Employee\Services\PerformanceReviewData;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\Entities\EmployeePerformance;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\Exceptions\InvalidPerformanceMetricsException;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

class PerformanceTrackingServiceTest extends TestCase
{
    private EmployeeRepositoryInterface $employeeRepository;
    private PerformanceTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeRepository = Mockery::mock(EmployeeRepositoryInterface::class);
        $this->service = new PerformanceTrackingService($this->employeeRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_performance_review(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $reviewer = $this->createEmployee('Jane', 'Manager');
        
        $data = PerformanceReviewData::create(
            $employee,
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31'),
            8.0, // investments facilitated
            85.5, // client retention rate
            12000.0, // commission generated
            4, // new client acquisitions
            78.0, // goal achievement rate
            $reviewer,
            'Good performance this quarter'
        );

        $this->employeeRepository
            ->shouldReceive('save')
            ->with(Mockery::type(Employee::class))
            ->once()
            ->andReturn(true);

        // Act
        $performance = $this->service->createPerformanceReview($data);

        // Assert
        $this->assertInstanceOf(EmployeePerformance::class, $performance);
        $this->assertEquals($employee->getId(), $performance->getEmployee()->getId());
        $this->assertEquals($reviewer->getId(), $performance->getReviewer()->getId());
        $this->assertEquals('Good performance this quarter', $performance->getReviewNotes());
        $this->assertTrue($employee->hasPerformanceMetrics());
    }

    public function test_throws_exception_for_invalid_evaluation_period(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $reviewer = $this->createEmployee('Jane', 'Manager');
        
        $data = PerformanceReviewData::create(
            $employee,
            new DateTimeImmutable('2024-03-31'), // End date before start date
            new DateTimeImmutable('2024-01-01'),
            8.0,
            85.5,
            12000.0,
            4,
            78.0,
            $reviewer
        );

        // Act & Assert
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->service->createPerformanceReview($data);
    }

    public function test_throws_exception_for_invalid_retention_rate(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $reviewer = $this->createEmployee('Jane', 'Manager');
        
        $data = PerformanceReviewData::create(
            $employee,
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31'),
            8.0,
            150.0, // Invalid retention rate > 100
            12000.0,
            4,
            78.0,
            $reviewer
        );

        // Act & Assert
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->service->createPerformanceReview($data);
    }

    public function test_throws_exception_for_negative_investments(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $reviewer = $this->createEmployee('Jane', 'Manager');
        
        $data = PerformanceReviewData::create(
            $employee,
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31'),
            -5.0, // Negative investments
            85.5,
            12000.0,
            4,
            78.0,
            $reviewer
        );

        // Act & Assert
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->service->createPerformanceReview($data);
    }

    public function test_can_set_performance_goals(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $goals = [
            [
                'description' => 'Increase client portfolio by 20%',
                'target' => 20,
                'deadline' => new DateTimeImmutable('2025-12-31')
            ],
            [
                'description' => 'Achieve 90% client retention',
                'target' => 90,
                'deadline' => new DateTimeImmutable('2025-12-31')
            ]
        ];

        // Act & Assert - Should not throw exception
        $this->service->setPerformanceGoals($employee, $goals, new DateTimeImmutable('2025-12-31'));
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    public function test_throws_exception_for_invalid_goals(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $invalidGoals = [
            [
                'description' => '', // Empty description
                'target' => 20,
                'deadline' => new DateTimeImmutable('2024-12-31')
            ]
        ];

        // Act & Assert
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->service->setPerformanceGoals($employee, $invalidGoals, new DateTimeImmutable('2024-12-31'));
    }

    public function test_can_track_goal_progress(): void
    {
        // Arrange
        $employee = $this->createEmployeeWithPerformance();
        $goalUpdates = [
            'investments_facilitated' => 8.0,
            'client_retention_rate' => 85.0
        ];

        // Act
        $progress = $this->service->trackGoalProgress($employee, $goalUpdates);

        // Assert
        $this->assertIsArray($progress);
        $this->assertArrayHasKey('investments_facilitated', $progress);
        $this->assertArrayHasKey('client_retention_rate', $progress);
        
        $investmentProgress = $progress['investments_facilitated'];
        $this->assertEquals(8.0, $investmentProgress['current_value']);
        $this->assertEquals(10, $investmentProgress['target_value']);
        $this->assertEquals(80.0, $investmentProgress['progress_percentage']);
        $this->assertEquals('on_track', $investmentProgress['status']);
    }

    public function test_throws_exception_when_tracking_progress_without_metrics(): void
    {
        // Arrange
        $employee = $this->createEmployee();
        $goalUpdates = ['investments_facilitated' => 8.0];

        // Act & Assert
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->service->trackGoalProgress($employee, $goalUpdates);
    }

    public function test_can_calculate_performance_trends(): void
    {
        // Arrange
        $employee = $this->createEmployeeWithPerformance();

        // Act
        $trends = $this->service->calculatePerformanceTrends($employee, 3);

        // Assert
        $this->assertIsArray($trends);
        $this->assertArrayHasKey('investment_trend', $trends);
        $this->assertArrayHasKey('retention_trend', $trends);
        $this->assertArrayHasKey('commission_trend', $trends);
        $this->assertArrayHasKey('overall_trend', $trends);
        
        $investmentTrend = $trends['investment_trend'];
        $this->assertEquals('investments_facilitated', $investmentTrend['metric']);
        $this->assertEquals(8.0, $investmentTrend['current_value']);
        $this->assertIsArray($investmentTrend['historical_data']);
        $this->assertContains($investmentTrend['trend_direction'], ['improving', 'declining', 'stable']);
    }

    public function test_can_generate_performance_recommendations(): void
    {
        // Arrange
        $employee = $this->createEmployeeWithLowPerformance();

        // Act
        $recommendations = $this->service->generatePerformanceRecommendations($employee);

        // Assert
        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        
        $firstRecommendation = $recommendations[0];
        $this->assertArrayHasKey('category', $firstRecommendation);
        $this->assertArrayHasKey('priority', $firstRecommendation);
        $this->assertArrayHasKey('recommendation', $firstRecommendation);
        $this->assertArrayHasKey('target_improvement', $firstRecommendation);
    }

    public function test_can_compare_employee_performance(): void
    {
        // Arrange
        $employee1 = $this->createEmployeeWithPerformance();
        $employee2 = $this->createEmployeeWithPerformance('Jane', 'Smith');

        // Act
        $comparison = $this->service->compareEmployeePerformance($employee1, $employee2);

        // Assert
        $this->assertIsArray($comparison);
        $this->assertArrayHasKey('employee1', $comparison);
        $this->assertArrayHasKey('employee2', $comparison);
        $this->assertArrayHasKey('comparison', $comparison);
        
        $this->assertEquals('John Doe', $comparison['employee1']['name']);
        $this->assertEquals('Jane Smith', $comparison['employee2']['name']);
        $this->assertIsFloat($comparison['employee1']['overall_score']);
        $this->assertIsFloat($comparison['employee2']['overall_score']);
    }

    public function test_throws_exception_when_comparing_without_metrics(): void
    {
        // Arrange
        $employee1 = $this->createEmployee();
        $employee2 = $this->createEmployee('Jane', 'Smith');

        // Act & Assert
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->service->compareEmployeePerformance($employee1, $employee2);
    }

    public function test_can_identify_top_performers(): void
    {
        // Arrange
        $employees = [
            $this->createEmployeeWithPerformance('High', 'Performer'),
            $this->createEmployeeWithLowPerformance('Low', 'Performer'),
            $this->createEmployeeWithPerformance('Medium', 'Performer')
        ];

        // Act
        $topPerformers = $this->service->identifyTopPerformers($employees, 2);

        // Assert
        $this->assertIsArray($topPerformers);
        $this->assertCount(2, $topPerformers);
        
        // Should be sorted by score descending
        $this->assertGreaterThanOrEqual($topPerformers[1]['score'], $topPerformers[0]['score']);
    }

    public function test_can_identify_underperformers(): void
    {
        // Arrange
        $employees = [
            $this->createEmployeeWithPerformance('High', 'Performer'),
            $this->createEmployeeWithLowPerformance('Low', 'Performer')
        ];

        // Act
        $underperformers = $this->service->identifyUnderperformers($employees, 7.0);

        // Assert
        $this->assertIsArray($underperformers);
        $this->assertNotEmpty($underperformers);
        
        foreach ($underperformers as $underperformer) {
            $this->assertLessThan(7.0, $underperformer['score']);
            $this->assertArrayHasKey('recommendations', $underperformer);
        }
    }

    public function test_can_calculate_department_performance_average(): void
    {
        // Arrange
        $employees = [
            $this->createEmployeeWithPerformance('Employee', 'One'),
            $this->createEmployeeWithPerformance('Employee', 'Two'),
            $this->createEmployeeWithLowPerformance('Employee', 'Three')
        ];

        // Act
        $average = $this->service->calculateDepartmentPerformanceAverage($employees);

        // Assert
        $this->assertIsArray($average);
        $this->assertArrayHasKey('average_overall_score', $average);
        $this->assertArrayHasKey('average_investments_facilitated', $average);
        $this->assertArrayHasKey('employee_count', $average);
        $this->assertEquals(3, $average['employee_count']);
        $this->assertIsFloat($average['average_overall_score']);
    }

    private function createEmployee(string $firstName = 'John', string $lastName = 'Doe'): Employee
    {
        $department = new Department(
            DepartmentId::generate(),
            'IT Department',
            'Test department',
            null, // parent department
            true  // isActive
        );

        $position = new Position(
            PositionId::generate(),
            'Software Developer',
            'Test position',
            $department,
            Salary::fromKwacha(40000),
            Salary::fromKwacha(80000),
            false,
            0.0
        );

        return Employee::create(
            'EMP20250001',
            $firstName,
            $lastName,
            Email::fromString(strtolower($firstName . '.' . $lastName . '@example.com')),
            new DateTimeImmutable('2024-01-15'),
            $department,
            $position,
            Salary::fromKwacha(50000)
        );
    }

    private function createEmployeeWithPerformance(string $firstName = 'John', string $lastName = 'Doe'): Employee
    {
        $employee = $this->createEmployee($firstName, $lastName);
        
        $metrics = new PerformanceMetrics(
            8.0, // investments facilitated
            85.0, // client retention rate
            12000.0, // commission generated
            4, // new client acquisitions
            78.0, // goal achievement rate
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31')
        );

        $employee->updatePerformance($metrics, new DateTimeImmutable());
        
        return $employee;
    }

    private function createEmployeeWithLowPerformance(string $firstName = 'Low', string $lastName = 'Performer'): Employee
    {
        $employee = $this->createEmployee($firstName, $lastName);
        
        $metrics = new PerformanceMetrics(
            2.0, // low investments facilitated
            65.0, // low client retention rate
            5000.0, // low commission generated
            1, // low new client acquisitions
            45.0, // low goal achievement rate
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-03-31')
        );

        $employee->updatePerformance($metrics, new DateTimeImmutable());
        
        return $employee;
    }
}