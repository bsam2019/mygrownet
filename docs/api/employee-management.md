# Employee Management API Documentation

## Overview

The Employee Management API provides comprehensive endpoints for managing employees, departments, positions, performance reviews, and commissions within the VBIF platform. All endpoints follow RESTful conventions and require appropriate authentication and authorization.

## Authentication

All endpoints require authentication via Laravel Sanctum or session-based authentication. Users must have appropriate permissions based on their roles (HR Manager, Department Head, Employee, etc.).

## Base URL

```
/api/employees
```

## Endpoints

### Employee Management

#### List Employees
```http
GET /employees
```

**Description**: Retrieve a paginated list of employees with optional filtering and search capabilities.

**Query Parameters**:
- `search` (string, optional): Search by name, email, or employee number
- `department_id` (integer, optional): Filter by department
- `position_id` (integer, optional): Filter by position
- `status` (string, optional): Filter by employment status (active, inactive, terminated, suspended)
- `page` (integer, optional): Page number for pagination
- `per_page` (integer, optional): Items per page (default: 15, max: 100)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "employee_number": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@vbif.com",
      "phone": "+260123456789",
      "employment_status": "active",
      "hire_date": "2024-01-15",
      "department": {
        "id": 1,
        "name": "Sales",
        "description": "Sales Department"
      },
      "position": {
        "id": 1,
        "title": "Field Agent",
        "description": "Client relationship management"
      },
      "manager": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith"
      },
      "base_salary": 5000.00,
      "commission_rate": 2.5,
      "performance_rating": 8.5,
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    }
  ],
  "links": {
    "first": "/employees?page=1",
    "last": "/employees?page=10",
    "prev": null,
    "next": "/employees?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

#### Get Employee Details
```http
GET /employees/{id}
```

**Description**: Retrieve detailed information about a specific employee.

**Path Parameters**:
- `id` (integer, required): Employee ID

**Response**:
```json
{
  "data": {
    "id": 1,
    "employee_number": "EMP001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@vbif.com",
    "phone": "+260123456789",
    "address": "123 Main Street, Lusaka",
    "employment_status": "active",
    "hire_date": "2024-01-15",
    "termination_date": null,
    "department": {
      "id": 1,
      "name": "Sales",
      "description": "Sales Department",
      "head_employee": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith"
      }
    },
    "position": {
      "id": 1,
      "title": "Field Agent",
      "description": "Client relationship management",
      "base_salary_min": 4000.00,
      "base_salary_max": 8000.00,
      "commission_eligible": true,
      "commission_rate": 2.5
    },
    "manager": {
      "id": 2,
      "first_name": "Jane",
      "last_name": "Smith",
      "position": "Sales Manager"
    },
    "base_salary": 5000.00,
    "commission_rate": 2.5,
    "performance_rating": 8.5,
    "last_performance_review": "2024-06-15",
    "assigned_clients": [
      {
        "id": 101,
        "name": "Client A",
        "assignment_type": "primary",
        "assigned_date": "2024-02-01"
      }
    ],
    "recent_performance": {
      "investments_facilitated_count": 15,
      "investments_facilitated_amount": 75000.00,
      "commission_generated": 1875.00,
      "client_retention_rate": 95.0
    },
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
}
```

#### Create Employee
```http
POST /employees
```

**Description**: Create a new employee record.

**Request Body**:
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@vbif.com",
  "phone": "+260123456789",
  "address": "123 Main Street, Lusaka",
  "hire_date": "2024-01-15",
  "department_id": 1,
  "position_id": 1,
  "manager_id": 2,
  "base_salary": 5000.00,
  "commission_rate": 2.5,
  "create_user_account": true,
  "user_permissions": ["view_clients", "manage_investments"]
}
```

**Validation Rules**:
- `first_name`: required, string, max:100
- `last_name`: required, string, max:100
- `email`: required, email, unique:employees,email
- `phone`: nullable, string, max:20
- `address`: nullable, string
- `hire_date`: required, date, before_or_equal:today
- `department_id`: required, exists:departments,id
- `position_id`: required, exists:positions,id
- `manager_id`: nullable, exists:employees,id
- `base_salary`: required, numeric, min:0
- `commission_rate`: nullable, numeric, min:0, max:100
- `create_user_account`: boolean
- `user_permissions`: array

**Response** (201 Created):
```json
{
  "data": {
    "id": 1,
    "employee_number": "EMP001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@vbif.com",
    "employment_status": "active",
    "user_account": {
      "id": 101,
      "email": "john.doe@vbif.com",
      "created": true
    },
    "created_at": "2024-01-15T10:00:00Z"
  },
  "message": "Employee created successfully"
}
```

#### Update Employee
```http
PUT /employees/{id}
```

**Description**: Update an existing employee record.

**Path Parameters**:
- `id` (integer, required): Employee ID

**Request Body**: Same as create employee, but all fields are optional except those being updated.

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "employee_number": "EMP001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@vbif.com",
    "updated_at": "2024-01-15T10:00:00Z"
  },
  "message": "Employee updated successfully"
}
```

#### Delete Employee
```http
DELETE /employees/{id}
```

**Description**: Soft delete an employee record (sets employment status to terminated).

**Path Parameters**:
- `id` (integer, required): Employee ID

**Request Body**:
```json
{
  "termination_reason": "resignation",
  "termination_date": "2024-12-31",
  "notes": "Employee resigned for personal reasons"
}
```

**Response** (200 OK):
```json
{
  "message": "Employee terminated successfully"
}
```

### Department Management

#### List Departments
```http
GET /departments
```

**Description**: Retrieve all departments with hierarchical structure.

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Sales",
      "description": "Sales Department",
      "head_employee": {
        "id": 2,
        "first_name": "Jane",
        "last_name": "Smith"
      },
      "parent_department": null,
      "sub_departments": [
        {
          "id": 3,
          "name": "Field Sales",
          "description": "Field sales operations"
        }
      ],
      "employee_count": 25,
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

#### Create Department
```http
POST /departments
```

**Request Body**:
```json
{
  "name": "Marketing",
  "description": "Marketing and promotions department",
  "head_employee_id": 5,
  "parent_department_id": null
}
```

#### Update Department
```http
PUT /departments/{id}
```

#### Delete Department
```http
DELETE /departments/{id}
```

### Position Management

#### List Positions
```http
GET /positions
```

#### Create Position
```http
POST /positions
```

**Request Body**:
```json
{
  "title": "Senior Field Agent",
  "description": "Senior level client relationship management",
  "department_id": 1,
  "base_salary_min": 6000.00,
  "base_salary_max": 10000.00,
  "commission_eligible": true,
  "commission_rate": 3.0,
  "responsibilities": [
    "Manage client portfolios",
    "Facilitate investments",
    "Mentor junior agents"
  ],
  "required_permissions": [
    "view_all_clients",
    "approve_investments"
  ]
}
```

### Performance Management

#### List Performance Reviews
```http
GET /performance-reviews
```

**Query Parameters**:
- `employee_id` (integer, optional): Filter by employee
- `period_start` (date, optional): Filter by evaluation period start
- `period_end` (date, optional): Filter by evaluation period end

#### Create Performance Review
```http
POST /performance-reviews
```

**Request Body**:
```json
{
  "employee_id": 1,
  "evaluation_period_start": "2024-01-01",
  "evaluation_period_end": "2024-03-31",
  "investments_facilitated_count": 15,
  "investments_facilitated_amount": 75000.00,
  "client_retention_rate": 95.0,
  "new_client_acquisitions": 5,
  "goal_achievement_rate": 90.0,
  "overall_score": 8.5,
  "review_notes": "Excellent performance this quarter",
  "goals_next_period": [
    "Increase client acquisition by 20%",
    "Maintain retention rate above 95%"
  ]
}
```

#### Update Performance Review
```http
PUT /performance-reviews/{id}
```

### Commission Management

#### List Commissions
```http
GET /commissions
```

**Query Parameters**:
- `employee_id` (integer, optional): Filter by employee
- `status` (string, optional): Filter by status (pending, approved, paid, cancelled)
- `commission_type` (string, optional): Filter by type
- `date_from` (date, optional): Filter by calculation date
- `date_to` (date, optional): Filter by calculation date

#### Calculate Commission
```http
POST /commissions/calculate
```

**Request Body**:
```json
{
  "employee_id": 1,
  "investment_id": 501,
  "commission_type": "investment_facilitation",
  "base_amount": 10000.00,
  "commission_rate": 2.5
}
```

#### Mark Commission as Paid
```http
PUT /commissions/{id}/mark-paid
```

**Request Body**:
```json
{
  "payment_date": "2024-01-31",
  "notes": "Paid via bank transfer"
}
```

## Error Responses

All endpoints return consistent error responses:

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "base_salary": [
      "The base salary must be a number."
    ]
  }
}
```

### Not Found (404)
```json
{
  "message": "Employee not found"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "message": "This action is unauthorized"
}
```

### Server Error (500)
```json
{
  "message": "Internal server error",
  "error": "An unexpected error occurred"
}
```

## Rate Limiting

API endpoints are rate limited to:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated requests

## Permissions

### Required Permissions by Endpoint

| Endpoint | Required Permission |
|----------|-------------------|
| GET /employees | `view_employees` |
| POST /employees | `create_employees` |
| PUT /employees/{id} | `edit_employees` |
| DELETE /employees/{id} | `delete_employees` |
| GET /departments | `view_departments` |
| POST /departments | `manage_departments` |
| GET /performance-reviews | `view_performance_reviews` |
| POST /performance-reviews | `create_performance_reviews` |
| GET /commissions | `view_commissions` |
| POST /commissions/calculate | `calculate_commissions` |

## Integration Notes

### With Investment Domain
- Field agents can be assigned to investor portfolios
- Commission calculations are triggered by investment events
- Performance metrics include investment facilitation data

### With Reward Domain
- Employee commissions integrate with referral system
- Performance bonuses calculated with existing reward structures
- Cross-domain event handling for commission payments

### With User Management
- Employee records link to user accounts for system access
- Role-based permissions inherited from Spatie Laravel Permission
- Authentication flows integrated with existing user system

## Changelog

### Version 1.0.0 (2024-01-15)
- Initial API release
- Basic CRUD operations for employees, departments, positions
- Performance review management
- Commission calculation and tracking
- Integration with existing VBIF platform domains