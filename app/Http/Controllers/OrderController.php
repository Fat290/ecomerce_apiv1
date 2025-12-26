<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Voucher;
use App\Models\Product;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get orders for the authenticated user (buyer).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $query = Order::where('buyer_id', $user->id)
                ->with(['shop', 'shop.owner']);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by shop_id if provided
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse($orders, 'Orders retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve orders: ' . $e->getMessage());
        }
    }

    /**
     * Get products the authenticated buyer has purchased, with review status.
     */
    public function purchasedProducts(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Default to statuses that indicate the order is sufficiently progressed for reviews
            $defaultStatuses = ['confirmed', 'shipping', 'completed'];
            $allowedStatuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
            $statuses = $request->filled('statuses')
                ? array_values(array_intersect(
                    array_map('trim', explode(',', $request->input('statuses'))),
                    $allowedStatuses
                ))
                : $defaultStatuses;

            $orders = Order::where('buyer_id', $user->id)
                ->when(!empty($statuses), fn($q) => $q->whereIn('status', $statuses))
                ->get(['items']);

            $productIds = $orders->flatMap(function ($order) {
                if (!is_array($order->items)) {
                    return [];
                }
                return collect($order->items)
                    ->pluck('product_id')
                    ->filter();
            })->unique()->values();

            if ($productIds->isEmpty()) {
                return $this->paginatedResponse(
                    collect([])->paginate(1),
                    'Purchased products retrieved successfully'
                );
            }

            $perPage = min(max(1, (int)$request->input('per_page', 20)), 100);
            $reviewStatus = $request->input('review_status'); // reviewed | unreviewed | all

            $productsQuery = Product::whereIn('id', $productIds)
                ->whereIn('status', ['active', 'out_of_stock'])
                ->with(['shop', 'category'])
                ->withCount(['reviews as my_review_count' => function ($q) use ($user) {
                    $q->where('buyer_id', $user->id);
                }]);

            if ($reviewStatus === 'reviewed') {
                $productsQuery->whereHas('reviews', function ($q) use ($user) {
                    $q->where('buyer_id', $user->id);
                });
            } elseif ($reviewStatus === 'unreviewed') {
                $productsQuery->whereDoesntHave('reviews', function ($q) use ($user) {
                    $q->where('buyer_id', $user->id);
                });
            }

            $products = $productsQuery->paginate($perPage);

            // Append a simple boolean flag for frontend consumption
            $products->getCollection()->transform(function ($product) {
                $product->reviewed_by_me = ($product->my_review_count ?? 0) > 0;
                return $product;
            });

            return $this->paginatedResponse($products, 'Purchased products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve purchased products: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific order by ID.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $order = Order::with(['shop', 'shop.owner', 'buyer'])
                ->find($id);

            if (!$order) {
                return $this->notFoundResponse('Order not found');
            }

            // Check if user is the buyer or the shop owner
            $isBuyer = $order->buyer_id === $user->id;
            $isShopOwner = $order->shop && $order->shop->owner_id === $user->id;

            if (!$isBuyer && !$isShopOwner && $user->role !== 'admin') {
                return $this->unauthorizedResponse('You do not have permission to view this order');
            }

            return $this->successResponse($order, 'Order retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve order: ' . $e->getMessage());
        }
    }

    /**
     * Update order status (seller only).
     */
    public function updateStatus(string $id, UpdateOrderRequest $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $order = Order::with(['shop', 'buyer'])->find($id);

            if (!$order) {
                return $this->notFoundResponse('Order not found');
            }

            // Check if user is the shop owner
            if ($order->shop->owner_id !== $user->id && $user->role !== 'admin') {
                return $this->unauthorizedResponse('You do not have permission to update this order');
            }

            $oldStatus = $order->status;
            $newStatus = $request->input('status');

            if (!$newStatus) {
                return $this->errorResponse('Status is required', 400);
            }

            // Validate status transition
            $validTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['shipping', 'cancelled'],
                'shipping' => ['completed', 'cancelled'],
                'completed' => [],
                'cancelled' => [],
            ];

            if (!isset($validTransitions[$oldStatus]) || !in_array($newStatus, $validTransitions[$oldStatus])) {
                return $this->errorResponse("Invalid status transition from {$oldStatus} to {$newStatus}", 400);
            }

            $order->status = $newStatus;
            $order->save();
            $order->refresh();
            $order->load(['shop', 'buyer']);

            // Send notification to buyer about status change
            $statusMessages = [
                'confirmed' => "Your order #{$order->id} has been confirmed by {$order->shop->name}",
                'shipping' => "Your order #{$order->id} from {$order->shop->name} is now shipping",
                'completed' => "Your order #{$order->id} from {$order->shop->name} has been completed",
                'cancelled' => "Your order #{$order->id} from {$order->shop->name} has been cancelled",
            ];

            if (isset($statusMessages[$newStatus])) {
                $this->notificationService->sendOrderUpdate(
                    $order->buyer,
                    $order->id,
                    $newStatus,
                    $statusMessages[$newStatus]
                );
            }

            return $this->successResponse($order, 'Order status updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Get orders for a shop (seller only).
     */
    public function shopOrders(Request $request, string $shopId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $shop = Shop::find($shopId);

            if (!$shop) {
                return $this->notFoundResponse('Shop not found');
            }

            // Check if user is the shop owner or admin
            if ($shop->owner_id !== $user->id && $user->role !== 'admin') {
                return $this->unauthorizedResponse('You do not have permission to view orders for this shop');
            }

            $query = Order::where('shop_id', $shopId)
                ->with(['buyer']);

            // Filter by status if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse($orders, 'Shop orders retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve shop orders: ' . $e->getMessage());
        }
    }
}
