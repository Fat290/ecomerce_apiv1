<?php

namespace App\Providers;

use App\Models\Chat;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Policies\ChatPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\ShopPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use App\Policies\VoucherPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Shop::class => ShopPolicy::class,
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Review::class => ReviewPolicy::class,
        Voucher::class => VoucherPolicy::class,
        Chat::class => ChatPolicy::class,
        Notification::class => NotificationPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
