# Backend EC API

An e-commerce backend built on Laravel 12 that powers user accounts, shops, products, carts, wishlists, reviews, notifications, and admin tooling. This README summarizes the system's capabilities, documents every API route, and explains how to interact with the service.

---

## Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Environment Configuration](#environment-configuration)
- [Running the Application](#running-the-application)
- [Authentication Workflow](#authentication-workflow)
- [API Reference](#api-reference)
  - [Response Format](#response-format)
  - [Auth Routes](#auth-routes)
  - [User Profile Routes](#user-profile-routes)
  - [Shop Routes](#shop-routes)
  - [Product Routes](#product-routes)
  - [Cart Routes](#cart-routes)
  - [Wishlist Routes](#wishlist-routes)
  - [Review Routes](#review-routes)
  - [Notification Routes](#notification-routes)
  - [Admin Routes](#admin-routes)
- [Error Handling](#error-handling)
- [Testing](#testing)

---

## Overview
- ## Database Schema Snapshot

  The tables below list the columns and data types defined in the Laravel migrations. Use this as a quick reference when writing queries, seeders, or API payloads.

  ### `users`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `role` | enum(`admin`,`seller`,`buyer`), default `user` | controls access level |
  | `name` | string |
  | `email` | string, unique |
  | `email_verified_at` | timestamp, nullable |
  | `password` | string |
  | `phone` | string, nullable, unique |
  | `avatar` | string, nullable |
  | `status` | enum(`active`,`banned`,`pending`), default `active` |
  | `address` | json, nullable |
  | `remember_token` | string, nullable |
  | Timestamps | `created_at`, `updated_at` |

  ### `business_types`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `name` | string, unique |
  | `slug` | string, unique |
  | `description` | text, nullable |
  | `is_active` | boolean, default true |
  | Timestamps | `created_at`, `updated_at` |

  ### `shops`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `owner_id` | FK → `users.id`, cascade delete |
  | `name` | string |
  | `logo` | string, nullable |
  | `banner` | string, nullable |
  | `description` | text, nullable |
  | `business_type_id` | FK → `business_types.id`, nullable, null on delete |
  | `join_date` | date, nullable |
  | `address` | string, nullable |
  | `rating` | decimal(3,2), default 0 |
  | `status` | enum(`active`,`banned`,`pending`), default `pending` |
  | Timestamps | `created_at`, `updated_at` |

  ### `categories`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `name` | string |
  | `image` | string, nullable |
  | `parent_id` | self FK, nullable, cascade delete |
  | Timestamps | `created_at`, `updated_at` |

  ### `variant`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `category_id` | FK → `categories.id`, cascade delete |
  | `name` | string |
  | `options` | json (array of option strings), nullable |
  | `is_required` | boolean, default false |
  | `position` | unsigned integer, default 0 |
  | Unique | (`category_id`, `name`) |
  | Timestamps | `created_at`, `updated_at` |

  ### `brands`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `name` | string |
  | Timestamps | `created_at`, `updated_at` |

  ### `products`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `shop_id` | FK → `shops.id`, cascade delete |
  | `category_id` | FK → `categories.id`, cascade delete |
  | `brand_id` | FK → `brands.id`, nullable, set null |
  | `name` | string |
  | `description` | text, nullable |
  | `images` | json, nullable |
  | `price` | decimal(10,2) |
  | `stock` | integer, default 0 |
  | `status` | enum(`draft`,`active`,`out_of_stock`,`hidden`,`archived`), default `draft` |
  | `rating` | decimal(3,2), nullable, default 0 |
  | `sold_count` | integer, default 0 |
  | Timestamps | `created_at`, `updated_at` |

  ### `vouchers`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `code` | string, unique |
  | `discount_type` | enum(`percent`,`amount`) |
  | `voucher_type` | enum(`shipping`,`product`), default `product` |
  | `creator_type` | enum(`admin`,`seller`), default `seller` |
  | `discount_value` | decimal(10,2) |
  | `min_order_value` | decimal(10,2), default 0 |
  | `shop_id` | FK → `shops.id`, nullable, null on delete |
  | `start_date` | datetime |
  | `end_date` | datetime |
  | `status` | enum(`active`,`expired`,`disabled`), default `active` |
  | Timestamps | `created_at`, `updated_at` |

  ### `banners`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `title` | string |
  | `subtitle` | string, nullable |
  | `image_url` | string |
  | `is_active` | boolean |
  | Timestamps | `created_at`, `updated_at` |

  ### `carts`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `user_id` | FK → `users.id`, cascade delete |
  | `product_id` | FK → `products.id`, cascade delete |
  | `quantity` | integer, default 1 |
  | Timestamps | `created_at`, `updated_at` |
  | Unique | (`user_id`, `product_id`) ensures one entry per product |

  ### `wishlists`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `user_id` | FK → `users.id`, cascade delete |
  | `product_id` | FK → `products.id`, cascade delete |
  | Timestamps | `created_at`, `updated_at` |
  | Unique | (`user_id`, `product_id`) |

  ### `orders`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `buyer_id` | FK → `users.id`, cascade delete |
  | `shop_id` | FK → `shops.id`, cascade delete |
  | `items` | json (array of `{product_id, qty, price}`) |
  | `total_amount` | decimal(10,2) |
  | `shipping_fee` | decimal(10,2), default 0 |
  | `payment_method` | string |
  | `status` | enum(`pending`,`confirmed`,`shipping`,`completed`,`cancelled`), default `pending` |
  | `shipping_address` | json |
  | Timestamps | `created_at`, `updated_at` |

  ### `transactions`
  | Column | Type / Constraints | Notes |
  |--------|--------------------|-------|
  | `id` | big integer, PK |
  | `user_id` | FK → `users.id`, cascade delete |
  | `order_id` | FK → `orders.id`, nullable, set null |
  | `type` | enum(`purchase`,`withdraw`,`refund`) |
  | `amount` | decimal(10,2) |
  | `method` | string |
  | `status` | enum(`success`,`pending`,`failed`), default `pending` |
  | Timestamps | `created_at`, `updated_at` |

  > _System tables such as `password_reset_tokens`, `sessions`, `jobs`, and `failed_jobs` follow Laravel's defaults. Refer to `database/migrations/` for the full list if you need additional details._


- **Framework**: Laravel 12.x, PHP 8.2+
- **Auth**: JWT (access + refresh tokens)
- **Storage**: Eloquent ORM + migrations
- **Queues**: Laravel queues for async notification creation
- **Media**: Supports image uploads for products and avatars (Cloudinary)
- **Search & Filters**: Server-side filtering on products, reviews, carts, etc.

---

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.2 with extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `curl`, `gd`, `zip`
- **Composer** (PHP dependency manager)
- **Database** (MySQL, PostgreSQL, or SQLite)
- **Node.js & NPM** (optional, for frontend assets)

---

## Installation & Setup

### Step 1: Clone and Install Dependencies

```bash
# Clone the repository (if applicable)
git clone <repository-url>
cd backend_ec

# Install PHP dependencies
composer install
```

### Step 2: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### Step 3: Configure Environment Variables

Edit the `.env` file with your configuration (see [Environment Configuration](#environment-configuration) below).

### Step 4: Database Setup

```bash
# Run migrations and seeders
php artisan migrate --seed

# Or run migrations only
php artisan migrate
```

### Step 5: Start Queue Worker (Optional but Recommended)

For async notification processing, start a queue worker:

```bash
# Using database driver (default)
php artisan queue:work

# Or use supervisor/systemd for production
```

### Step 6: Serve the Application

```bash
# Development server
php artisan serve

# Or specify port
php artisan serve --port=8000
```

The API will be available at: `http://127.0.0.1:8000`

---

## Environment Configuration

Edit your `.env` file with the following required variables:

### Database Configuration

```env
DB_CONNECTION=mysql  # or pgsql, sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### JWT Configuration

```env
JWT_SECRET=your_jwt_secret_key  # Generated by: php artisan jwt:secret
```

### Application Configuration

```env
APP_NAME="Backend EC"
APP_ENV=local
APP_KEY=base64:...  # Generated by: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Queue Configuration

```env
QUEUE_CONNECTION=database  # or redis, sqs for production
```

### Cloudinary Configuration (for image uploads)

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### Mail Configuration (optional)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Running the Application

### Development Mode

```bash
# Start Laravel development server
php artisan serve

# Start queue worker (in separate terminal)
php artisan queue:work
```

### Production Mode

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Optimize the application:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Use a production web server (Nginx/Apache) with PHP-FPM
4. Set up queue workers using supervisor or systemd

---

## Authentication Workflow

| Step | Endpoint | Description |
|------|----------|-------------|
| 1. Register | `POST /api/auth/register` | Create new user account |
| 2. Login | `POST /api/auth/login` | Get access + refresh tokens |
| 3. Use Access Token | `Authorization: Bearer <access_token>` | Include in header for protected routes |
| 4. Refresh Token | `POST /api/auth/refresh` | Get new access token when expired |
| 5. Logout | `POST /api/auth/logout` | Revoke current token |

**Token Expiration:**
- **Access Token**: 15 minutes
- **Refresh Token**: 30 days

---

## API Reference

### Response Format

All API responses follow a consistent format:

#### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

#### Paginated Response

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

#### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error detail"]
  }
}
```

---

### Auth Routes

#### POST `/api/auth/register`

Create a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "role": "buyer"  // Optional: buyer, seller, admin
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "role": "buyer",
      "status": "active",
      "avatar": null,
      "address": null,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "abc123def456...",
    "token_type": "bearer",
    "expires_in": 900
  }
}
```

#### POST `/api/auth/login`

Authenticate user and get tokens.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "abc123def456...",
    "token_type": "bearer",
    "expires_in": 900
  }
}
```

#### POST `/api/auth/refresh`

Get a new access token using refresh token.

**Request Body:**
```json
{
  "refresh_token": "abc123def456..."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 900
  }
}
```

#### POST `/api/auth/logout`

Revoke current access token.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### POST `/api/auth/logout-all`

Revoke all tokens for the user.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out from all devices successfully"
}
```

#### GET `/api/auth/me`

Get authenticated user information.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "buyer",
    "status": "active",
    "avatar": "https://example.com/avatar.jpg",
    "address": {
      "street": "123 Main St",
      "city": "New York",
      "state": "NY",
      "zip": "10001"
    },
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### User Profile Routes

#### GET `/api/profile`

Get authenticated user's profile with relationships.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "buyer",
    "status": "active",
    "avatar": "https://example.com/avatar.jpg",
    "address": { ... },
    "shops": [ ... ],
    "cart_items": [ ... ],
    "wishlist_items": [ ... ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/profile`

Update authenticated user's profile.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body (multipart/form-data or JSON):**
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "phone": "+1234567890",
  "password": "newpassword123",
  "password_confirmation": "newpassword123",
  "avatar": "https://example.com/new-avatar.jpg",  // or upload file
  "address": {
    "street": "456 Oak Ave",
    "city": "Los Angeles",
    "state": "CA",
    "zip": "90001"
  }
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": { ... }  // Updated user object
}
```

#### GET `/api/users/{id}`

Get public user profile.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "User profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "avatar": "https://example.com/avatar.jpg",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "shops": [
      {
        "id": 1,
        "name": "John's Shop",
        "logo": "https://example.com/logo.jpg",
        "rating": 4.5
      }
    ]
  }
}
```

---

### Shop Routes

#### GET `/api/shops`

List shops. Sellers see only their shop; buyers see all active shops.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200) - For Buyers:**
```json
{
  "success": true,
  "message": "Shops retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Tech Store",
      "logo": "https://example.com/logo.jpg",
      "description": "Best tech products",
      "address": "123 Tech St",
      "rating": 4.5,
      "status": "active",
      "owner": {
        "id": 2,
        "name": "Shop Owner",
        "email": "owner@example.com"
      },
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "pagination": { ... }
}
```

**Response (200) - For Sellers:**
```json
{
  "success": true,
  "message": "Shop retrieved successfully",
  "data": {
    "id": 1,
    "name": "My Shop",
    "logo": "https://example.com/logo.jpg",
    "description": "My shop description",
    "address": "123 Main St",
    "rating": 4.5,
    "status": "active",
    "owner": { ... },
    "products": [ ... ],
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### GET `/api/shops/{id}`

Get specific shop details.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Shop retrieved successfully",
  "data": {
    "id": 1,
    "name": "Tech Store",
    "logo": "https://example.com/logo.jpg",
    "description": "Best tech products",
    "address": "123 Tech St",
    "rating": 4.5,
    "status": "active",
    "owner": { ... },
    "products": [ ... ],
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### POST `/api/shops`

Create a new shop.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body (multipart/form-data or JSON):**
```json
{
  "name": "My New Shop",
  "description": "Shop description",
  "address": "123 Shop St",
  "logo": "https://example.com/logo.jpg",  // or upload file
  "rating": 0,
  "status": "pending"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Shop created successfully and is pending approval.",
  "data": {
    "id": 1,
    "name": "My New Shop",
    "logo": "https://example.com/logo.jpg",
    "description": "Shop description",
    "address": "123 Shop St",
    "rating": 0,
    "status": "pending",
    "owner_id": 1,
    "owner": { ... },
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/shops/{id}`

Update shop information.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "name": "Updated Shop Name",
  "description": "Updated description",
  "address": "456 New St",
  "logo": "https://example.com/new-logo.jpg"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Shop updated successfully",
  "data": { ... }  // Updated shop object
}
```

---

### Product Routes

#### GET `/api/products`

List products with search, filters, and pagination.

**Headers:** `Authorization: Bearer <access_token>`

**Query Parameters:**
- `search` - Search by name or description
- `category_id` - Filter by category
- `brand_id` - Filter by brand
- `status` - Filter by status (sellers only)
- `min_price` - Minimum price
- `max_price` - Maximum price
- `min_rating` - Minimum rating
- `in_stock` - Boolean (true/false)
- `sort_by` - Sort by: `price`, `rating`, `newest`, `oldest`
- `sort_order` - `asc` or `desc`
- `per_page` - Items per page (default: 15)

**Example:** `GET /api/products?search=laptop&min_price=500&max_price=2000&sort_by=price&sort_order=asc`

**Response (200):**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Laptop Pro",
      "description": "High-performance laptop",
      "price": 1299.99,
      "stock": 50,
      "status": "active",
      "rating": 4.5,
      "images": [
        "https://example.com/image1.jpg",
        "https://example.com/image2.jpg"
      ],
      "category": {
        "id": 1,
        "name": "Electronics"
      },
      "brand": {
        "id": 1,
        "name": "TechBrand"
      },
      "shop": {
        "id": 1,
        "name": "Tech Store"
      },
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

#### GET `/api/products/{id}`

Get product details (public endpoint).

**Response (200):**
```json
{
  "success": true,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "name": "Laptop Pro",
    "description": "High-performance laptop",
    "price": 1299.99,
    "stock": 50,
    "status": "active",
    "rating": 4.5,
    "images": [ ... ],
    "category": { ... },
    "brand": { ... },
    "shop": { ... },
    "reviews": [ ... ],
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### POST `/api/products`

Create a new product.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body (multipart/form-data):**
```
name: Laptop Pro
description: High-performance laptop
price: 1299.99
stock: 50
category_id: 1
brand_id: 1
status: active
images[]: [file1.jpg]
images[]: [file2.jpg]
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "Laptop Pro",
    "description": "High-performance laptop",
    "price": 1299.99,
    "stock": 50,
    "status": "active",
    "images": [ ... ],
    "category": { ... },
    "brand": { ... },
    "shop": { ... },
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/products/{id}`

Update product.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "name": "Updated Laptop Pro",
  "price": 1199.99,
  "stock": 45,
  "images": [ ... ]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": { ... }  // Updated product object
}
```

#### DELETE `/api/products/{id}`

Delete product.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

---

### Cart Routes

#### GET `/api/cart`

Get all cart items for authenticated user.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Cart retrieved successfully",
  "data": {
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "product_id": 1,
        "quantity": 2,
        "product": {
          "id": 1,
          "name": "Laptop Pro",
          "price": 1299.99,
          "images": [ ... ],
          "category": { ... },
          "brand": { ... },
          "shop": { ... }
        },
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "total": 2599.98,
    "item_count": 1
  }
}
```

#### POST `/api/cart`

Add product to cart.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product added to cart successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "product_id": 1,
    "quantity": 2,
    "product": { ... },
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/cart/{id}`

Update cart item quantity.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "quantity": 3
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cart item updated successfully",
  "data": { ... }  // Updated cart item
}
```

#### DELETE `/api/cart/{id}`

Remove item from cart.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Product removed from cart successfully"
}
```

#### DELETE `/api/cart/clear`

Clear all items from cart.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Cart cleared successfully"
}
```

---

### Wishlist Routes

#### GET `/api/wishlist`

Get all wishlist items.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Wishlist retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "product_id": 1,
      "product": {
        "id": 1,
        "name": "Laptop Pro",
        "price": 1299.99,
        "images": [ ... ],
        "category": { ... },
        "brand": { ... },
        "shop": { ... }
      },
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

#### POST `/api/wishlist`

Add product to wishlist.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "product_id": 1
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product added to wishlist successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "product_id": 1,
    "product": { ... },
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### DELETE `/api/wishlist/{id}`

Remove item from wishlist (by wishlist ID or product ID).

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Product removed from wishlist successfully"
}
```

#### GET `/api/wishlist/check/{productId}`

Check if product is in wishlist.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Wishlist check completed",
  "data": {
    "is_in_wishlist": true,
    "wishlist_id": 1
  }
}
```

---

### Review Routes

#### GET `/api/reviews`

Get reviews for a product.

**Headers:** `Authorization: Bearer <access_token>`

**Query Parameters:**
- `product_id` (required) - Product ID to get reviews for

**Response (200):**
```json
{
  "success": true,
  "message": "Reviews retrieved successfully",
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "buyer_id": 1,
      "rating": 5,
      "comment": "Great product!",
      "buyer": {
        "id": 1,
        "name": "John Doe",
        "avatar": "https://example.com/avatar.jpg"
      },
      "product": {
        "id": 1,
        "name": "Laptop Pro"
      },
      "shop_reply": null,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "pagination": { ... }
}
```

#### GET `/api/reviews/{id}`

Get specific review.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Review retrieved successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "buyer_id": 1,
    "rating": 5,
    "comment": "Great product!",
    "buyer": { ... },
    "product": { ... },
    "shop_reply": "Thank you for your feedback!",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### POST `/api/reviews`

Create a review (buyers only).

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "product_id": 1,
  "rating": 5,
  "comment": "Great product! Highly recommended."
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Review created successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "buyer_id": 1,
    "rating": 5,
    "comment": "Great product! Highly recommended.",
    "buyer": { ... },
    "product": { ... },
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/reviews/{id}`

Update review.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "rating": 4,
  "comment": "Updated review comment"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Review updated successfully",
  "data": { ... }  // Updated review
}
```

#### DELETE `/api/reviews/{id}`

Delete review.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Review deleted successfully"
}
```

#### POST `/api/reviews/{id}/reply`

Shop owner reply to review.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "reply": "Thank you for your feedback! We appreciate your business."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Reply added successfully",
  "data": {
    "id": 1,
    "shop_reply": "Thank you for your feedback!",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/reviews/{id}/reply`

Update shop reply.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "reply": "Updated reply message"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Reply updated successfully",
  "data": { ... }
}
```

#### DELETE `/api/reviews/{id}/reply`

Delete shop reply.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Reply deleted successfully"
}
```

#### GET `/api/reviews/shop/all`

Get all reviews for shop's products (shop owner only).

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Shop reviews retrieved successfully",
  "data": [ ... ],
  "pagination": { ... }
}
```

---

### Notification Routes

#### GET `/api/notifications`

Get all notifications for authenticated user.

**Headers:** `Authorization: Bearer <access_token>`

**Query Parameters:**
- `type` - Filter by type: `order_update`, `order_placed`, `chat_message`, `promotion`, `product_review`, `shop_review`, `system`
- `is_read` - Filter by read status (true/false)
- `sort_order` - `asc` or `desc` (default: desc)
- `per_page` - Items per page (default: 15)

**Response (200):**
```json
{
  "success": true,
  "message": "Notifications retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "type": "order_update",
      "title": "Order Update",
      "message": "Your order #123 has been shipped",
      "data": {
        "order_id": 123,
        "status": "shipped"
      },
      "action_url": "/orders/123",
      "is_read": false,
      "read_at": null,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "pagination": { ... },
  "unread_count": 5
}
```

#### GET `/api/notifications/unread-count`

Get unread notification count.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Unread count retrieved successfully",
  "data": {
    "unread_count": 5
  }
}
```

#### GET `/api/notifications/{id}`

Get specific notification.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Notification retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "type": "order_update",
    "title": "Order Update",
    "message": "Your order #123 has been shipped",
    "data": { ... },
    "action_url": "/orders/123",
    "is_read": false,
    "read_at": null,
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/notifications/{id}/read`

Mark notification as read.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "id": 1,
    "is_read": true,
    "read_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

#### PUT/PATCH `/api/notifications/read-all`

Mark all notifications as read.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Marked 5 notifications as read",
  "data": {
    "marked_count": 5
  }
}
```

#### DELETE `/api/notifications/{id}`

Delete notification.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

#### POST `/api/notifications/test`

Send test notification (for testing purposes).

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "user_id": 1,
  "type": "system",
  "title": "Test Notification",
  "message": "This is a test notification",
  "data": {
    "test": true
  },
  "action_url": "/test"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Test notification sent successfully",
  "data": { ... }
}
```

---

### Admin Routes

All admin routes require JWT authentication and admin role.

#### GET `/api/admin/users`

List all users with pagination.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [ ... ],
  "pagination": { ... }
}
```

#### GET `/api/admin/users/statistics`

Get user statistics.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Statistics retrieved successfully",
  "data": {
    "total_users": 100,
    "active_users": 85,
    "banned_users": 5,
    "sellers": 20,
    "buyers": 80
  }
}
```

#### GET `/api/admin/users/{id}`

Get specific user details.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": { ... }
}
```

#### PUT `/api/admin/users/{id}/status`

Update user status.

**Headers:** `Authorization: Bearer <access_token>`

**Request Body:**
```json
{
  "status": "active"  // or "inactive", "banned"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "User status updated successfully",
  "data": { ... }
}
```

#### POST `/api/admin/users/{id}/ban`

Ban a user.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "User banned successfully",
  "data": { ... }
}
```

#### POST `/api/admin/users/{id}/unban`

Unban a user.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "User unbanned successfully",
  "data": { ... }
}
```

#### POST `/api/admin/users/{id}/activate-seller`

Activate seller status for a user.

**Headers:** `Authorization: Bearer <access_token>`

**Response (200):**
```json
{
  "success": true,
  "message": "Seller status activated successfully",
  "data": { ... }
}
```

---

## Error Handling

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

### Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": [
      "The field name is required.",
      "The field name must be a valid email."
    ]
  }
}
```

### Common Error Messages

- **401 Unauthorized**: `"Token has expired"`, `"Token is invalid"`, `"User not authenticated"`
- **403 Forbidden**: `"You do not have permission to perform this action"`, `"Your account has been banned"`
- **404 Not Found**: `"Resource not found"`, `"Product not found"`
- **422 Validation Error**: Field-specific validation errors

---

## Testing

### Using Postman

1. Import the API collection (if available)
2. Set base URL: `http://127.0.0.1:8000`
3. Register/Login to get access token
4. Add token to Authorization header: `Bearer <token>`
5. Test endpoints

### Using cURL

```bash
# Register
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"password123","password_confirmation":"password123","phone":"+1234567890"}'

# Login
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Get Profile (with token)
curl -X GET http://127.0.0.1:8000/api/profile \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Queue Testing

To test notification queues:

```bash
# Start queue worker
php artisan queue:work

# Send test notification via API
POST /api/notifications/test
```

---

## Support & Further Development

- Extend by adding more modules (orders, payments, analytics)
- Hook up frontends via REST calls
- Replace queue driver with Redis/SQS for production
- Add real-time features if needed (Laravel Echo/Pusher)

For questions or contributions, open an issue or submit a PR.

---

**License:** MIT
