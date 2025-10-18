# Design Document

## Overview

The Employee Management System extends the existing VBIF platform with a comprehensive employee lifecycle management solution. Following the established Domain-Driven Design architecture, this system introduces a new Employee bounded context that integrates seamlessly with existing Investment, Reward, Financial, and User domains.

The system provides structured employee data management, performance tracking, commission calculations for field agents, and role-based access control while maintaining the platform's security standards and audit requirements.

## Architecture

### Domain Integration

The Employee Management System follows the existing DDD structure and integrates with current bounded contexts:

```
app/Domain/Employee/          # New Employee bounded context
├── Entities/
│   ├── Employee.php         # Core employee entity
│   ├── Department.php       # Organizational structure
│   ├── Position.php         # Job roles and responsibilities
│   └── EmployeePerformance.php # Performance tracking
├── ValueObjects/
│   ├── EmployeeId.php       # Employee identifier
│   ├── Salary.php           # Compensation value object
│   ├── PerformanceMetrics.php # Performance measurement
│   └── EmploymentStatus.php # Employment state
├── Services/
│   ├── EmployeeRegistrationService.php
│   ├── PerformanceTrackingService.php
│   ├── PayrollCalculationService.php
│   └── CommissionCalculationService.php
├── Repositories/
│   └── EmployeeRepositoryInterface.php
└── Events/
    ├── EmployeeHired.php
    ├── EmployeePromoted.php
    └── PerformanceReviewed.php
```

### Integration Points

**With User Domain:**
- Employee-User relationship for system access
- Role-based permissions integration with Spatie Laravel Permission
- Authentication and authorization flow

**With Investment Domain:**
- Field agent assignment to investor portfolios
- Investment facilitation tracking
- Client relationship management

**With Reward Domain:**
- Commission calculation for employee referrals
- Performance-based bonus calculations
- Integration with existing referral matrix system

**With Financial Domain:**
- Payroll processing and commission payments
- Employee expense management
- Financial reporting and compliance

## Components and Interfaces

### Core Entities

#### Employee Entity
```php
class Employee
{
    private EmployeeId $id;
    private string $firstName;
    private string $lastName;
    private Email $email;
    private Phone $phone;
    private EmploymentStatus $status;
    private Department $department;
    private Position $position;
    private Salary $baseSalary;
    private ?User $user; // System access account
    private DateTimeImmutable $hireDate;
    private ?DateTimeImmutable $terminationDate;
    
    public function assignToUser(User $user): void;
    public function updatePerformance(PerformanceMetrics $metrics): void;
    public function calculateTotalCompensation(Period $period): Money;
    public function canManageInvestor(User $investor): bool;
}
```

#### Department Entity
```php
class Department
{
    private DepartmentId $id;
    private string $name;
    private string $description;
    private ?Employee $head;
    private Collection $employees;
    private Collection $positions;
    
    public function addEmployee(Employee $employee): void;
    public function removeEmployee(Employee $employee): void;
    public function assignHead(Employee $employee): void;
}
```

#### Position Entity
```php
class Position
{
    private PositionId $id;
    private string $title;
    private string $description;
    private Department $department;
    private Salary $baseSalary;
    private Collection $responsibilities;
    private Collection $permissions;
    
    public function addResponsibility(string $responsibility): void;
    public function hasPermission(string $permission): bool;
}
```

### Value Objects

#### EmploymentStatus
```php
class EmploymentStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const TERMINATED = 'terminated';
    public const SUSPENDED = 'suspended';
    
    private string $status;
    private ?string $reason;
    private ?DateTimeImmutable $effectiveDate;
}
```

#### PerformanceMetrics
```php
class PerformanceMetrics
{
    private float $investmentsFacilitated;
    private float $clientRetentionRate;
    private float $commissionGenerated;
    private int $newClientAcquisitions;
    private float $goalAchievementRate;
    private Period $evaluationPeriod;
    
    public function calculateOverallScore(): float;
    public function compareWith(PerformanceMetrics $other): array;
}
```

### Domain Services

#### EmployeeRegistrationService
```php
class EmployeeRegistrationService
{
    public function registerEmployee(
        EmployeeRegistrationData $data,
        Department $department,
        Position $position
    ): Employee;
    
    public function createSystemAccount(Employee $employee): User;
    public function assignPermissions(Employee $employee): void;
}
```

#### CommissionCalculationService
```php
class CommissionCalculationService
{
    public function calculateFieldAgentCommission(
        Employee $agent,
        Investment $investment
    ): CommissionAmount;
    
    public function calculatePerformanceBonus(
        Employee $employee,
        PerformanceMetrics $metrics
    ): BonusAmount;
    
    public function calculateMonthlyCommissions(
        Employee $employee,
        Period $period
    ): array;
}
```

## Data Models

### Database Schema

#### employees table
```sql
CREATE TABLE employees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    hire_date DATE NOT NULL,
    termination_date DATE NULL,
    employment_status ENUM('active', 'inactive', 'terminated', 'suspended') DEFAULT 'active',
    department_id BIGINT NOT NULL,
    position_id BIGINT NOT NULL,
    user_id BIGINT NULL, -- Link to system user account
    manager_id BIGINT NULL, -- Self-referencing for reporting structure
    base_salary DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,2) DEFAULT 0,
    performance_rating DECIMAL(3,2) DEFAULT 0,
    last_performance_review DATE NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (manager_id) REFERENCES employees(id),
    
    INDEX idx_employee_status (employment_status),
    INDEX idx_employee_department (department_id),
    INDEX idx_employee_manager (manager_id),
    INDEX idx_employee_user (user_id)
);
```

#### departments table
```sql
CREATE TABLE departments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    head_employee_id BIGINT NULL,
    parent_department_id BIGINT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (head_employee_id) REFERENCES employees(id),
    FOREIGN KEY (parent_department_id) REFERENCES departments(id),
    
    INDEX idx_department_head (head_employee_id),
    INDEX idx_department_parent (parent_department_id)
);
```

#### positions table
```sql
CREATE TABLE positions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    department_id BIGINT NOT NULL,
    base_salary_min DECIMAL(10,2) NOT NULL,
    base_salary_max DECIMAL(10,2) NOT NULL,
    commission_eligible BOOLEAN DEFAULT FALSE,
    commission_rate DECIMAL(5,2) DEFAULT 0,
    responsibilities JSON,
    required_permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (department_id) REFERENCES departments(id),
    
    INDEX idx_position_department (department_id),
    INDEX idx_position_commission (commission_eligible)
);
```

#### employee_performance table
```sql
CREATE TABLE employee_performance (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL,
    evaluation_period_start DATE NOT NULL,
    evaluation_period_end DATE NOT NULL,
    investments_facilitated_count INT DEFAULT 0,
    investments_facilitated_amount DECIMAL(15,2) DEFAULT 0,
    client_retention_rate DECIMAL(5,2) DEFAULT 0,
    commission_generated DECIMAL(10,2) DEFAULT 0,
    new_client_acquisitions INT DEFAULT 0,
    goal_achievement_rate DECIMAL(5,2) DEFAULT 0,
    overall_score DECIMAL(3,2) DEFAULT 0,
    reviewer_id BIGINT NOT NULL,
    review_notes TEXT,
    goals_next_period JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (reviewer_id) REFERENCES employees(id),
    
    INDEX idx_performance_employee (employee_id),
    INDEX idx_performance_period (evaluation_period_start, evaluation_period_end),
    INDEX idx_performance_score (overall_score)
);
```

#### employee_commissions table
```sql
CREATE TABLE employee_commissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL,
    investment_id BIGINT NULL, -- Link to specific investment
    user_id BIGINT NULL, -- Client who made the investment
    commission_type ENUM('investment_facilitation', 'referral', 'performance_bonus', 'retention_bonus') NOT NULL,
    base_amount DECIMAL(15,2) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,
    commission_amount DECIMAL(10,2) NOT NULL,
    calculation_date DATE NOT NULL,
    payment_date DATE NULL,
    status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (investment_id) REFERENCES investments(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    
    INDEX idx_commission_employee (employee_id),
    INDEX idx_commission_status (status),
    INDEX idx_commission_date (calculation_date),
    INDEX idx_commission_type (commission_type)
);
```

#### employee_client_assignments table
```sql
CREATE TABLE employee_client_assignments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    assignment_type ENUM('primary', 'secondary', 'support') DEFAULT 'primary',
    assigned_date DATE NOT NULL,
    unassigned_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    
    UNIQUE KEY unique_active_primary (employee_id, user_id, assignment_type, is_active),
    INDEX idx_assignment_employee (employee_id),
    INDEX idx_assignment_user (user_id),
    INDEX idx_assignment_active (is_active)
);
```

## Error Handling

### Domain-Specific Exceptions

```php
// Employee Domain Exceptions
class EmployeeNotFoundException extends DomainException {}
class InvalidEmploymentStatusException extends DomainException {}
class EmployeeAlreadyExistsException extends DomainException {}
class InsufficientPermissionsException extends DomainException {}

// Performance Exceptions
class InvalidPerformanceMetricsException extends DomainException {}
class PerformanceReviewNotFoundException extends DomainException {}

// Commission Exceptions
class CommissionCalculationException extends DomainException {}
class InvalidCommissionRateException extends DomainException {}
```

### Error Response Structure

```php
class EmployeeErrorResponse
{
    public function __construct(
        private string $code,
        private string $message,
        private array $context = [],
        private ?Throwable $previous = null
    ) {}
    
    public function toArray(): array
    {
        return [
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'context' => $this->context,
                'timestamp' => now()->toISOString()
            ]
        ];
    }
}
```

### Validation Rules

```php
class EmployeeValidationRules
{
    public static function registration(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date|before_or_equal:today',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'base_salary' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100'
        ];
    }
    
    public static function performanceReview(): array
    {
        return [
            'evaluation_period_start' => 'required|date',
            'evaluation_period_end' => 'required|date|after:evaluation_period_start',
            'investments_facilitated_count' => 'required|integer|min:0',
            'investments_facilitated_amount' => 'required|numeric|min:0',
            'client_retention_rate' => 'required|numeric|min:0|max:100',
            'new_client_acquisitions' => 'required|integer|min:0',
            'goal_achievement_rate' => 'required|numeric|min:0|max:100',
            'overall_score' => 'required|numeric|min:0|max:10'
        ];
    }
}
```

## Testing Strategy

### Unit Testing

**Domain Entity Tests:**
- Employee entity business logic validation
- Value object immutability and validation
- Domain service calculations and rules

**Repository Tests:**
- Data persistence and retrieval
- Query optimization and performance
- Transaction handling

**Service Tests:**
- Commission calculation accuracy
- Performance metric calculations
- Integration with existing domains

### Integration Testing

**Database Integration:**
- Migration compatibility with existing schema
- Foreign key constraints and relationships
- Data integrity across bounded contexts

**API Integration:**
- Controller response formats
- Authentication and authorization
- Error handling and validation

**Domain Integration:**
- Cross-domain event handling
- Service communication patterns
- Data consistency across contexts

### Feature Testing

**Employee Management Workflows:**
- Complete employee registration process
- Performance review submission and approval
- Commission calculation and payment processing

**Role-Based Access Control:**
- Permission inheritance from existing system
- Department-specific access controls
- Field agent client assignment workflows

**Reporting and Analytics:**
- Performance dashboard accuracy
- Commission reporting completeness
- Compliance audit trail verification

### Performance Testing

**Database Performance:**
- Query optimization for large employee datasets
- Index effectiveness for common operations
- Bulk operation performance (payroll processing)

**API Performance:**
- Response time benchmarks for employee operations
- Concurrent user handling for dashboard access
- Memory usage optimization for reporting features

### Security Testing

**Data Protection:**
- Employee PII encryption and access controls
- Audit trail completeness and integrity
- Role-based data access validation

**Integration Security:**
- Cross-domain permission validation
- API endpoint security compliance
- Session management and authentication flows

## Implementation Phases

### Phase 1: Core Employee Management (Weeks 1-2)
- Employee, Department, and Position entities
- Basic CRUD operations and database schema
- Integration with existing User and Permission systems
- Employee registration and profile management

### Phase 2: Performance Tracking (Weeks 3-4)
- Performance metrics and review system
- Goal setting and tracking functionality
- Basic reporting and dashboard views
- Integration with Investment domain for tracking

### Phase 3: Commission System (Weeks 5-6)
- Commission calculation services
- Integration with Reward domain
- Payroll processing workflows
- Financial reporting and compliance features

### Phase 4: Advanced Features (Weeks 7-8)
- Advanced analytics and reporting
- Mobile-responsive interfaces
- Automated notification systems
- Performance optimization and caching

This design ensures seamless integration with your existing VBIF platform while providing comprehensive employee management capabilities that support your growing organization's needs.