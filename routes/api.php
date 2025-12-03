<?php

use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BusinessTypeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Seller\VoucherController as SellerVoucherController;
use App\Http\Controllers\VoucherBrowseController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;



/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Route::get('/login', function () {
//     return response()->json([
//         'success' => false,
//         'message' => 'Unauthenticated.',
//     ], 401);
// })->name('login');

Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']); // Public - uses refresh token
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Protected routes (require access token)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('catalog')->group(function () {
    // Public product recommendations (leverages auth if token present)
    Route::get('/products/recommended', [ProductController::class, 'recommended']);

    // Public product list and search
    Route::get('/products', [ProductController::class, 'publicIndex']);
    Route::get('/products/search', [ProductController::class, 'publicSearch']);
    // Public product detail (no authentication required)
    Route::get('/products/{id}', [ProductController::class, 'show']);
});



// Public banners for the app (no authentication required)
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/business-types', [BusinessTypeController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}/variants', [CategoryController::class, 'variants']);
Route::get('/vouchers/available', [VoucherBrowseController::class, 'available']);
Route::get('/vouchers/claimable', [VoucherBrowseController::class, 'claimable']);

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
| All routes below require JWT authentication
*/

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // User profile routes (require authentication)
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::patch('/profile', [UserController::class, 'updateProfile']);
    Route::get('/users/{id}', [UserController::class, 'show']); // Public user profile

    // Shop routes (require authentication)
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{id}', [ShopController::class, 'show']);
    Route::post('/shops', [ShopController::class, 'store']);
    Route::put('/shops/{id}', [ShopController::class, 'update']);
    Route::patch('/shops/{id}', [ShopController::class, 'update']);

    // Product routes (require authentication)
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/categories/{category}/variants', [ProductController::class, 'categoryVariants']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::patch('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Cart routes (require authentication)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::patch('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/clear', [CartController::class, 'clear']); // Clear all cart items
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Checkout voucher validation
    Route::post('/checkout/apply-vouchers', [CheckoutController::class, 'applyVouchers']);

    // Wishlist routes (require authentication)
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']); // id can be wishlist item id or product_id
    Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check']); // Check if product is in wishlist

    // Review routes (require authentication)
    Route::get('/reviews', [ReviewController::class, 'index']); // Get reviews for a product (requires product_id query param)
    Route::get('/reviews/{id}', [ReviewController::class, 'show']); // Get specific review
    Route::post('/reviews', [ReviewController::class, 'store']); // Create review (buyers only)
    Route::put('/reviews/{id}', [ReviewController::class, 'update']); // Update review (owner only)
    Route::patch('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']); // Delete review (owner/admin only)
    Route::post('/reviews/{id}/reply', [ReviewController::class, 'reply']); // Shop owner reply to review
    Route::put('/reviews/{id}/reply', [ReviewController::class, 'updateReply']); // Update shop reply
    Route::patch('/reviews/{id}/reply', [ReviewController::class, 'updateReply']);
    Route::delete('/reviews/{id}/reply', [ReviewController::class, 'deleteReply']); // Delete shop reply
    Route::get('/reviews/shop/all', [ReviewController::class, 'shopReviews']); // Get all reviews for shop products (shop owner only)

    // Notification routes (require authentication)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('/notifications/test', [NotificationController::class, 'test']); // Test endpoint for sending notifications

    // Admin routes (require admin role)
    Route::prefix('admin')->group(function () {
        // User management
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/statistics', [UserManagementController::class, 'statistics']);
        Route::get('/users/{id}', [UserManagementController::class, 'show']);
        Route::put('/users/{id}/status', [UserManagementController::class, 'updateStatus']);
        Route::post('/users/{id}/ban', [UserManagementController::class, 'ban']);
        Route::post('/users/{id}/unban', [UserManagementController::class, 'unban']);
        Route::post('/users/{id}/activate-seller', [UserManagementController::class, 'activateSeller']);

        // Banner management
        Route::get('/banners', [AdminBannerController::class, 'index']);
        Route::post('/banners', [AdminBannerController::class, 'store']);
        Route::put('/banners/{id}', [AdminBannerController::class, 'update']);
        Route::patch('/banners/{id}', [AdminBannerController::class, 'update']);
        Route::delete('/banners/{id}', [AdminBannerController::class, 'destroy']);

        // Voucher management (admin)
        Route::get('/vouchers', [AdminVoucherController::class, 'index']);
        Route::post('/vouchers', [AdminVoucherController::class, 'store']);
        Route::put('/vouchers/{id}', [AdminVoucherController::class, 'update']);
        Route::patch('/vouchers/{id}', [AdminVoucherController::class, 'update']);
        Route::delete('/vouchers/{id}', [AdminVoucherController::class, 'destroy']);

        // Category management (admin)
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
        Route::patch('/categories/{category}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);
    });

    // Seller routes
    Route::prefix('seller')->group(function () {
        Route::get('/vouchers', [SellerVoucherController::class, 'index']);
        Route::post('/vouchers', [SellerVoucherController::class, 'store']);
        Route::put('/vouchers/{id}', [SellerVoucherController::class, 'update']);
        Route::patch('/vouchers/{id}', [SellerVoucherController::class, 'update']);
        Route::delete('/vouchers/{id}', [SellerVoucherController::class, 'destroy']);
    });
});
