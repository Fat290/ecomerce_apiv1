<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'address' => 'array',
        ];
    }

    /**
     * Get the shops owned by the user.
     */
    public function shops()
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    /**
     * Get the chats where the user is a buyer.
     */
    public function buyerChats()
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    /**
     * Get the chats where the user is a seller.
     */
    public function sellerChats()
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    /**
     * Get the custom notifications for the user.
     * Note: This is separate from Laravel's built-in notification system.
     */
    public function userNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the orders placed by the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'buyer_id');
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the refresh tokens for the user.
     */
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    /**
     * Get the cart items for the user.
     */
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the wishlist items for the user.
     */
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }
}
