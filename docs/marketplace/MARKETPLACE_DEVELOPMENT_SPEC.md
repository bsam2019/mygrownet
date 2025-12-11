# MyGrowNet Marketplace – Complete Development Specification

**Last Updated:** December 11, 2025  
**Status:** Development Planning  
**Source:** MARKETPLACE_CONCEPT.md  
**Stack:** Laravel 12 + Vue.js 3 + TypeScript

---

## Table of Contents

1. [System Architecture](#a-system-architecture)
2. [Database Schema](#b-database-schema)
3. [API Endpoints](#c-api-endpoints)
4. [Development Tasks](#d-development-tasks)
5. [Sprint Plan (Phased)](#e-sprint-plan-phased-implementation)
6. [Feature Breakdown](#f-feature-breakdown)
7. [UI/UX Flows](#g-uiux-flows)
8. [Developer Notes](#h-developer-notes)

---

# A. System Architecture

## A.1 Backend Structure (Laravel)

### Directory Structure

```
app/
├── Domain/
│   └── Marketplace/
│       ├── Entities/
│       │   ├── Vendor.php
│       │   ├── Product.php
│       │   ├── Category.php
│       │   ├── Order.php
│       │   ├── OrderItem.php
│       │   ├── Cart.php
│       │   ├── Review.php
│       │   └── DeliveryOption.php
│       ├── ValueObjects/
│       │   ├── Money.php
│       │   ├── Address.php
│       │   ├── OrderStatus.php
│       │   └── VendorStatus.php
│       ├── Services/
│       │   ├── VendorService.php
│       │   ├── ProductService.php
│       │   ├── OrderService.php
│       │   ├── CartService.php
│       │   ├── PaymentService.php
│       │   ├── DeliveryService.php
│       │   ├── CommissionService.php
│       │   ├── LoyaltyService.php
│       │   └── SearchService.php
│       ├── Repositories/
│       │   ├── VendorRepositoryInterface.php
│       │   ├── ProductRepositoryInterface.php
│       │   ├── OrderRepositoryInterface.php
│       │   └── CategoryRepositoryInterface.php
│       └── Events/
│           ├── VendorRegistered.php
│           ├── ProductCreated.php
│           ├── OrderPlaced.php
│           ├── OrderStatusChanged.php
│           ├── PaymentReceived.php
│           └── ReviewSubmitted.php
│
├── Infrastructure/
│   └── Marketplace/
│       ├── Persistence/
│       │   ├── Eloquent/
│       │   │   ├── Vendor.php
│       │   │   ├── VendorProfile.php
│       │   │   ├── VendorKyc.php
│       │   │   ├── Product.php
│       │   │   ├── ProductImage.php
│       │   │   ├── ProductVariant.php
│       │   │   ├── Category.php
│       │   │   ├── Order.php
│       │   │   ├── OrderItem.php
│       │   │   ├── Cart.php
│       │   │   ├── CartItem.php
│       │   │   ├── Review.php
│       │   │   ├── DeliveryZone.php
│       │   │   ├── DeliveryOption.php
│       │   │   ├── Commission.php
│       │   │   ├── VendorSubscription.php
│       │   │   └── PickupStation.php
│       │   └── Repositories/
│       │       ├── EloquentVendorRepository.php
│       │       ├── EloquentProductRepository.php
│       │       ├── EloquentOrderRepository.php
│       │       └── EloquentCategoryRepository.php
│       ├── Payment/
│       │   ├── PaymentGatewayInterface.php
│       │   ├── MtnMomoGateway.php
│       │   ├── AirtelMoneyGateway.php
│       │   └── CardPaymentGateway.php
│       ├── Delivery/
│       │   ├── DeliveryProviderInterface.php
│       │   ├── UlendoProvider.php
│       │   ├── MandoProvider.php
│       │   └── VendorSelfDelivery.php
│       └── Integrations/
│           ├── WalletIntegration.php
│           ├── BizBoostIntegration.php
│           └── GrowFinanceIntegration.php
│
├── Application/
│   └── Marketplace/
│       ├── UseCases/
│       │   ├── Vendor/
│       │   │   ├── RegisterVendorUseCase.php
│       │   │   ├── UpdateVendorProfileUseCase.php
│       │   │   ├── VerifyVendorKycUseCase.php
│       │   │   └── GetVendorDashboardUseCase.php
│       │   ├── Product/
│       │   │   ├── CreateProductUseCase.php
│       │   │   ├── UpdateProductUseCase.php
│       │   │   ├── DeleteProductUseCase.php
│       │   │   └── SearchProductsUseCase.php
│       │   ├── Order/
│       │   │   ├── PlaceOrderUseCase.php
│       │   │   ├── UpdateOrderStatusUseCase.php
│       │   │   ├── CancelOrderUseCase.php
│       │   │   └── ProcessRefundUseCase.php
│       │   └── Cart/
│       │       ├── AddToCartUseCase.php
│       │       ├── UpdateCartUseCase.php
│       │       └── CheckoutUseCase.php
│       ├── DTOs/
│       │   ├── VendorDTO.php
│       │   ├── ProductDTO.php
│       │   ├── OrderDTO.php
│       │   ├── CartDTO.php
│       │   └── CheckoutDTO.php
│       └── Commands/
│           ├── ProcessPendingOrdersCommand.php
│           ├── CalculateCommissionsCommand.php
│           └── SendOrderRemindersCommand.php
│
└── Presentation/
    └── Http/
        └── Controllers/
            └── Marketplace/
                ├── Buyer/
                │   ├── ProductController.php
                │   ├── CategoryController.php
                │   ├── CartController.php
                │   ├── CheckoutController.php
                │   ├── OrderController.php
                │   └── ReviewController.php
                ├── Vendor/
                │   ├── DashboardController.php
                │   ├── ProductController.php
                │   ├── OrderController.php
                │   ├── ProfileController.php
                │   ├── AnalyticsController.php
                │   └── SettingsController.php
                └── Admin/
                    ├── DashboardController.php
                    ├── VendorController.php
                    ├── ProductController.php
                    ├── OrderController.php
                    ├── CategoryController.php
                    ├── CommissionController.php
                    └── ReportController.php
        └── Requests/
            └── Marketplace/
                ├── VendorRegistrationRequest.php
                ├── ProductRequest.php
                ├── CheckoutRequest.php
                └── ReviewRequest.php
```


## A.2 Frontend Structure (Vue.js)

### Directory Structure

```
resources/js/
├── Pages/
│   └── Marketplace/
│       ├── Home.vue                    # Marketplace homepage
│       ├── Browse/
│       │   ├── Index.vue               # Product listing
│       │   ├── Category.vue            # Category view
│       │   └── Search.vue              # Search results
│       ├── Product/
│       │   └── Show.vue                # Product detail
│       ├── Cart/
│       │   └── Index.vue               # Shopping cart
│       ├── Checkout/
│       │   ├── Index.vue               # Checkout flow
│       │   ├── Payment.vue             # Payment step
│       │   └── Confirmation.vue        # Order confirmation
│       ├── Orders/
│       │   ├── Index.vue               # Order history
│       │   └── Show.vue                # Order detail
│       ├── Vendor/
│       │   ├── Register.vue            # Vendor registration
│       │   ├── Dashboard.vue           # Vendor dashboard
│       │   ├── Products/
│       │   │   ├── Index.vue           # Product list
│       │   │   ├── Create.vue          # Add product
│       │   │   └── Edit.vue            # Edit product
│       │   ├── Orders/
│       │   │   ├── Index.vue           # Order management
│       │   │   └── Show.vue            # Order detail
│       │   ├── Analytics.vue           # Sales analytics
│       │   ├── Profile.vue             # Vendor profile
│       │   └── Settings.vue            # Vendor settings
│       └── Admin/
│           ├── Dashboard.vue           # Admin dashboard
│           ├── Vendors/
│           │   ├── Index.vue           # Vendor list
│           │   ├── Show.vue            # Vendor detail
│           │   └── Kyc.vue             # KYC verification
│           ├── Products/
│           │   ├── Index.vue           # Product moderation
│           │   └── Show.vue            # Product detail
│           ├── Orders/
│           │   └── Index.vue           # All orders
│           ├── Categories/
│           │   └── Index.vue           # Category management
│           ├── Commissions/
│           │   └── Index.vue           # Commission settings
│           └── Reports/
│               └── Index.vue           # Reports & analytics
│
├── Components/
│   └── Marketplace/
│       ├── Product/
│       │   ├── ProductCard.vue         # Product grid card
│       │   ├── ProductList.vue         # Product list item
│       │   ├── ProductGallery.vue      # Image gallery
│       │   ├── ProductInfo.vue         # Product details
│       │   ├── ProductReviews.vue      # Reviews section
│       │   ├── AddToCartButton.vue     # Add to cart
│       │   └── QuantitySelector.vue    # Quantity input
│       ├── Category/
│       │   ├── CategoryCard.vue        # Category card
│       │   ├── CategoryNav.vue         # Category navigation
│       │   └── CategoryFilter.vue      # Category filter
│       ├── Cart/
│       │   ├── CartItem.vue            # Cart line item
│       │   ├── CartSummary.vue         # Cart totals
│       │   ├── CartDrawer.vue          # Slide-out cart
│       │   └── CartBadge.vue           # Cart icon badge
│       ├── Checkout/
│       │   ├── AddressForm.vue         # Delivery address
│       │   ├── DeliveryOptions.vue     # Delivery selection
│       │   ├── PaymentMethods.vue      # Payment selection
│       │   └── OrderSummary.vue        # Order summary
│       ├── Order/
│       │   ├── OrderCard.vue           # Order summary card
│       │   ├── OrderTimeline.vue       # Order status timeline
│       │   ├── OrderItems.vue          # Order items list
│       │   └── TrackingInfo.vue        # Delivery tracking
│       ├── Vendor/
│       │   ├── VendorCard.vue          # Vendor profile card
│       │   ├── VendorBadge.vue         # Verified badge
│       │   ├── SalesChart.vue          # Sales analytics
│       │   ├── OrdersTable.vue         # Orders table
│       │   └── ProductForm.vue         # Product form
│       ├── Search/
│       │   ├── SearchBar.vue           # Search input
│       │   ├── SearchFilters.vue       # Filter sidebar
│       │   ├── ProvinceFilter.vue      # Province selector
│       │   └── PriceFilter.vue         # Price range
│       ├── Review/
│       │   ├── ReviewCard.vue          # Single review
│       │   ├── ReviewForm.vue          # Write review
│       │   ├── RatingStars.vue         # Star rating
│       │   └── RatingSummary.vue       # Rating breakdown
│       └── Common/
│           ├── PriceDisplay.vue        # Price formatting
│           ├── ProvinceSelector.vue    # Province dropdown
│           ├── ImageUploader.vue       # Image upload
│           ├── Pagination.vue          # Pagination
│           └── EmptyState.vue          # Empty states
│
├── Composables/
│   └── Marketplace/
│       ├── useCart.ts                  # Cart state
│       ├── useProducts.ts              # Product queries
│       ├── useOrders.ts                # Order management
│       ├── useVendor.ts                # Vendor state
│       ├── useCheckout.ts              # Checkout flow
│       ├── useSearch.ts                # Search & filter
│       └── useReviews.ts               # Reviews
│
└── types/
    └── marketplace.ts                  # TypeScript interfaces
```

---

## A.3 TypeScript Interfaces

```typescript
// resources/js/types/marketplace.ts

// ==================== VENDOR ====================
export interface Vendor {
  id: number;
  userId: number;
  businessName: string;
  slug: string;
  description: string | null;
  logo: string | null;
  coverImage: string | null;
  province: string;
  city: string;
  phone: string;
  email: string;
  status: VendorStatus;
  isVerified: boolean;
  rating: number;
  totalSales: number;
  totalProducts: number;
  commissionRate: number;
  subscriptionTier: SubscriptionTier;
  createdAt: string;
}

export type VendorStatus = 'pending' | 'active' | 'suspended' | 'rejected';
export type SubscriptionTier = 'free' | 'starter' | 'professional' | 'enterprise';

export interface VendorKyc {
  id: number;
  vendorId: number;
  nrcFront: string;
  nrcBack: string;
  businessCertificate: string | null;
  proofOfAddress: string | null;
  status: 'pending' | 'approved' | 'rejected';
  rejectionReason: string | null;
  verifiedAt: string | null;
}

export interface VendorDashboard {
  vendor: Vendor;
  stats: {
    totalOrders: number;
    pendingOrders: number;
    totalRevenue: number;
    thisMonthRevenue: number;
    totalProducts: number;
    activeProducts: number;
    averageRating: number;
    totalReviews: number;
  };
  recentOrders: Order[];
  topProducts: Product[];
  salesChart: ChartData;
}

// ==================== PRODUCT ====================
export interface Product {
  id: number;
  vendorId: number;
  categoryId: number;
  name: string;
  slug: string;
  description: string;
  price: number;
  compareAtPrice: number | null;
  sku: string | null;
  quantity: number;
  isActive: boolean;
  isFeatured: boolean;
  images: ProductImage[];
  variants: ProductVariant[];
  category: Category;
  vendor: Vendor;
  rating: number;
  reviewCount: number;
  soldCount: number;
  createdAt: string;
}

export interface ProductImage {
  id: number;
  productId: number;
  url: string;
  altText: string | null;
  order: number;
  isPrimary: boolean;
}

export interface ProductVariant {
  id: number;
  productId: number;
  name: string;
  sku: string | null;
  price: number;
  quantity: number;
  attributes: Record<string, string>;
}

export interface ProductFilters {
  categoryId?: number;
  province?: string;
  minPrice?: number;
  maxPrice?: number;
  rating?: number;
  vendorId?: number;
  search?: string;
  sortBy?: 'newest' | 'price_low' | 'price_high' | 'popular' | 'rating';
}

// ==================== CATEGORY ====================
export interface Category {
  id: number;
  parentId: number | null;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  image: string | null;
  commissionRate: number;
  isActive: boolean;
  productCount: number;
  children?: Category[];
}

// ==================== CART ====================
export interface Cart {
  id: number;
  userId: number;
  items: CartItem[];
  subtotal: number;
  itemCount: number;
}

export interface CartItem {
  id: number;
  cartId: number;
  productId: number;
  variantId: number | null;
  quantity: number;
  price: number;
  product: Product;
  variant: ProductVariant | null;
}

// ==================== ORDER ====================
export interface Order {
  id: number;
  orderNumber: string;
  userId: number;
  vendorId: number;
  status: OrderStatus;
  paymentStatus: PaymentStatus;
  paymentMethod: PaymentMethod;
  subtotal: number;
  deliveryFee: number;
  discount: number;
  total: number;
  items: OrderItem[];
  deliveryAddress: Address;
  deliveryOption: DeliveryOption;
  trackingNumber: string | null;
  notes: string | null;
  vendor: Vendor;
  createdAt: string;
  paidAt: string | null;
  shippedAt: string | null;
  deliveredAt: string | null;
}

export type OrderStatus = 
  | 'pending' 
  | 'confirmed' 
  | 'processing' 
  | 'shipped' 
  | 'delivered' 
  | 'cancelled' 
  | 'refunded';

export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded';
export type PaymentMethod = 'mtn_momo' | 'airtel_money' | 'card' | 'wallet' | 'cod';

export interface OrderItem {
  id: number;
  orderId: number;
  productId: number;
  variantId: number | null;
  name: string;
  sku: string | null;
  price: number;
  quantity: number;
  total: number;
  product: Product;
}

// ==================== DELIVERY ====================
export interface Address {
  fullName: string;
  phone: string;
  province: string;
  city: string;
  area: string;
  streetAddress: string;
  landmark: string | null;
}

export interface DeliveryOption {
  id: number;
  name: string;
  type: 'courier' | 'vendor_delivery' | 'pickup';
  provider: string | null;
  estimatedDays: string;
  price: number;
  description: string | null;
}

export interface DeliveryZone {
  id: number;
  province: string;
  city: string | null;
  deliveryFee: number;
  estimatedDays: number;
  isActive: boolean;
}

export interface PickupStation {
  id: number;
  name: string;
  province: string;
  city: string;
  address: string;
  phone: string;
  operatingHours: string;
  isActive: boolean;
}

// ==================== REVIEW ====================
export interface Review {
  id: number;
  productId: number;
  userId: number;
  orderId: number;
  rating: number;
  title: string | null;
  comment: string;
  images: string[];
  isVerifiedPurchase: boolean;
  helpfulCount: number;
  createdAt: string;
  user: {
    id: number;
    name: string;
    avatar: string | null;
  };
}

// ==================== CHECKOUT ====================
export interface CheckoutData {
  cart: Cart;
  deliveryAddress: Address;
  deliveryOption: DeliveryOption;
  paymentMethod: PaymentMethod;
  useWalletBalance: boolean;
  walletAmount: number;
  couponCode: string | null;
  notes: string | null;
}

export interface CheckoutSummary {
  subtotal: number;
  deliveryFee: number;
  walletDiscount: number;
  couponDiscount: number;
  total: number;
  loyaltyPointsEarned: number;
}

// ==================== ADMIN ====================
export interface AdminDashboard {
  stats: {
    totalVendors: number;
    activeVendors: number;
    pendingKyc: number;
    totalProducts: number;
    totalOrders: number;
    totalRevenue: number;
    totalCommissions: number;
    thisMonthOrders: number;
    thisMonthRevenue: number;
  };
  recentOrders: Order[];
  pendingVendors: Vendor[];
  topVendors: Vendor[];
  salesChart: ChartData;
  categoryBreakdown: CategoryStats[];
}

export interface CategoryStats {
  category: Category;
  productCount: number;
  orderCount: number;
  revenue: number;
}

export interface ChartData {
  labels: string[];
  datasets: {
    label: string;
    data: number[];
  }[];
}

// ==================== COMMISSION ====================
export interface Commission {
  id: number;
  orderId: number;
  vendorId: number;
  orderTotal: number;
  commissionRate: number;
  commissionAmount: number;
  status: 'pending' | 'paid' | 'cancelled';
  paidAt: string | null;
}

export interface CommissionSettings {
  defaultRate: number;
  categoryRates: Record<number, number>;
  vendorOverrides: Record<number, number>;
}
```

---

# B. Database Schema

## B.1 Core Tables

```php
// Migration 1: create_marketplace_categories_table
Schema::create('marketplace_categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')->nullable()->constrained('marketplace_categories')->nullOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('icon')->nullable();
    $table->string('image')->nullable();
    $table->decimal('commission_rate', 5, 2)->default(10.00);
    $table->boolean('is_active')->default(true);
    $table->integer('order')->default(0);
    $table->timestamps();
});

// Migration 2: create_marketplace_vendors_table
Schema::create('marketplace_vendors', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('business_name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('logo')->nullable();
    $table->string('cover_image')->nullable();
    $table->string('province');
    $table->string('city');
    $table->string('phone');
    $table->string('email');
    $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');
    $table->boolean('is_verified')->default(false);
    $table->decimal('rating', 3, 2)->default(0);
    $table->integer('total_sales')->default(0);
    $table->integer('total_products')->default(0);
    $table->decimal('commission_rate', 5, 2)->nullable(); // Override default
    $table->enum('subscription_tier', ['free', 'starter', 'professional', 'enterprise'])->default('free');
    $table->timestamp('subscription_expires_at')->nullable();
    $table->timestamps();
    
    $table->index(['status', 'is_verified']);
    $table->index('province');
});

// Migration 3: create_marketplace_vendor_kyc_table
Schema::create('marketplace_vendor_kyc', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained('marketplace_vendors')->cascadeOnDelete();
    $table->string('nrc_front');
    $table->string('nrc_back');
    $table->string('business_certificate')->nullable();
    $table->string('proof_of_address')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});

// Migration 4: create_marketplace_products_table
Schema::create('marketplace_products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained('marketplace_vendors')->cascadeOnDelete();
    $table->foreignId('category_id')->constrained('marketplace_categories');
    $table->string('name');
    $table->string('slug');
    $table->text('description');
    $table->decimal('price', 12, 2);
    $table->decimal('compare_at_price', 12, 2)->nullable();
    $table->string('sku')->nullable();
    $table->integer('quantity')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->boolean('is_approved')->default(false);
    $table->decimal('rating', 3, 2)->default(0);
    $table->integer('review_count')->default(0);
    $table->integer('sold_count')->default(0);
    $table->integer('view_count')->default(0);
    $table->json('attributes')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['vendor_id', 'slug']);
    $table->index(['category_id', 'is_active', 'is_approved']);
    $table->index(['vendor_id', 'is_active']);
    $table->fullText(['name', 'description']);
});

// Migration 5: create_marketplace_product_images_table
Schema::create('marketplace_product_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('marketplace_products')->cascadeOnDelete();
    $table->string('url');
    $table->string('alt_text')->nullable();
    $table->integer('order')->default(0);
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
});

// Migration 6: create_marketplace_product_variants_table
Schema::create('marketplace_product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('marketplace_products')->cascadeOnDelete();
    $table->string('name');
    $table->string('sku')->nullable();
    $table->decimal('price', 12, 2);
    $table->integer('quantity')->default(0);
    $table->json('attributes'); // {"size": "XL", "color": "Red"}
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

## B.2 Order Tables

```php
// Migration 7: create_marketplace_carts_table
Schema::create('marketplace_carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('session_id')->nullable();
    $table->timestamps();
    
    $table->index(['user_id']);
    $table->index(['session_id']);
});

// Migration 8: create_marketplace_cart_items_table
Schema::create('marketplace_cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained('marketplace_carts')->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('marketplace_products')->cascadeOnDelete();
    $table->foreignId('variant_id')->nullable()->constrained('marketplace_product_variants')->nullOnDelete();
    $table->integer('quantity');
    $table->decimal('price', 12, 2);
    $table->timestamps();
    
    $table->unique(['cart_id', 'product_id', 'variant_id']);
});

// Migration 9: create_marketplace_orders_table
Schema::create('marketplace_orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('vendor_id')->constrained('marketplace_vendors');
    $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
    $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
    $table->enum('payment_method', ['mtn_momo', 'airtel_money', 'card', 'wallet', 'cod']);
    $table->string('payment_reference')->nullable();
    $table->decimal('subtotal', 12, 2);
    $table->decimal('delivery_fee', 12, 2)->default(0);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('total', 12, 2);
    $table->json('delivery_address');
    $table->foreignId('delivery_option_id')->nullable()->constrained('marketplace_delivery_options')->nullOnDelete();
    $table->string('tracking_number')->nullable();
    $table->text('notes')->nullable();
    $table->text('cancellation_reason')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index(['vendor_id', 'status']);
    $table->index(['created_at']);
});

// Migration 10: create_marketplace_order_items_table
Schema::create('marketplace_order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('marketplace_orders')->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('marketplace_products');
    $table->foreignId('variant_id')->nullable()->constrained('marketplace_product_variants')->nullOnDelete();
    $table->string('name');
    $table->string('sku')->nullable();
    $table->decimal('price', 12, 2);
    $table->integer('quantity');
    $table->decimal('total', 12, 2);
    $table->timestamps();
});
```

## B.3 Delivery & Review Tables

```php
// Migration 11: create_marketplace_delivery_zones_table
Schema::create('marketplace_delivery_zones', function (Blueprint $table) {
    $table->id();
    $table->string('province');
    $table->string('city')->nullable();
    $table->decimal('delivery_fee', 10, 2);
    $table->integer('estimated_days');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->unique(['province', 'city']);
});

// Migration 12: create_marketplace_delivery_options_table
Schema::create('marketplace_delivery_options', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('type', ['courier', 'vendor_delivery', 'pickup']);
    $table->string('provider')->nullable();
    $table->string('estimated_days');
    $table->decimal('base_price', 10, 2);
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration 13: create_marketplace_pickup_stations_table
Schema::create('marketplace_pickup_stations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('province');
    $table->string('city');
    $table->text('address');
    $table->string('phone');
    $table->string('operating_hours');
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration 14: create_marketplace_reviews_table
Schema::create('marketplace_reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('marketplace_products')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->constrained('marketplace_orders');
    $table->tinyInteger('rating'); // 1-5
    $table->string('title')->nullable();
    $table->text('comment');
    $table->json('images')->nullable();
    $table->boolean('is_verified_purchase')->default(true);
    $table->integer('helpful_count')->default(0);
    $table->boolean('is_approved')->default(true);
    $table->timestamps();
    
    $table->unique(['product_id', 'user_id', 'order_id']);
    $table->index(['product_id', 'is_approved']);
});

// Migration 15: create_marketplace_commissions_table
Schema::create('marketplace_commissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('marketplace_orders')->cascadeOnDelete();
    $table->foreignId('vendor_id')->constrained('marketplace_vendors');
    $table->decimal('order_total', 12, 2);
    $table->decimal('commission_rate', 5, 2);
    $table->decimal('commission_amount', 12, 2);
    $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    
    $table->index(['vendor_id', 'status']);
});

// Migration 16: create_marketplace_vendor_subscriptions_table
Schema::create('marketplace_vendor_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained('marketplace_vendors')->cascadeOnDelete();
    $table->enum('tier', ['starter', 'professional', 'enterprise']);
    $table->decimal('amount', 10, 2);
    $table->enum('billing_cycle', ['monthly', 'yearly']);
    $table->timestamp('starts_at');
    $table->timestamp('ends_at');
    $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
    $table->string('payment_reference')->nullable();
    $table->timestamps();
});

// Migration 17: create_marketplace_wishlists_table
Schema::create('marketplace_wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained('marketplace_products')->cascadeOnDelete();
    $table->timestamps();
    
    $table->unique(['user_id', 'product_id']);
});
```

---

# C. API Endpoints

## C.1 Public/Buyer Endpoints

### Products & Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/products` | List products with filters |
| GET | `/api/marketplace/products/{slug}` | Get product details |
| GET | `/api/marketplace/products/featured` | Get featured products |
| GET | `/api/marketplace/products/search` | Search products |
| GET | `/api/marketplace/categories` | List all categories |
| GET | `/api/marketplace/categories/{slug}` | Get category with products |
| GET | `/api/marketplace/categories/{slug}/products` | Products in category |

### Cart

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/cart` | Get current cart |
| POST | `/api/marketplace/cart/items` | Add item to cart |
| PUT | `/api/marketplace/cart/items/{id}` | Update cart item quantity |
| DELETE | `/api/marketplace/cart/items/{id}` | Remove item from cart |
| DELETE | `/api/marketplace/cart` | Clear cart |

### Checkout & Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/marketplace/checkout/validate` | Validate checkout data |
| GET | `/api/marketplace/checkout/delivery-options` | Get delivery options |
| POST | `/api/marketplace/checkout/calculate` | Calculate totals |
| POST | `/api/marketplace/checkout/place-order` | Place order |
| GET | `/api/marketplace/orders` | List user orders |
| GET | `/api/marketplace/orders/{orderNumber}` | Get order details |
| POST | `/api/marketplace/orders/{orderNumber}/cancel` | Cancel order |

### Reviews

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/products/{id}/reviews` | Get product reviews |
| POST | `/api/marketplace/products/{id}/reviews` | Submit review |
| POST | `/api/marketplace/reviews/{id}/helpful` | Mark review helpful |

### Vendors & Locations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/vendors/{slug}` | Get vendor profile |
| GET | `/api/marketplace/vendors/{slug}/products` | Vendor's products |
| GET | `/api/marketplace/provinces` | List provinces |
| GET | `/api/marketplace/pickup-stations` | List pickup stations |

---

## C.2 Vendor Endpoints

### Dashboard & Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/vendor/dashboard` | Vendor dashboard data |
| GET | `/api/marketplace/vendor/profile` | Get vendor profile |
| PUT | `/api/marketplace/vendor/profile` | Update profile |
| POST | `/api/marketplace/vendor/kyc` | Submit KYC documents |
| GET | `/api/marketplace/vendor/kyc/status` | Check KYC status |

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/vendor/products` | List vendor products |
| POST | `/api/marketplace/vendor/products` | Create product |
| GET | `/api/marketplace/vendor/products/{id}` | Get product |
| PUT | `/api/marketplace/vendor/products/{id}` | Update product |
| DELETE | `/api/marketplace/vendor/products/{id}` | Delete product |
| POST | `/api/marketplace/vendor/products/{id}/images` | Upload images |
| DELETE | `/api/marketplace/vendor/products/{id}/images/{imageId}` | Delete image |
| POST | `/api/marketplace/vendor/products/{id}/variants` | Add variant |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/vendor/orders` | List vendor orders |
| GET | `/api/marketplace/vendor/orders/{id}` | Get order details |
| PUT | `/api/marketplace/vendor/orders/{id}/status` | Update order status |
| POST | `/api/marketplace/vendor/orders/{id}/ship` | Mark as shipped |

### Analytics

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/vendor/analytics/sales` | Sales analytics |
| GET | `/api/marketplace/vendor/analytics/products` | Product performance |
| GET | `/api/marketplace/vendor/analytics/orders` | Order analytics |

---

## C.3 Admin Endpoints

### Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/admin/dashboard` | Admin dashboard |
| GET | `/api/marketplace/admin/reports/sales` | Sales reports |
| GET | `/api/marketplace/admin/reports/vendors` | Vendor reports |

### Vendor Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/admin/vendors` | List all vendors |
| GET | `/api/marketplace/admin/vendors/{id}` | Get vendor details |
| PUT | `/api/marketplace/admin/vendors/{id}/status` | Update vendor status |
| GET | `/api/marketplace/admin/vendors/pending-kyc` | Pending KYC list |
| PUT | `/api/marketplace/admin/vendors/{id}/kyc/approve` | Approve KYC |
| PUT | `/api/marketplace/admin/vendors/{id}/kyc/reject` | Reject KYC |

### Product Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/admin/products` | List all products |
| GET | `/api/marketplace/admin/products/pending` | Pending approval |
| PUT | `/api/marketplace/admin/products/{id}/approve` | Approve product |
| PUT | `/api/marketplace/admin/products/{id}/reject` | Reject product |
| PUT | `/api/marketplace/admin/products/{id}/feature` | Feature product |

### Category Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/admin/categories` | List categories |
| POST | `/api/marketplace/admin/categories` | Create category |
| PUT | `/api/marketplace/admin/categories/{id}` | Update category |
| DELETE | `/api/marketplace/admin/categories/{id}` | Delete category |

### Order Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/admin/orders` | List all orders |
| GET | `/api/marketplace/admin/orders/{id}` | Get order details |
| PUT | `/api/marketplace/admin/orders/{id}/status` | Update status |
| POST | `/api/marketplace/admin/orders/{id}/refund` | Process refund |

### Commission Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/marketplace/admin/commissions` | List commissions |
| GET | `/api/marketplace/admin/commissions/settings` | Get settings |
| PUT | `/api/marketplace/admin/commissions/settings` | Update settings |
| POST | `/api/marketplace/admin/commissions/payout` | Process payout |

---

# D. Development Tasks

## D.1 Phase 0 – Preparation & Planning (1-2 weeks)

### Backend Tasks

| ID | Task | Description | Expected Output | Difficulty |
|----|------|-------------|-----------------|------------|
| P0-BE-001 | Project setup | Initialize Laravel module for marketplace | Base structure | Easy |
| P0-BE-002 | Database schema design | Finalize all table structures | Schema document | Medium |
| P0-BE-003 | API planning | Document all endpoints | API specification | Medium |
| P0-BE-004 | Service provider | Create MarketplaceServiceProvider | Provider class | Easy |
| P0-BE-005 | Config file | Create marketplace config | config/marketplace.php | Easy |

### Frontend Tasks

| ID | Task | Description | Expected Output | Difficulty |
|----|------|-------------|-----------------|------------|
| P0-FE-001 | UI wireframes | Create wireframes for all pages | Figma/wireframes | Medium |
| P0-FE-002 | Design system | Define colors, typography, components | Design tokens | Medium |
| P0-FE-003 | TypeScript types | Define all interfaces | types/marketplace.ts | Medium |
| P0-FE-004 | Route planning | Plan all frontend routes | Route document | Easy |

### Infrastructure Tasks

| ID | Task | Description | Expected Output | Difficulty |
|----|------|-------------|-----------------|------------|
| P0-INF-001 | Git setup | Create branch strategy | Git workflow | Easy |
| P0-INF-002 | Dev environment | Ensure Laravel + Vue setup | Working environment | Easy |
| P0-INF-003 | Storage setup | Configure file storage | Storage config | Easy |

---

## D.2 Phase 1 – MVP Development (4-6 weeks)

### Backend Tasks - Models & Migrations

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-BE-001 | Category migration | Create categories table | Migration file | None | Easy |
| P1-BE-002 | Vendor migrations | Create vendors, kyc tables | Migration files | None | Medium |
| P1-BE-003 | Product migrations | Create products, images, variants | Migration files | P1-BE-001, P1-BE-002 | Medium |
| P1-BE-004 | Cart migrations | Create carts, cart_items | Migration files | P1-BE-003 | Easy |
| P1-BE-005 | Order migrations | Create orders, order_items | Migration files | P1-BE-003 | Medium |
| P1-BE-006 | Delivery migrations | Create delivery zones, options | Migration files | None | Easy |
| P1-BE-007 | Commission migration | Create commissions table | Migration file | P1-BE-005 | Easy |
| P1-BE-008 | Eloquent models | Create all model classes | 15+ model files | P1-BE-001 to P1-BE-007 | Medium |
| P1-BE-009 | Model relationships | Define all relationships | Updated models | P1-BE-008 | Medium |
| P1-BE-010 | Model factories | Create factories for testing | Factory files | P1-BE-008 | Easy |
| P1-BE-011 | Seeders | Seed categories, delivery zones | Seeder files | P1-BE-008 | Medium |

### Backend Tasks - Services

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-BE-012 | VendorService | Vendor registration, profile | Service class | P1-BE-008 | Medium |
| P1-BE-013 | ProductService | CRUD, search, filtering | Service class | P1-BE-008 | Hard |
| P1-BE-014 | CartService | Cart management | Service class | P1-BE-008 | Medium |
| P1-BE-015 | OrderService | Order creation, status updates | Service class | P1-BE-008 | Hard |
| P1-BE-016 | PaymentService | Payment processing | Service class | P1-BE-015 | Hard |
| P1-BE-017 | CommissionService | Commission calculation | Service class | P1-BE-015 | Medium |
| P1-BE-018 | LoyaltyService | Points integration | Service class | P1-BE-015 | Medium |

### Backend Tasks - Controllers

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-BE-019 | CategoryController | Category listing | Controller | P1-BE-008 | Easy |
| P1-BE-020 | ProductController (Buyer) | Browse, search, detail | Controller | P1-BE-013 | Medium |
| P1-BE-021 | CartController | Cart operations | Controller | P1-BE-014 | Medium |
| P1-BE-022 | CheckoutController | Checkout flow | Controller | P1-BE-015, P1-BE-016 | Hard |
| P1-BE-023 | OrderController (Buyer) | Order history, details | Controller | P1-BE-015 | Medium |
| P1-BE-024 | VendorRegController | Vendor registration | Controller | P1-BE-012 | Medium |
| P1-BE-025 | VendorDashController | Vendor dashboard | Controller | P1-BE-012 | Medium |
| P1-BE-026 | VendorProductController | Product management | Controller | P1-BE-013 | Medium |
| P1-BE-027 | VendorOrderController | Order management | Controller | P1-BE-015 | Medium |
| P1-BE-028 | AdminDashController | Admin dashboard | Controller | P1-BE-008 | Medium |
| P1-BE-029 | AdminVendorController | Vendor management | Controller | P1-BE-012 | Medium |
| P1-BE-030 | AdminProductController | Product moderation | Controller | P1-BE-013 | Medium |
| P1-BE-031 | AdminOrderController | Order oversight | Controller | P1-BE-015 | Medium |
| P1-BE-032 | AdminCommissionController | Commission settings | Controller | P1-BE-017 | Medium |

### Backend Tasks - Payment Integration

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-BE-033 | Payment gateway interface | Abstract payment interface | Interface | None | Easy |
| P1-BE-034 | MTN MoMo integration | MTN Mobile Money | Gateway class | P1-BE-033 | Hard |
| P1-BE-035 | Airtel Money integration | Airtel Money | Gateway class | P1-BE-033 | Hard |
| P1-BE-036 | Wallet integration | MyGrow Wallet | Integration class | P1-BE-033 | Medium |

### Frontend Tasks - Core Pages

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-FE-001 | MarketplaceLayout | Main layout component | Layout.vue | None | Medium |
| P1-FE-002 | Home page | Marketplace homepage | Home.vue | P1-FE-001 | Medium |
| P1-FE-003 | Product listing | Browse products | Browse/Index.vue | P1-FE-001 | Medium |
| P1-FE-004 | Category page | Category view | Browse/Category.vue | P1-FE-003 | Medium |
| P1-FE-005 | Search page | Search results | Browse/Search.vue | P1-FE-003 | Medium |
| P1-FE-006 | Product detail | Product page | Product/Show.vue | P1-FE-001 | Hard |
| P1-FE-007 | Cart page | Shopping cart | Cart/Index.vue | P1-FE-001 | Medium |
| P1-FE-008 | Checkout flow | Checkout pages | Checkout/*.vue | P1-FE-007 | Hard |
| P1-FE-009 | Order history | User orders | Orders/Index.vue | P1-FE-001 | Medium |
| P1-FE-010 | Order detail | Order page | Orders/Show.vue | P1-FE-009 | Medium |

### Frontend Tasks - Vendor Pages

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-FE-011 | Vendor registration | Registration form | Vendor/Register.vue | P1-FE-001 | Medium |
| P1-FE-012 | Vendor dashboard | Dashboard page | Vendor/Dashboard.vue | P1-FE-001 | Hard |
| P1-FE-013 | Product list (vendor) | Vendor products | Vendor/Products/Index.vue | P1-FE-012 | Medium |
| P1-FE-014 | Add product | Product form | Vendor/Products/Create.vue | P1-FE-013 | Hard |
| P1-FE-015 | Edit product | Edit form | Vendor/Products/Edit.vue | P1-FE-014 | Medium |
| P1-FE-016 | Vendor orders | Order list | Vendor/Orders/Index.vue | P1-FE-012 | Medium |
| P1-FE-017 | Vendor order detail | Order page | Vendor/Orders/Show.vue | P1-FE-016 | Medium |
| P1-FE-018 | Vendor profile | Profile page | Vendor/Profile.vue | P1-FE-012 | Medium |

### Frontend Tasks - Admin Pages

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-FE-019 | Admin dashboard | Dashboard page | Admin/Dashboard.vue | P1-FE-001 | Hard |
| P1-FE-020 | Vendor list | Vendor management | Admin/Vendors/Index.vue | P1-FE-019 | Medium |
| P1-FE-021 | Vendor detail | Vendor page | Admin/Vendors/Show.vue | P1-FE-020 | Medium |
| P1-FE-022 | KYC verification | KYC review | Admin/Vendors/Kyc.vue | P1-FE-021 | Medium |
| P1-FE-023 | Product moderation | Product list | Admin/Products/Index.vue | P1-FE-019 | Medium |
| P1-FE-024 | Order management | Order list | Admin/Orders/Index.vue | P1-FE-019 | Medium |
| P1-FE-025 | Commission settings | Settings page | Admin/Commissions/Index.vue | P1-FE-019 | Medium |

### Frontend Tasks - Components

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-FE-026 | ProductCard | Product grid card | ProductCard.vue | None | Easy |
| P1-FE-027 | CategoryCard | Category card | CategoryCard.vue | None | Easy |
| P1-FE-028 | CartItem | Cart line item | CartItem.vue | None | Easy |
| P1-FE-029 | CartDrawer | Slide-out cart | CartDrawer.vue | P1-FE-028 | Medium |
| P1-FE-030 | AddToCartButton | Add to cart | AddToCartButton.vue | None | Easy |
| P1-FE-031 | SearchBar | Search input | SearchBar.vue | None | Easy |
| P1-FE-032 | SearchFilters | Filter sidebar | SearchFilters.vue | None | Medium |
| P1-FE-033 | ProvinceFilter | Province selector | ProvinceFilter.vue | None | Easy |
| P1-FE-034 | PriceDisplay | Price formatting | PriceDisplay.vue | None | Easy |
| P1-FE-035 | ImageUploader | Image upload | ImageUploader.vue | None | Medium |
| P1-FE-036 | ProductForm | Product form | ProductForm.vue | P1-FE-035 | Hard |
| P1-FE-037 | OrdersTable | Orders table | OrdersTable.vue | None | Medium |
| P1-FE-038 | SalesChart | Sales chart | SalesChart.vue | None | Medium |

### Frontend Tasks - Composables

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P1-FE-039 | useCart | Cart state | useCart.ts | None | Medium |
| P1-FE-040 | useProducts | Product queries | useProducts.ts | None | Medium |
| P1-FE-041 | useOrders | Order management | useOrders.ts | None | Medium |
| P1-FE-042 | useVendor | Vendor state | useVendor.ts | None | Medium |
| P1-FE-043 | useCheckout | Checkout flow | useCheckout.ts | None | Medium |
| P1-FE-044 | useSearch | Search & filter | useSearch.ts | None | Medium |

---

## D.3 Phase 2 – Beta Launch & Iteration (2-4 weeks)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P2-001 | Beta seller onboarding | Onboard 50-100 sellers | Active sellers | Phase 1 | Medium |
| P2-002 | Bug fixes | Fix issues from testing | Bug fixes | Phase 1 | Medium |
| P2-003 | UI/UX refinements | Polish based on feedback | UI updates | Phase 1 | Medium |
| P2-004 | Basic analytics | Admin dashboard analytics | Analytics views | P1-FE-019 | Medium |
| P2-005 | Performance optimization | Optimize slow queries | Faster responses | Phase 1 | Medium |
| P2-006 | Feedback collection | Implement feedback system | Feedback mechanism | Phase 1 | Easy |
| P2-007 | Category expansion | Add more categories | 5-10 categories | P1-BE-011 | Easy |
| P2-008 | Inventory management | Stock tracking improvements | Inventory features | P1-BE-013 | Medium |
| P2-009 | Order notifications | Email/SMS notifications | Notification system | P1-BE-015 | Medium |
| P2-010 | Mobile responsiveness | Ensure mobile-first | Responsive UI | Phase 1 FE | Medium |

---

## D.4 Phase 3 – Full Feature Rollout (6-8 weeks)

### BizBoost Integration

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P3-BB-001 | BizBoost integration service | Connect to BizBoost | Integration class | Phase 1 | Medium |
| P3-BB-002 | Auto-generated posters | Product poster generation | Poster feature | P3-BB-001 | Hard |
| P3-BB-003 | Social media sharing | Share to FB/WhatsApp | Share buttons | P3-BB-001 | Medium |
| P3-BB-004 | Marketing dashboard | Vendor marketing tools | Dashboard section | P3-BB-001 | Medium |

### Delivery System

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P3-DL-001 | Courier API interface | Abstract courier interface | Interface | None | Easy |
| P3-DL-002 | Ulendo integration | Ulendo courier API | Integration | P3-DL-001 | Hard |
| P3-DL-003 | Mando integration | Mando courier API | Integration | P3-DL-001 | Hard |
| P3-DL-004 | Delivery tracking | Real-time tracking | Tracking feature | P3-DL-002 | Hard |
| P3-DL-005 | Pickup stations | Pickup station management | Pickup feature | P1-BE-006 | Medium |
| P3-DL-006 | Delivery zones UI | Zone management admin | Admin UI | P1-BE-006 | Medium |

### Reviews & Ratings

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P3-RV-001 | Review migration | Create reviews table | Migration | Phase 1 | Easy |
| P3-RV-002 | ReviewService | Review management | Service class | P3-RV-001 | Medium |
| P3-RV-003 | ReviewController | Review endpoints | Controller | P3-RV-002 | Medium |
| P3-RV-004 | Review components | Review UI components | Vue components | P3-RV-003 | Medium |
| P3-RV-005 | Rating aggregation | Calculate product ratings | Rating logic | P3-RV-002 | Medium |

### Advanced Wallet & Loyalty

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P3-WL-001 | Points redemption | Redeem points at checkout | Redemption feature | P1-BE-018 | Medium |
| P3-WL-002 | Loyalty tiers | Tier-based benefits | Tier system | P3-WL-001 | Medium |
| P3-WL-003 | Points history | Points transaction history | History UI | P3-WL-001 | Easy |
| P3-WL-004 | Referral rewards | Buyer referral program | Referral feature | P3-WL-001 | Medium |

### Seller Training Portal

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P3-TR-001 | Training module | Training content system | Training feature | Phase 1 | Medium |
| P3-TR-002 | Certification system | Seller certification | Certification | P3-TR-001 | Medium |
| P3-TR-003 | Verified badge | Verified seller badge | Badge system | P3-TR-002 | Easy |
| P3-TR-004 | Training dashboard | Vendor training UI | Dashboard section | P3-TR-001 | Medium |

### Advanced Analytics

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P3-AN-001 | Sales analytics | Detailed sales reports | Analytics | Phase 1 | Medium |
| P3-AN-002 | Product analytics | Product performance | Analytics | Phase 1 | Medium |
| P3-AN-003 | Customer analytics | Customer insights | Analytics | Phase 1 | Medium |
| P3-AN-004 | Export reports | Export to Excel/PDF | Export feature | P3-AN-001 | Medium |

---

## D.5 Phase 4 – Growth & Optimization (Ongoing)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P4-001 | Mobile app prep | Prepare API for mobile | API optimization | Phase 3 | Medium |
| P4-002 | Capacitor setup | Mobile app framework | App skeleton | P4-001 | Hard |
| P4-003 | iOS app | iOS mobile app | iOS app | P4-002 | Hard |
| P4-004 | Android app | Android mobile app | Android app | P4-002 | Hard |
| P4-005 | Performance caching | Redis caching | Caching layer | Phase 3 | Medium |
| P4-006 | Queue optimization | Queue workers | Queue system | Phase 3 | Medium |
| P4-007 | CDN integration | Image CDN | CDN setup | Phase 3 | Medium |
| P4-008 | Search optimization | Elasticsearch | Search engine | Phase 3 | Hard |
| P4-009 | Regional expansion | Copperbelt, Southern | New regions | Phase 3 | Medium |
| P4-010 | GrowFinance integration | Rewards integration | Integration | Phase 3 | Medium |
| P4-011 | Venture Builder link | Investment opportunities | Integration | Phase 3 | Medium |

---

## D.6 Phase 5 – Long-Term Expansion (6-12 months)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| P5-001 | Category expansion | Electronics, agriculture, services | New categories | Phase 4 | Medium |
| P5-002 | Digital products | Digital product support | Digital delivery | Phase 4 | Hard |
| P5-003 | Services marketplace | Service bookings | Service feature | Phase 4 | Hard |
| P5-004 | Cross-border delivery | International shipping | Cross-border | Phase 4 | Hard |
| P5-005 | AI pricing suggestions | AI-powered pricing | AI feature | Phase 4 | Hard |
| P5-006 | AI ad generation | Auto-generate ads | AI feature | Phase 4 | Hard |
| P5-007 | Multi-language | Local language support | i18n | Phase 4 | Medium |
| P5-008 | Partner integrations | University, SME partners | Partnerships | Phase 4 | Medium |
| P5-009 | Regional expansion | Malawi, Botswana | New countries | Phase 4 | Hard |

---

# E. Sprint Plan (Phased Implementation)

## Phase 0: Preparation & Planning (Weeks 1-2)

### Sprint 0.1 (Week 1)

| Day | Focus | Tasks |
|-----|-------|-------|
| 1-2 | Planning | P0-BE-002, P0-BE-003 (Schema & API design) |
| 3-4 | Design | P0-FE-001, P0-FE-002 (Wireframes & design system) |
| 5 | Setup | P0-INF-001, P0-INF-002 (Git & environment) |

### Sprint 0.2 (Week 2)

| Day | Focus | Tasks |
|-----|-------|-------|
| 1-2 | Backend setup | P0-BE-001, P0-BE-004, P0-BE-005 |
| 3-4 | Frontend setup | P0-FE-003, P0-FE-004 |
| 5 | Review | Finalize MVP scope, review designs |

### Deliverables
- [ ] Finalized MVP feature list
- [ ] Database schema draft
- [ ] UI wireframes & mockups
- [ ] Dev environment ready
- [ ] Git repository and branching strategy

---

## Phase 1: MVP Development (Weeks 3-8)

### Sprint 1.1 (Week 3-4): Database & Core Backend

**Objectives:** Set up database, models, and core services

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P1-BE-001 to P1-BE-011 | All migrations, models, factories, seeders |
| Backend | P1-BE-012, P1-BE-013 | VendorService, ProductService |
| Frontend | P1-FE-001 | MarketplaceLayout |

**Deliverables:**
- [ ] All 17 database tables created
- [ ] All Eloquent models with relationships
- [ ] Category and delivery zone seeders
- [ ] Vendor and Product services
- [ ] Base layout component

---

### Sprint 1.2 (Week 5-6): Buyer Experience

**Objectives:** Complete buyer-facing features

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P1-BE-014, P1-BE-015 | CartService, OrderService |
| Backend | P1-BE-019 to P1-BE-023 | Buyer controllers |
| Backend | P1-BE-033 to P1-BE-036 | Payment integrations |
| Frontend | P1-FE-002 to P1-FE-010 | All buyer pages |
| Frontend | P1-FE-026 to P1-FE-034 | Core components |
| Frontend | P1-FE-039 to P1-FE-044 | Composables |

**Deliverables:**
- [ ] Product browsing and search
- [ ] Shopping cart functionality
- [ ] Checkout with mobile money
- [ ] Order history and tracking
- [ ] Wallet integration (basic)

**Demo Ready:**
- User can browse products
- User can add to cart and checkout
- User can pay with MTN MoMo/Airtel Money
- User can view order history

---

### Sprint 1.3 (Week 7-8): Vendor & Admin

**Objectives:** Complete vendor and admin features

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P1-BE-016, P1-BE-017, P1-BE-018 | Payment, Commission, Loyalty services |
| Backend | P1-BE-024 to P1-BE-032 | Vendor and Admin controllers |
| Frontend | P1-FE-011 to P1-FE-018 | Vendor pages |
| Frontend | P1-FE-019 to P1-FE-025 | Admin pages |
| Frontend | P1-FE-035 to P1-FE-038 | Remaining components |

**Deliverables:**
- [ ] Vendor registration with KYC
- [ ] Vendor product management
- [ ] Vendor order management
- [ ] Vendor dashboard with sales overview
- [ ] Admin dashboard
- [ ] Admin vendor/product/order management
- [ ] Commission settings

**Demo Ready:**
- Vendor can register and submit KYC
- Vendor can add/edit products
- Vendor can manage orders
- Admin can approve vendors
- Admin can view all orders
- Commission calculated on orders

---

## Phase 2: Beta Launch & Iteration (Weeks 9-12)

### Sprint 2.1 (Week 9-10): Beta Onboarding

| Category | Tasks | Description |
|----------|-------|-------------|
| Operations | P2-001 | Onboard 50-100 beta sellers |
| Backend | P2-002, P2-005 | Bug fixes, performance |
| Frontend | P2-003, P2-010 | UI polish, mobile responsive |

### Sprint 2.2 (Week 11-12): Iteration

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P2-004, P2-008, P2-009 | Analytics, inventory, notifications |
| Frontend | P2-006 | Feedback collection |
| Content | P2-007 | Category expansion |

**Deliverables:**
- [ ] Beta version with live transactions
- [ ] 50-100 active sellers
- [ ] Feedback report with improvement plan
- [ ] Basic analytics dashboard
- [ ] Email/SMS order notifications

---

## Phase 3: Full Feature Rollout (Weeks 13-20)

### Sprint 3.1 (Week 13-14): Reviews & Ratings

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P3-RV-001 to P3-RV-005 | Review system |
| Frontend | P3-RV-004 | Review components |

### Sprint 3.2 (Week 15-16): Delivery Integration

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P3-DL-001 to P3-DL-006 | Courier integrations, tracking |
| Frontend | Delivery UI | Tracking components |

### Sprint 3.3 (Week 17-18): BizBoost & Marketing

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P3-BB-001 to P3-BB-004 | BizBoost integration |
| Frontend | Marketing UI | Marketing dashboard |

### Sprint 3.4 (Week 19-20): Loyalty & Training

| Category | Tasks | Description |
|----------|-------|-------------|
| Backend | P3-WL-001 to P3-WL-004 | Advanced wallet |
| Backend | P3-TR-001 to P3-TR-004 | Training portal |
| Backend | P3-AN-001 to P3-AN-004 | Advanced analytics |

**Deliverables:**
- [ ] Full ratings & reviews system
- [ ] Multi-courier delivery with tracking
- [ ] Pickup stations
- [ ] BizBoost marketing tools
- [ ] Points redemption
- [ ] Seller training & certification
- [ ] Advanced analytics & reports

---

## Phase 4: Growth & Optimization (Weeks 21-28)

### Sprint 4.1-4.2 (Week 21-24): Mobile App

| Category | Tasks | Description |
|----------|-------|-------------|
| Mobile | P4-001 to P4-004 | Mobile app development |

### Sprint 4.3-4.4 (Week 25-28): Scaling

| Category | Tasks | Description |
|----------|-------|-------------|
| Infrastructure | P4-005 to P4-008 | Caching, queues, CDN, search |
| Expansion | P4-009 to P4-011 | Regional expansion, integrations |

**Deliverables:**
- [ ] iOS and Android mobile apps
- [ ] Redis caching layer
- [ ] Elasticsearch for search
- [ ] CDN for images
- [ ] Copperbelt & Southern Province expansion
- [ ] GrowFinance & Venture Builder integration

---

## Phase 5: Long-Term Expansion (Months 7-12)

### Quarterly Sprints

**Q3:** P5-001 to P5-003 (Category expansion, digital products, services)
**Q4:** P5-004 to P5-009 (Cross-border, AI features, regional expansion)

**Deliverables:**
- [ ] Digital products marketplace
- [ ] Services booking
- [ ] AI-powered features
- [ ] Multi-language support
- [ ] Regional expansion (Malawi, Botswana)

---

# F. Feature Breakdown

## F.1 Vendor Registration & KYC

### Overview
Complete vendor onboarding with identity verification.

### Backend Logic
```php
// Services
VendorService::register()
VendorService::submitKyc()
VendorService::approveKyc()
VendorService::rejectKyc()

// Key endpoints
POST /api/marketplace/vendor/register
POST /api/marketplace/vendor/kyc
GET  /api/marketplace/vendor/kyc/status
PUT  /api/marketplace/admin/vendors/{id}/kyc/approve
PUT  /api/marketplace/admin/vendors/{id}/kyc/reject
```

### Frontend Components
- `Vendor/Register.vue` - Registration form
- `Vendor/KycUpload.vue` - Document upload
- `Admin/Vendors/Kyc.vue` - KYC review

### Data Needed
```typescript
interface VendorRegistration {
  businessName: string;
  email: string;
  phone: string;
  province: string;
  city: string;
  description?: string;
}

interface KycSubmission {
  nrcFront: File;
  nrcBack: File;
  businessCertificate?: File;
  proofOfAddress?: File;
}
```

### Workflow
```
1. User clicks "Become a Seller"
2. Fill registration form (business name, contact, location)
3. Submit KYC documents (NRC front/back)
4. Status: Pending → Admin reviews
5. Admin approves/rejects with reason
6. Vendor notified via email/SMS
7. If approved: Vendor can add products
```

---

## F.2 Product Management

### Overview
Vendors can add, edit, and manage products with images and variants.

### Backend Logic
```php
// Services
ProductService::create()
ProductService::update()
ProductService::delete()
ProductService::uploadImages()
ProductService::addVariant()

// Key endpoints
GET    /api/marketplace/vendor/products
POST   /api/marketplace/vendor/products
PUT    /api/marketplace/vendor/products/{id}
DELETE /api/marketplace/vendor/products/{id}
POST   /api/marketplace/vendor/products/{id}/images
```

### Frontend Components
- `Vendor/Products/Index.vue` - Product list
- `Vendor/Products/Create.vue` - Add product
- `Vendor/Products/Edit.vue` - Edit product
- `Vendor/ProductForm.vue` - Reusable form
- `ImageUploader.vue` - Multi-image upload

### Data Needed
```typescript
interface ProductCreate {
  categoryId: number;
  name: string;
  description: string;
  price: number;
  compareAtPrice?: number;
  sku?: string;
  quantity: number;
  images: File[];
  variants?: ProductVariantCreate[];
}
```

### Workflow
```
1. Vendor opens Products page
2. Click "Add Product"
3. Fill form: name, description, price, category
4. Upload images (drag & drop, max 5)
5. Add variants if needed (size, color)
6. Save → Product pending approval (MVP: auto-approve)
7. Product appears in catalog
```

---

## F.3 Shopping Cart & Checkout

### Overview
Full e-commerce cart and checkout with multiple payment options.

### Backend Logic
```php
// Services
CartService::addItem()
CartService::updateQuantity()
CartService::removeItem()
CartService::getCart()
OrderService::placeOrder()
PaymentService::processPayment()

// Key endpoints
GET    /api/marketplace/cart
POST   /api/marketplace/cart/items
PUT    /api/marketplace/cart/items/{id}
DELETE /api/marketplace/cart/items/{id}
POST   /api/marketplace/checkout/place-order
```

### Frontend Components
- `Cart/Index.vue` - Cart page
- `CartDrawer.vue` - Slide-out cart
- `CartItem.vue` - Cart line item
- `Checkout/Index.vue` - Checkout flow
- `Checkout/Payment.vue` - Payment selection
- `PaymentMethods.vue` - Payment options
- `DeliveryOptions.vue` - Delivery selection

### Data Needed
```typescript
interface CheckoutData {
  deliveryAddress: Address;
  deliveryOptionId: number;
  paymentMethod: PaymentMethod;
  useWalletBalance: boolean;
  couponCode?: string;
  notes?: string;
}
```

### Workflow
```
1. User browses products
2. Click "Add to Cart" → Item added
3. Cart drawer shows → Continue shopping or checkout
4. Checkout: Enter delivery address
5. Select delivery option (courier/pickup/vendor)
6. Select payment method (MTN MoMo/Airtel/Wallet)
7. Review order summary
8. Place order → Payment initiated
9. Payment callback → Order confirmed
10. Confirmation page with order number
```

### Payment Flow (MTN MoMo)
```
1. User selects MTN MoMo
2. Enter phone number
3. Submit → API call to MTN
4. User receives USSD prompt
5. User enters PIN on phone
6. Callback received → Order status updated
7. Success/failure notification
```

---

## F.4 Order Management

### Overview
Order lifecycle management for buyers, vendors, and admins.

### Backend Logic
```php
// Services
OrderService::getOrders()
OrderService::getOrderDetails()
OrderService::updateStatus()
OrderService::cancelOrder()
OrderService::processRefund()

// Key endpoints (Buyer)
GET  /api/marketplace/orders
GET  /api/marketplace/orders/{orderNumber}
POST /api/marketplace/orders/{orderNumber}/cancel

// Key endpoints (Vendor)
GET  /api/marketplace/vendor/orders
PUT  /api/marketplace/vendor/orders/{id}/status
POST /api/marketplace/vendor/orders/{id}/ship
```

### Frontend Components
- `Orders/Index.vue` - Order history (buyer)
- `Orders/Show.vue` - Order detail (buyer)
- `Vendor/Orders/Index.vue` - Order list (vendor)
- `Vendor/Orders/Show.vue` - Order detail (vendor)
- `OrderCard.vue` - Order summary card
- `OrderTimeline.vue` - Status timeline
- `TrackingInfo.vue` - Delivery tracking

### Order Status Flow
```
pending → confirmed → processing → shipped → delivered
    ↓         ↓           ↓           ↓
cancelled  cancelled   cancelled   refunded
```

### Workflow (Vendor)
```
1. New order notification
2. Vendor views order details
3. Confirm order → Status: confirmed
4. Prepare items → Status: processing
5. Ship order → Enter tracking number
6. Status: shipped → Buyer notified
7. Delivery confirmed → Status: delivered
8. Commission calculated
```

---

## F.5 Vendor Dashboard

### Overview
Comprehensive dashboard for vendors to manage their business.

### Backend Logic
```php
// Services
VendorService::getDashboardData()
VendorService::getSalesAnalytics()
VendorService::getTopProducts()

// Key endpoints
GET /api/marketplace/vendor/dashboard
GET /api/marketplace/vendor/analytics/sales
GET /api/marketplace/vendor/analytics/products
```

### Frontend Components
- `Vendor/Dashboard.vue` - Main dashboard
- `SalesChart.vue` - Sales over time
- `OrdersTable.vue` - Recent orders
- `TopProducts.vue` - Best sellers
- `StatsCards.vue` - Key metrics

### Data Needed
```typescript
interface VendorDashboard {
  stats: {
    totalOrders: number;
    pendingOrders: number;
    totalRevenue: number;
    thisMonthRevenue: number;
    totalProducts: number;
    activeProducts: number;
    averageRating: number;
    totalReviews: number;
  };
  recentOrders: Order[];
  topProducts: Product[];
  salesChart: ChartData;
}
```

### Dashboard Sections
```
┌─────────────────────────────────────────────────────────────────┐
│  📊 VENDOR DASHBOARD                                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐               │
│  │ Orders  │ │ Revenue │ │Products │ │ Rating  │               │
│  │   45    │ │ K12,500 │ │   23    │ │  4.5⭐  │               │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘               │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📈 Sales This Month                                     │   │
│  │  [Chart showing daily sales]                            │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────┐  ┌─────────────────────────────────┐  │
│  │  📦 Recent Orders   │  │  🏆 Top Products                │  │
│  │  [Order list]       │  │  [Product list with sales]     │  │
│  └─────────────────────┘  └─────────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## F.6 Admin Dashboard

### Overview
Platform-wide management and analytics for administrators.

### Backend Logic
```php
// Services
AdminService::getDashboardData()
AdminService::getVendorStats()
AdminService::getOrderStats()
AdminService::getRevenueStats()

// Key endpoints
GET /api/marketplace/admin/dashboard
GET /api/marketplace/admin/reports/sales
GET /api/marketplace/admin/reports/vendors
```

### Frontend Components
- `Admin/Dashboard.vue` - Main dashboard
- `Admin/Vendors/Index.vue` - Vendor management
- `Admin/Products/Index.vue` - Product moderation
- `Admin/Orders/Index.vue` - Order oversight
- `Admin/Commissions/Index.vue` - Commission management

### Data Needed
```typescript
interface AdminDashboard {
  stats: {
    totalVendors: number;
    activeVendors: number;
    pendingKyc: number;
    totalProducts: number;
    totalOrders: number;
    totalRevenue: number;
    totalCommissions: number;
    thisMonthOrders: number;
    thisMonthRevenue: number;
  };
  recentOrders: Order[];
  pendingVendors: Vendor[];
  topVendors: Vendor[];
  salesChart: ChartData;
  categoryBreakdown: CategoryStats[];
}
```

---

# G. UI/UX Flows

## G.1 Buyer Product Browsing Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  🛒 MYGROWNET MARKETPLACE                    🔍 [Search...]  🛒 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Categories: [All] [Electronics] [Fashion] [Food] [Services]    │
│                                                                  │
│  ┌─────────────────┐  ┌─────────────────────────────────────┐  │
│  │  FILTERS        │  │  PRODUCTS                           │  │
│  │                 │  │                                      │  │
│  │  Province       │  │  ┌─────────┐ ┌─────────┐ ┌─────────┐│  │
│  │  [Lusaka    ▼]  │  │  │ 📷      │ │ 📷      │ │ 📷      ││  │
│  │                 │  │  │ Product │ │ Product │ │ Product ││  │
│  │  Price Range    │  │  │ K150    │ │ K250    │ │ K99     ││  │
│  │  K[___] - K[___]│  │  │ ⭐4.5   │ │ ⭐4.8   │ │ ⭐4.2   ││  │
│  │                 │  │  └─────────┘ └─────────┘ └─────────┘│  │
│  │  Rating         │  │                                      │  │
│  │  ○ 4+ stars     │  │  ┌─────────┐ ┌─────────┐ ┌─────────┐│  │
│  │  ○ 3+ stars     │  │  │ 📷      │ │ 📷      │ │ 📷      ││  │
│  │  ○ All          │  │  │ Product │ │ Product │ │ Product ││  │
│  │                 │  │  │ K350    │ │ K180    │ │ K420    ││  │
│  │  [Apply Filters]│  │  │ ⭐4.9   │ │ ⭐4.1   │ │ ⭐4.7   ││  │
│  └─────────────────┘  │  └─────────┘ └─────────┘ └─────────┘│  │
│                       │                                      │  │
│                       │  [Load More...]                      │  │
│                       └─────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## G.2 Product Detail Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  ← Back to Products                                    🛒 (3)   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────┐  ┌─────────────────────────────────┐  │
│  │                     │  │  Premium Wireless Headphones     │  │
│  │    [Main Image]     │  │                                  │  │
│  │                     │  │  ⭐⭐⭐⭐⭐ 4.8 (124 reviews)     │  │
│  │                     │  │                                  │  │
│  │  [📷][📷][📷][📷]   │  │  K450.00  ̶K̶5̶5̶0̶.̶0̶0̶  -18%        │  │
│  │                     │  │                                  │  │
│  └─────────────────────┘  │  Color: [Black ▼]               │  │
│                           │                                  │  │
│                           │  Quantity: [-] 1 [+]            │  │
│                           │                                  │  │
│                           │  ✓ In Stock (15 available)      │  │
│                           │                                  │  │
│                           │  [🛒 Add to Cart]  [❤️ Wishlist] │  │
│                           │                                  │  │
│                           │  ─────────────────────────────── │  │
│                           │                                  │  │
│                           │  📦 Delivery to Lusaka: K25     │  │
│                           │     Est. 2-3 days               │  │
│                           │                                  │  │
│                           │  🏪 Sold by: TechZone Zambia    │  │
│                           │     ✓ Verified Seller           │  │
│                           └─────────────────────────────────┘  │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📋 Description                                          │   │
│  │  High-quality wireless headphones with noise cancellation│   │
│  │  and 30-hour battery life...                            │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  ⭐ Reviews (124)                                        │   │
│  │  ┌─────────────────────────────────────────────────────┐│   │
│  │  │ John M. ⭐⭐⭐⭐⭐                                    ││   │
│  │  │ "Great sound quality, very comfortable!"            ││   │
│  │  └─────────────────────────────────────────────────────┘│   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## G.3 Checkout Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  CHECKOUT                                              Step 1/3 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ● Delivery Address  ○ Payment  ○ Review                        │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📍 Delivery Address                                     │   │
│  │                                                          │   │
│  │  Full Name: [_______________________________]           │   │
│  │  Phone:     [_______________________________]           │   │
│  │  Province:  [Lusaka                        ▼]           │   │
│  │  City:      [Lusaka                        ▼]           │   │
│  │  Area:      [_______________________________]           │   │
│  │  Street:    [_______________________________]           │   │
│  │  Landmark:  [_______________________________]           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  🚚 Delivery Option                                      │   │
│  │                                                          │   │
│  │  ○ Standard Delivery (2-3 days) - K25                   │   │
│  │  ○ Express Delivery (1 day) - K50                       │   │
│  │  ○ Pickup Station (Manda Hill) - K10                    │   │
│  │  ○ Vendor Self-Delivery - K20                           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│                                            [Continue to Payment →]│
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  CHECKOUT                                              Step 2/3 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ✓ Delivery Address  ● Payment  ○ Review                        │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  💳 Payment Method                                       │   │
│  │                                                          │   │
│  │  ○ 📱 MTN Mobile Money                                  │   │
│  │  ○ 📱 Airtel Money                                      │   │
│  │  ○ 💰 MyGrow Wallet (Balance: K150)                     │   │
│  │  ○ 💳 Debit/Credit Card                                 │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  ☑️ Use Wallet Balance (K150)                            │   │
│  │     Remaining: K325 to pay                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [← Back]                                  [Continue to Review →]│
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  CHECKOUT                                              Step 3/3 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ✓ Delivery Address  ✓ Payment  ● Review                        │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📦 Order Summary                                        │   │
│  │                                                          │   │
│  │  Premium Wireless Headphones (x1)           K450.00     │   │
│  │  USB-C Charging Cable (x2)                  K60.00      │   │
│  │  ─────────────────────────────────────────────────────  │   │
│  │  Subtotal                                   K510.00     │   │
│  │  Delivery (Standard)                        K25.00      │   │
│  │  Wallet Discount                           -K150.00     │   │
│  │  ─────────────────────────────────────────────────────  │   │
│  │  TOTAL                                      K385.00     │   │
│  │                                                          │   │
│  │  💰 You'll earn 38 loyalty points                       │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📍 Delivering to:                                       │   │
│  │  John Banda, +260 97X XXX XXX                           │   │
│  │  123 Cairo Road, Lusaka                                 │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [← Back]                                      [🛒 Place Order] │
└─────────────────────────────────────────────────────────────────┘
```

---

## G.4 Vendor Dashboard Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  🏪 VENDOR DASHBOARD                    TechZone Zambia  ✓      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  [Dashboard] [Products] [Orders] [Analytics] [Profile] [Settings]│
│                                                                  │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐               │
│  │ 📦      │ │ 💰      │ │ 🛍️      │ │ ⭐      │               │
│  │ Orders  │ │ Revenue │ │Products │ │ Rating  │               │
│  │   45    │ │K12,500  │ │   23    │ │  4.5    │               │
│  │ +5 new  │ │ +K2,100 │ │ 3 low   │ │124 rev  │               │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘               │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📈 Sales This Month                                     │   │
│  │                                                          │   │
│  │  K3,000 ┤                              ╭─╮               │   │
│  │  K2,000 ┤              ╭─╮    ╭─╮    ╭─╯ │               │   │
│  │  K1,000 ┤    ╭─╮    ╭─╯ ╰────╯ ╰────╯   │               │   │
│  │       0 ┼────╯ ╰────╯                    │               │   │
│  │         1   5   10   15   20   25   30                   │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────┐  ┌─────────────────────────────┐  │
│  │  📦 Recent Orders       │  │  🏆 Top Products            │  │
│  │                         │  │                              │  │
│  │  #ORD-001 - K450       │  │  1. Wireless Headphones     │  │
│  │  Pending • 2 mins ago  │  │     K450 • 45 sold          │  │
│  │                         │  │                              │  │
│  │  #ORD-002 - K120       │  │  2. USB-C Cable             │  │
│  │  Shipped • 1 hour ago  │  │     K30 • 38 sold           │  │
│  │                         │  │                              │  │
│  │  #ORD-003 - K890       │  │  3. Phone Case              │  │
│  │  Delivered • 3 hrs ago │  │     K75 • 25 sold           │  │
│  │                         │  │                              │  │
│  │  [View All Orders →]   │  │  [View All Products →]      │  │
│  └─────────────────────────┘  └─────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## G.5 Vendor Product Management Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  🏪 PRODUCTS                              [+ Add New Product]   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  [All (23)] [Active (20)] [Draft (2)] [Out of Stock (1)]        │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 📷 │ Product Name          │ Price  │ Stock │ Status   │   │
│  ├────┼───────────────────────┼────────┼───────┼──────────┤   │
│  │ 🎧 │ Wireless Headphones   │ K450   │ 15    │ ✓ Active │   │
│  │ 🔌 │ USB-C Cable           │ K30    │ 50    │ ✓ Active │   │
│  │ 📱 │ Phone Case            │ K75    │ 0     │ ⚠️ Out   │   │
│  │ 🔋 │ Power Bank            │ K250   │ 8     │ ✓ Active │   │
│  │ 🎤 │ Microphone            │ K180   │ 12    │ 📝 Draft │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [← Prev] Page 1 of 3 [Next →]                                  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  ADD NEW PRODUCT                                       [Cancel] │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📷 Product Images                                       │   │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │   │
│  │  │ [Drop]  │ │ [Drop]  │ │ [Drop]  │ │ [Drop]  │       │   │
│  │  │ Image 1 │ │ Image 2 │ │ Image 3 │ │ Image 4 │       │   │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘       │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Product Name: [_______________________________]                │
│  Category:     [Electronics                   ▼]                │
│  Description:  [_______________________________]                │
│               [_______________________________]                │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  💰 Pricing                                              │   │
│  │  Price:          K[_________]                           │   │
│  │  Compare Price:  K[_________] (optional)                │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  📦 Inventory                                            │   │
│  │  SKU:      [_________]                                  │   │
│  │  Quantity: [_________]                                  │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [Save as Draft]                              [Publish Product] │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

# H. Developer Notes

## H.1 Recommended Libraries

### Backend (PHP/Laravel)

| Library | Purpose | Version |
|---------|---------|---------|
| `spatie/laravel-permission` | Role/permission management | ^6.0 |
| `spatie/laravel-medialibrary` | Image management | ^11.0 |
| `spatie/laravel-sluggable` | Auto-generate slugs | ^3.0 |
| `maatwebsite/excel` | Excel exports | ^3.1 |
| `barryvdh/laravel-dompdf` | PDF generation | ^2.0 |
| `intervention/image` | Image processing | ^3.0 |
| `laravel/sanctum` | API authentication | ^4.0 |
| `laravel/scout` | Full-text search | ^10.0 |
| `meilisearch/meilisearch-php` | Search engine (Phase 4) | ^1.0 |

### Frontend (Vue/TypeScript)

| Library | Purpose | Version |
|---------|---------|---------|
| `@vueuse/core` | Vue composition utilities | ^10.0 |
| `chart.js` | Charts and graphs | ^4.0 |
| `vue-chartjs` | Vue Chart.js wrapper | ^5.0 |
| `@heroicons/vue` | Icons | ^2.0 |
| `lucide-vue-next` | Additional icons | ^0.300 |
| `sweetalert2` | Alerts and modals | ^11.0 |
| `vue-toastification` | Toast notifications | ^2.0 |
| `@tanstack/vue-query` | Data fetching | ^5.0 |
| `swiper` | Image carousel | ^11.0 |
| `vue-advanced-cropper` | Image cropping | ^2.0 |

---

## H.2 Component Naming Conventions

### Vue Components

```
Pattern: [Domain][Feature][Type].vue

Examples:
- MarketplaceLayout.vue           # Layout
- ProductCard.vue                 # Component
- ProductGallery.vue              # Component
- CartDrawer.vue                  # Component
- CheckoutPayment.vue             # Page section
- VendorDashboard.vue             # Page
- AdminVendorList.vue             # Page
```

### File Organization

```
resources/js/
├── Pages/Marketplace/            # Page components (routed)
│   ├── Buyer/                    # Buyer-facing pages
│   ├── Vendor/                   # Vendor pages
│   └── Admin/                    # Admin pages
├── Components/Marketplace/       # Reusable components
│   ├── Product/                  # Product-related
│   ├── Cart/                     # Cart-related
│   ├── Order/                    # Order-related
│   └── Common/                   # Shared components
├── Composables/Marketplace/      # Composition functions
│   └── use[Feature].ts
└── types/
    └── marketplace.ts            # TypeScript interfaces
```

---

## H.3 Payment Integration Guidelines

### MTN MoMo Integration

```php
// Payment flow
class MtnMomoGateway implements PaymentGatewayInterface
{
    public function initiatePayment(Order $order, string $phone): PaymentResponse
    {
        // 1. Generate reference
        $reference = $this->generateReference($order);
        
        // 2. Call MTN API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'X-Reference-Id' => $reference,
        ])->post($this->apiUrl . '/collection/v1_0/requesttopay', [
            'amount' => $order->total,
            'currency' => 'ZMW',
            'externalId' => $order->order_number,
            'payer' => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
            'payerMessage' => "Payment for order {$order->order_number}",
        ]);
        
        // 3. Return response
        return new PaymentResponse($reference, $response->status());
    }
    
    public function checkStatus(string $reference): PaymentStatus
    {
        // Poll for payment status
    }
}
```

### Webhook Handling

```php
// Handle payment callbacks
Route::post('/webhooks/mtn-momo', function (Request $request) {
    $reference = $request->input('referenceId');
    $status = $request->input('status');
    
    $order = Order::where('payment_reference', $reference)->first();
    
    if ($status === 'SUCCESSFUL') {
        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
        
        event(new PaymentReceived($order));
    }
    
    return response()->json(['status' => 'ok']);
});
```

---

## H.4 Commission Calculation

```php
class CommissionService
{
    public function calculateCommission(Order $order): Commission
    {
        // 1. Get commission rate (vendor override > category > default)
        $rate = $this->getCommissionRate($order);
        
        // 2. Calculate commission amount
        $amount = $order->subtotal * ($rate / 100);
        
        // 3. Create commission record
        return Commission::create([
            'order_id' => $order->id,
            'vendor_id' => $order->vendor_id,
            'order_total' => $order->subtotal,
            'commission_rate' => $rate,
            'commission_amount' => $amount,
            'status' => 'pending',
        ]);
    }
    
    private function getCommissionRate(Order $order): float
    {
        // Check vendor override
        if ($order->vendor->commission_rate) {
            return $order->vendor->commission_rate;
        }
        
        // Check category rate
        $categoryRate = $order->items->first()?->product->category->commission_rate;
        if ($categoryRate) {
            return $categoryRate;
        }
        
        // Default rate
        return config('marketplace.default_commission_rate', 10);
    }
}
```

---

## H.5 Search & Filtering

### Product Search Service

```php
class SearchService
{
    public function searchProducts(ProductFilters $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->where('is_active', true)
            ->where('is_approved', true)
            ->with(['images', 'vendor', 'category']);
        
        // Category filter
        if ($filters->categoryId) {
            $query->where('category_id', $filters->categoryId);
        }
        
        // Province filter (via vendor)
        if ($filters->province) {
            $query->whereHas('vendor', fn($q) => 
                $q->where('province', $filters->province)
            );
        }
        
        // Price range
        if ($filters->minPrice) {
            $query->where('price', '>=', $filters->minPrice);
        }
        if ($filters->maxPrice) {
            $query->where('price', '<=', $filters->maxPrice);
        }
        
        // Rating filter
        if ($filters->rating) {
            $query->where('rating', '>=', $filters->rating);
        }
        
        // Text search
        if ($filters->search) {
            $query->whereFullText(['name', 'description'], $filters->search);
        }
        
        // Sorting
        $query->when($filters->sortBy, function ($q, $sort) {
            return match($sort) {
                'newest' => $q->latest(),
                'price_low' => $q->orderBy('price', 'asc'),
                'price_high' => $q->orderBy('price', 'desc'),
                'popular' => $q->orderBy('sold_count', 'desc'),
                'rating' => $q->orderBy('rating', 'desc'),
                default => $q->latest(),
            };
        });
        
        return $query->paginate(24);
    }
}
```

---

## H.6 Cart Management

### Cart Composable (Frontend)

```typescript
// resources/js/Composables/Marketplace/useCart.ts
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface CartState {
  items: CartItem[];
  isLoading: boolean;
}

const state = ref<CartState>({
  items: [],
  isLoading: false,
});

export function useCart() {
  const itemCount = computed(() => 
    state.value.items.reduce((sum, item) => sum + item.quantity, 0)
  );
  
  const subtotal = computed(() =>
    state.value.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  );
  
  async function addToCart(productId: number, quantity: number = 1, variantId?: number) {
    state.value.isLoading = true;
    
    try {
      await router.post('/api/marketplace/cart/items', {
        product_id: productId,
        variant_id: variantId,
        quantity,
      }, {
        preserveState: true,
        onSuccess: () => fetchCart(),
      });
    } finally {
      state.value.isLoading = false;
    }
  }
  
  async function updateQuantity(itemId: number, quantity: number) {
    await router.put(`/api/marketplace/cart/items/${itemId}`, { quantity });
  }
  
  async function removeItem(itemId: number) {
    await router.delete(`/api/marketplace/cart/items/${itemId}`);
  }
  
  async function fetchCart() {
    const response = await fetch('/api/marketplace/cart');
    const data = await response.json();
    state.value.items = data.items;
  }
  
  return {
    items: computed(() => state.value.items),
    itemCount,
    subtotal,
    isLoading: computed(() => state.value.isLoading),
    addToCart,
    updateQuantity,
    removeItem,
    fetchCart,
  };
}
```

---

## H.7 Order Status Management

### Order State Machine

```php
class OrderService
{
    private array $allowedTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];
    
    public function updateStatus(Order $order, string $newStatus): Order
    {
        $currentStatus = $order->status;
        
        if (!in_array($newStatus, $this->allowedTransitions[$currentStatus])) {
            throw new InvalidStatusTransitionException(
                "Cannot transition from {$currentStatus} to {$newStatus}"
            );
        }
        
        $order->update([
            'status' => $newStatus,
            "{$newStatus}_at" => now(),
        ]);
        
        event(new OrderStatusChanged($order, $currentStatus, $newStatus));
        
        return $order;
    }
}
```

---

## H.8 Performance Optimization

### Caching Strategy

```php
// Cache frequently accessed data
class ProductService
{
    public function getFeaturedProducts(): Collection
    {
        return Cache::remember('featured_products', 3600, function () {
            return Product::where('is_featured', true)
                ->where('is_active', true)
                ->with(['images', 'vendor'])
                ->limit(12)
                ->get();
        });
    }
    
    public function getCategories(): Collection
    {
        return Cache::rememberForever('categories', function () {
            return Category::where('is_active', true)
                ->withCount('products')
                ->orderBy('order')
                ->get();
        });
    }
}

// Invalidate cache on updates
class ProductObserver
{
    public function saved(Product $product): void
    {
        if ($product->is_featured) {
            Cache::forget('featured_products');
        }
    }
}
```

### Database Optimization

```php
// Use eager loading
$products = Product::with([
    'images' => fn($q) => $q->orderBy('order'),
    'vendor:id,business_name,slug,is_verified',
    'category:id,name,slug',
])->paginate(24);

// Use chunking for large operations
Order::where('status', 'delivered')
    ->where('delivered_at', '<', now()->subDays(7))
    ->chunk(100, function ($orders) {
        foreach ($orders as $order) {
            // Process order
        }
    });
```

---

## H.9 Security Considerations

### Input Validation

```php
class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:1|max:999999',
            'category_id' => 'required|exists:marketplace_categories,id',
            'quantity' => 'required|integer|min:0|max:99999',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,webp|max:2048',
        ];
    }
}
```

### Authorization

```php
class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->vendor?->id === $product->vendor_id;
    }
    
    public function delete(User $user, Product $product): bool
    {
        return $user->vendor?->id === $product->vendor_id
            || $user->hasRole('admin');
    }
}
```

---

## H.10 MVP Scope Notes

### MVP Included Features
- ✅ Vendor registration & basic KYC
- ✅ Product upload (title, description, price, photos)
- ✅ Seller dashboard (orders, sales overview)
- ✅ Browse products by category & province
- ✅ Search & filter
- ✅ Cart & checkout
- ✅ Mobile money payment (MTN, Airtel)
- ✅ Basic loyalty wallet
- ✅ Admin dashboard (vendors, products, orders)
- ✅ Commission settings
- ✅ Vendor self-delivery

### MVP Excluded (Phase 3+)
- ❌ Full BizBoost marketing tools
- ❌ Multi-courier delivery integration
- ❌ Pickup stations
- ❌ Ratings & reviews
- ❌ Seller training portal
- ❌ Advanced analytics

### MVP Limits
- 3-5 initial categories
- 50-100 beta sellers
- Basic dashboards only
- No advanced analytics

---

## H.11 Testing Guidelines

### Unit Tests

```php
// tests/Unit/Marketplace/CommissionServiceTest.php
it('calculates commission correctly', function () {
    $vendor = Vendor::factory()->create(['commission_rate' => 12]);
    $order = Order::factory()->create([
        'vendor_id' => $vendor->id,
        'subtotal' => 1000,
    ]);
    
    $service = new CommissionService();
    $commission = $service->calculateCommission($order);
    
    expect($commission->commission_rate)->toBe(12.0);
    expect($commission->commission_amount)->toBe(120.0);
});
```

### Feature Tests

```php
// tests/Feature/Marketplace/CheckoutTest.php
it('places an order successfully', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);
    
    // Add to cart
    $this->actingAs($user)
        ->postJson('/api/marketplace/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
        ->assertStatus(200);
    
    // Place order
    $this->postJson('/api/marketplace/checkout/place-order', [
        'delivery_address' => [...],
        'delivery_option_id' => 1,
        'payment_method' => 'mtn_momo',
        'phone' => '0971234567',
    ])
    ->assertStatus(201)
    ->assertJsonPath('data.order.total', 200);
});
```

---

## Changelog

### December 11, 2025
- Initial development specification created
- Complete architecture documented
- All tasks defined and organized by phase
- Feature breakdowns completed
- UI/UX flows designed
- Developer notes added
