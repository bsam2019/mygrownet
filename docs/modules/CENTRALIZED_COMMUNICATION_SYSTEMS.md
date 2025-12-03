# Centralized Communication Systems

**Last Updated:** December 3, 2025
**Status:** ✅ Production Ready
**Architecture:** Domain-Driven Design (DDD)

## Overview

The MyGrowNet platform uses centralized messaging and support ticket systems that serve all application modules (MyGrowNet, GrowFinance, GrowBiz, etc.). This architecture ensures:

- **Consistency**: Same experience across all apps
- **Maintainability**: Single codebase for communication logic
- **Scalability**: Easy to add new modules without duplicating code
- **Data Integrity**: All data stored in unified tables with module context

---

## Part 1: Messaging System

### Architecture

#### Domain Layer
```
app/Domain/Messaging/
├── Entities/
│   └── Message.php              # Core message entity
├── ValueObjects/
│   ├── MessageId.php            # Message identifier
│   ├── MessageContent.php       # Subject + body
│   └── UserId.php               # User identifier
├── Events/
│   ├── MessageSent.php          # Dispatched when message sent
│   └── MessageRead.php          # Dispatched when message read
├── Repositories/
│   └── MessageRepository.php    # Repository interface
└── Services/
    └── MessagingService.php     # Domain service
```

#### Application Layer
```
app/Application/Messaging/
├── DTOs/
│   ├── MessageDTO.php           # Message data transfer object
│   └── SendMessageDTO.php       # Send message request DTO
└── UseCases/
    ├── GetInboxUseCase.php
    ├── GetSentMessagesUseCase.php
    ├── GetConversationUseCase.php
    ├── SendMessageUseCase.php
    ├── MarkMessageAsReadUseCase.php
    └── BroadcastMessageUseCase.php
```

#### Infrastructure Layer
```
app/Infrastructure/Persistence/Eloquent/Messaging/
├── MessageModel.php             # Eloquent model
└── EloquentMessageRepository.php
```

### Database Schema: `messages` Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| sender_id | foreignId | User who sent the message |
| recipient_id | foreignId | User who receives the message |
| subject | string | Message subject |
| body | text | Message body |
| is_read | boolean | Read status |
| read_at | timestamp | When message was read |
| parent_id | foreignId | Parent message (for threads) |
| module | string(50) | App context (mygrownet, growfinance, growbiz) |
| metadata | json | App-specific data |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### Indexes
- `(recipient_id, is_read)` - For inbox queries
- `(sender_id, created_at)` - For sent messages
- `(recipient_id, module, is_read)` - For module-filtered inbox
- `(sender_id, module, created_at)` - For module-filtered sent

### API Reference

#### SendMessageDTO
```php
new SendMessageDTO(
    senderId: int,           // Required: Sender user ID
    recipientId: int,        // Required: Recipient user ID
    subject: string,         // Required: Message subject
    body: string,            // Required: Message body
    parentId: ?int,          // Optional: Parent message ID for threads
    module: string,          // Default: 'mygrownet'
    metadata: ?array,        // Optional: App-specific data
);
```

#### Use Case Methods
```php
// Get inbox
$messages = $getInboxUseCase->execute(
    userId: int,
    limit: int = 100,
    offset: int = 0,
    module: ?string = null  // null = all modules
);

// Get sent messages
$messages = $getSentMessagesUseCase->execute(
    userId: int,
    limit: int = 100,
    offset: int = 0,
    module: ?string = null
);

// Get conversation
$messages = $getConversationUseCase->execute(
    userId: int,
    otherUserId: int,
    limit: int = 50,
    module: ?string = null
);
```

---

## Part 2: Support Ticket System

### Architecture

#### Infrastructure Layer
```
app/Infrastructure/Persistence/Eloquent/Support/
├── SupportTicketModel.php       # Main ticket model
└── TicketCommentModel.php       # Ticket comments/replies
```

### Database Schema: `support_tickets` Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | foreignId | User who created ticket |
| investor_account_id | foreignId | Investor account (nullable) |
| category | string(50) | Ticket category |
| source | string | Source type (member, investor) |
| module | string(50) | App context |
| metadata | json | App-specific data |
| priority | enum | low, medium, high |
| status | enum | open, in_progress, waiting, resolved, closed |
| subject | string | Ticket subject |
| description | text | Ticket description |
| assigned_to | foreignId | Assigned admin user |
| resolved_at | timestamp | Resolution timestamp |
| closed_at | timestamp | Closure timestamp |
| satisfaction_rating | tinyint | 1-5 rating |
| rating_feedback | text | User feedback |

### Database Schema: `ticket_comments` Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| ticket_id | foreignId | Parent ticket |
| user_id | foreignId | Comment author |
| author_type | string | member, support, admin |
| author_name | string | Display name |
| comment | text | Comment content |
| is_internal | boolean | Internal note flag |
| read_by_admin | boolean | Admin read status |
| read_by_user | boolean | User read status |

### Ticket Lifecycle

```
┌─────────┐    ┌─────────────┐    ┌─────────┐    ┌──────────┐    ┌────────┐
│  Open   │───▶│ In Progress │───▶│ Waiting │───▶│ Resolved │───▶│ Closed │
└─────────┘    └─────────────┘    └─────────┘    └──────────┘    └────────┘
```

### Model Scopes
```php
// Filter by module
SupportTicketModel::forModule('growfinance')->get();

// Filter by source
SupportTicketModel::investorTickets()->get();
SupportTicketModel::memberTickets()->get();
```

---

## Part 3: Module Integration

### Supported Modules

| Module | Value | Description |
|--------|-------|-------------|
| MyGrowNet | `mygrownet` | Main platform |
| GrowFinance | `growfinance` | Business/accounting |
| GrowBiz | `growbiz` | Team/employee management |
| Admin | `admin` | Admin communications |

### Adding a New Module

#### Step 1: Create Message Controller
```php
namespace App\Http\Controllers\NewModule;

class MessageController extends Controller
{
    private const MODULE = 'newmodule';
    
    public function __construct(
        private GetInboxUseCase $getInboxUseCase,
        private SendMessageUseCase $sendMessageUseCase,
    ) {}
    
    public function index(Request $request)
    {
        $messages = $this->getInboxUseCase->execute(
            auth()->id(),
            100,
            0,
            self::MODULE
        );
        
        return Inertia::render('NewModule/Messages/Index', [
            'messages' => $messages,
        ]);
    }
    
    public function store(Request $request)
    {
        $dto = new SendMessageDTO(
            senderId: auth()->id(),
            recipientId: $request->recipient_id,
            subject: $request->subject,
            body: $request->body,
            module: self::MODULE,
            metadata: $request->metadata,
        );
        
        $this->sendMessageUseCase->execute($dto);
        return back()->with('success', 'Message sent');
    }
}
```

#### Step 2: Create Support Controller
```php
namespace App\Http\Controllers\NewModule;

class SupportController extends Controller
{
    private const MODULE = 'newmodule';
    
    public function index()
    {
        $tickets = SupportTicketModel::where('user_id', auth()->id())
            ->forModule(self::MODULE)
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return Inertia::render('NewModule/Support/Index', [
            'tickets' => $tickets,
            'categories' => $this->getCategories(),
        ]);
    }
    
    public function store(Request $request)
    {
        $ticket = SupportTicketModel::create([
            'user_id' => auth()->id(),
            'category' => $request->category,
            'source' => 'member',
            'module' => self::MODULE,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
            'subject' => $request->subject,
            'description' => $request->description,
        ]);
        
        return back()->with('success', 'Ticket created');
    }
    
    private function getCategories(): array
    {
        return [
            ['value' => 'general', 'label' => 'General Help'],
            ['value' => 'feature', 'label' => 'Feature Request'],
            ['value' => 'bug', 'label' => 'Bug Report'],
        ];
    }
}
```

#### Step 3: Add Routes
```php
// Messages
Route::prefix('newmodule/messages')->name('newmodule.messages.')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('/{id}', [MessageController::class, 'show'])->name('show');
    Route::post('/', [MessageController::class, 'store'])->name('store');
    Route::post('/{id}/read', [MessageController::class, 'markAsRead'])->name('read');
});

// Support
Route::prefix('newmodule/support')->name('newmodule.support.')->group(function () {
    Route::get('/', [SupportController::class, 'index'])->name('index');
    Route::get('/create', [SupportController::class, 'create'])->name('create');
    Route::post('/', [SupportController::class, 'store'])->name('store');
    Route::get('/{id}', [SupportController::class, 'show'])->name('show');
    Route::post('/{id}/comment', [SupportController::class, 'addComment'])->name('comment');
});
```

#### Step 4: Create Vue Pages
Copy from existing module and customize layout/styling.

---

## Part 4: Metadata Examples

### GrowFinance Context
```php
// Message metadata
$metadata = [
    'context_type' => 'invoice',
    'context_id' => 123,
    'invoice_number' => 'INV-001',
];

// Ticket metadata
$metadata = [
    'related_invoice' => 'INV-001',
    'customer_id' => 456,
];
```

### GrowBiz Context
```php
$metadata = [
    'context_type' => 'task',
    'context_id' => 456,
    'project_id' => 789,
];
```

---

## Part 5: Best Practices

### Messaging
1. **Always specify module** when sending messages from a specific app
2. **Use metadata** for app-specific data that needs to be preserved
3. **Filter by module** when displaying messages in app-specific views
4. **Don't filter by module** in admin views to see all messages

### Support Tickets
1. **Always set module** when creating tickets from a specific app
2. **Use category prefixes** for easy filtering (e.g., `growfinance_invoices`)
3. **Include context in subject** (e.g., "[GrowFinance] Invoice Issue")
4. **Use metadata** for app-specific context
5. **Track read status** to show unread indicators

---

## Changelog

### December 3, 2025
- Consolidated messaging and support documentation
- Added module integration guide
- Added metadata examples
