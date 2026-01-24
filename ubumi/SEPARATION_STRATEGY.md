# Ubumi - Separation Strategy

**Last Updated:** January 21, 2026  
**Status:** Planning Phase

## Overview

Ubumi will be implemented as a **separate Laravel project** to enable independent scaling and deployment as the userbase grows. This document outlines the integration strategy with MyGrowNet.

---

## Architecture Approach

### Option 1: Microservice from Day 1 (Recommended)

**Structure:**
```
/ubumi-app (Separate Laravel Project)
├── app/
│   ├── Domain/
│   ├── Application/
│   ├── Infrastructure/
│   └── Http/
├── database/
├── routes/
└── config/

/mygrownet (Main Project)
├── Integration with Ubumi via API
└── Shared authentication
```

**Benefits:**
- ✅ Clean separation from start
- ✅ Independent scaling
- ✅ Separate database
- ✅ Can deploy on different servers
- ✅ No migration needed later

**Integration Points:**
- Shared authentication (JWT or OAuth2)
- API communication
- Shared user database (or user sync)

### Option 2: Module First, Extract Later

**Structure:**
```
/mygrownet
├── modules/
│   └── Ubumi/ (Self-contained module)
│       ├── Domain/
│       ├── Application/
│       ├── Infrastructure/
│       └── Http/
```

**Benefits:**
- ✅ Faster initial development
- ✅ Shared infrastructure
- ✅ Easy to extract later

**Drawbacks:**
- ⚠️ Requires migration work later
- ⚠️ Harder to scale independently

---

## Recommended: Separate Project Setup

### Project Structure

```
ubumi/
├── app/
│   ├── Domain/
│   │   └── Ubumi/
│   │       ├── Entities/
│   │       │   ├── Family.php
│   │       │   ├── Person.php
│   │       │   └── Relationship.php
│   │       ├── ValueObjects/
│   │       │   ├── FamilyId.php
│   │       │   ├── PersonId.php
│   │       │   ├── FamilyName.php
│   │       │   ├── PersonName.php
│   │       │   └── ApproximateAge.php
│   │       ├── Services/
│   │       │   ├── DuplicateDetectionService.php
│   │       │   └── FamilyTreeService.php
│   │       ├── Repositories/
│   │       │   ├── FamilyRepositoryInterface.php
│   │       │   └── PersonRepositoryInterface.php
│   │       └── Events/
│   │           ├── FamilyCreated.php
│   │           └── PersonAdded.php
│   ├── Application/
│   │   └── UseCases/
│   │       ├── CreateFamily/
│   │       ├── AddPerson/
│   │       ├── DetectDuplicates/
│   │       └── MergePersons/
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   │   ├── Eloquent/
│   │   │   └── Repositories/
│   │   └── External/
│   │       └── MyGrowNetAuthService.php
│   └── Http/
│       ├── Controllers/
│       │   ├── FamilyController.php
│       │   ├── PersonController.php
│       │   └── CheckInController.php
│       └── Middleware/
│           └── AuthenticateFromMyGrowNet.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── config/
│   ├── ubumi.php
│   └── mygrownet.php
└── tests/
```

---

## Integration with MyGrowNet

### Authentication Strategy

**Option A: Shared JWT Tokens**
```php
// MyGrowNet generates JWT
// Ubumi validates JWT using shared secret

// config/mygrownet.php in Ubumi
return [
    'jwt_secret' => env('MYGROWNET_JWT_SECRET'),
    'api_url' => env('MYGROWNET_API_URL'),
];
```

**Option B: OAuth2 Provider**
```php
// MyGrowNet acts as OAuth2 provider
// Ubumi is OAuth2 client
```

**Option C: API Key + User ID**
```php
// Simple API key authentication
// Pass user_id in requests
```

### API Communication

**From MyGrowNet to Ubumi:**
```php
// MyGrowNet calls Ubumi API
$response = Http::withToken($userToken)
    ->post('https://ubumi.mygrownet.com/api/families', [
        'name' => 'Mwansa Family',
        'admin_user_id' => $user->id,
    ]);
```

**From Ubumi to MyGrowNet:**
```php
// Ubumi validates user with MyGrowNet
$response = Http::withHeaders([
    'X-API-Key' => config('mygrownet.api_key')
])->get('https://api.mygrownet.com/users/' . $userId);
```

### Database Strategy

**Option 1: Separate Databases (Recommended)**
```
mygrownet_db:
- users
- subscriptions
- payments

ubumi_db:
- families
- persons
- relationships
- check_ins
```

**Option 2: Shared Database, Separate Schemas**
```
mygrownet_db:
  - public schema (MyGrowNet tables)
  - ubumi schema (Ubumi tables)
```

**Option 3: User Sync**
```php
// Sync users from MyGrowNet to Ubumi
// Keep local copy for performance
```

---

## Deployment Architecture

### Phase 1: Same Server, Different Subdomain

```
Server: mygrownet.com
├── app.mygrownet.com (Main app)
└── ubumi.mygrownet.com (Ubumi app)
```

**Nginx Configuration:**
```nginx
# Main app
server {
    server_name app.mygrownet.com;
    root /var/www/mygrownet/public;
    # ... Laravel config
}

# Ubumi app
server {
    server_name ubumi.mygrownet.com;
    root /var/www/ubumi/public;
    # ... Laravel config
}
```

### Phase 2: Separate Servers

```
Load Balancer
├── app.mygrownet.com → Server 1 (MyGrowNet)
└── ubumi.mygrownet.com → Server 2 (Ubumi)
```

### Phase 3: Microservices with Kubernetes

```
Kubernetes Cluster
├── mygrownet-deployment
├── ubumi-deployment
├── shared-redis
└── shared-database (or separate)
```

---

## Development Workflow

### Local Development

**Docker Compose Setup:**
```yaml
version: '3.8'
services:
  mygrownet:
    build: ./mygrownet
    ports:
      - "8000:80"
    environment:
      - UBUMI_API_URL=http://ubumi
  
  ubumi:
    build: ./ubumi
    ports:
      - "8001:80"
    environment:
      - MYGROWNET_API_URL=http://mygrownet
  
  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=secret
    volumes:
      - mysql_data:/var/lib/mysql
```

### Running Locally

```bash
# Terminal 1: MyGrowNet
cd mygrownet
php artisan serve --port=8000

# Terminal 2: Ubumi
cd ubumi
php artisan serve --port=8001
```

---

## Migration Path (If Starting as Module)

### Step 1: Prepare Module for Extraction
- Ensure all Ubumi code is in `modules/Ubumi/`
- No dependencies on MyGrowNet code
- All routes prefixed with `/ubumi`

### Step 2: Create Separate Project
```bash
# Create new Laravel project
composer create-project laravel/laravel ubumi

# Copy module code
cp -r mygrownet/modules/Ubumi/* ubumi/app/
```

### Step 3: Update Dependencies
- Change namespaces
- Update composer.json
- Configure authentication

### Step 4: Database Migration
```sql
-- Export Ubumi tables
mysqldump mygrownet_db ubumi_* > ubumi_tables.sql

-- Import to new database
mysql ubumi_db < ubumi_tables.sql
```

### Step 5: Update API Calls
- Replace internal calls with HTTP API calls
- Add authentication headers
- Handle API failures gracefully

---

## API Contract

### Ubumi API Endpoints

**Families:**
```
POST   /api/families
GET    /api/families
GET    /api/families/{id}
PUT    /api/families/{id}
DELETE /api/families/{id}
```

**Persons:**
```
POST   /api/families/{familyId}/persons
GET    /api/families/{familyId}/persons
GET    /api/persons/{id}
PUT    /api/persons/{id}
DELETE /api/persons/{id}
```

**Duplicates:**
```
POST   /api/families/{familyId}/check-duplicates
GET    /api/families/{familyId}/duplicate-alerts
POST   /api/merge-proposals
PUT    /api/merge-proposals/{id}
```

**Check-Ins (Phase 2):**
```
POST   /api/persons/{id}/check-in
GET    /api/persons/{id}/check-ins
GET    /api/families/{familyId}/check-ins
```

### MyGrowNet Integration Endpoints

**User Validation:**
```
GET /api/users/{id}/validate
Response: { "valid": true, "user": {...} }
```

**Subscription Check:**
```
GET /api/users/{id}/subscription
Response: { "active": true, "plan": "pro" }
```

---

## Configuration Files

### Ubumi: config/mygrownet.php
```php
<?php

return [
    'api_url' => env('MYGROWNET_API_URL', 'https://api.mygrownet.com'),
    'api_key' => env('MYGROWNET_API_KEY'),
    'jwt_secret' => env('MYGROWNET_JWT_SECRET'),
    'timeout' => env('MYGROWNET_API_TIMEOUT', 30),
];
```

### MyGrowNet: config/ubumi.php
```php
<?php

return [
    'api_url' => env('UBUMI_API_URL', 'https://ubumi.mygrownet.com'),
    'api_key' => env('UBUMI_API_KEY'),
    'enabled' => env('UBUMI_ENABLED', true),
];
```

---

## Monitoring & Observability

### Metrics to Track
- API response times
- Error rates
- User activity
- Database performance
- Memory usage

### Tools
- Laravel Telescope (development)
- Laravel Horizon (queues)
- Sentry (error tracking)
- New Relic or DataDog (production monitoring)

---

## Security Considerations

### API Security
- Use HTTPS only
- Implement rate limiting
- Validate all inputs
- Use API keys or JWT
- Log all API calls

### Data Privacy
- Encrypt sensitive data
- Implement GDPR compliance
- User data deletion
- Audit logs

---

## Recommendation

**Start with separate project from day 1** because:

1. ✅ Clean architecture from the start
2. ✅ No migration work needed later
3. ✅ Independent scaling from day 1
4. ✅ Clear boundaries and contracts
5. ✅ Can be deployed separately when needed
6. ✅ Different teams can work independently

**Initial Setup:**
- Deploy both on same server
- Use subdomains
- Shared authentication
- API communication
- Separate databases

**Future Scaling:**
- Move to separate servers
- Add load balancers
- Implement caching
- Use CDN for media
- Consider Kubernetes

---

## Next Steps

1. Create separate Ubumi Laravel project
2. Set up authentication integration
3. Define API contracts
4. Implement core domain logic
5. Create integration layer
6. Test end-to-end flow
7. Deploy to staging
8. Monitor and optimize

---

## Changelog

### Version 1.0 (January 21, 2026)
- Initial separation strategy document
- Defined microservice architecture
- Outlined integration approaches
- Created deployment roadmap
- Established API contracts

---

**Status:** Ready for implementation  
**Recommended Approach:** Separate project from day 1
