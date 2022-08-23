<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;
use App\Notifications\EmailVerificationNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'referred_by',
        'provider_id',
        'user_type',
        'active',
        'name',
        'email',
        'email_verified_at',
        'verification_code',
        'new_email_verificiation_code',
        'password',
        'customer_type',
        'commercial_registry',
        'tax_number_certificate',
        'long',
        'lat',
        'owner_name',
        'tax_number',
        'commercial_name',
        'commercial_registration_no',
        'remember_token',
        'device_token',
        'avatar',
        'avatar_original	',
        'address',
        'country',
        'country_id',
        'state',
        'state_id',
        'city',
        'city_id',
        'zone',
        'zone_id',
        'postal_code',
        'phone',
        'balance',
        'banned',
        'referral_code',
        'customer_package_id',
        'remaining_uploads',
        'status',
        'created_at',
        'updated_at',
        'delivery_type',
        'national_id',
        'national_id_attachment',
        'national_id_expired',
        'license_id',
        'license_id_attachment',
        'license_id_expired',
        'license_car',
        'license_car_attachment',
        'license_car_expired',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function commercial_photos()
    {
        return $this->hasMany(CommercialPhotos::class, 'user_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function affiliate_user()
    {
        return $this->hasOne(AffiliateUser::class);
    }

    public function affiliate_withdraw_request()
    {
        return $this->hasMany(AffiliateWithdrawRequest::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class)->orderBy('created_at', 'desc');
    }

    public function club_point()
    {
        return $this->hasOne(ClubPoint::class);
    }

    public function customer_package()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    }

    public function customer_products()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function seller_package_payments()
    {
        return $this->hasMany(SellerPackagePayment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function state_data()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function product_bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }


    public function packages()
    {
        return $this->hasMany(Package::class, 'user_id', 'id');
    }

    public function funds()
    {
        return $this->hasMany(UserFunds::class);
    }

    public function user_packages()
    {
        return $this->hasMany(UserPackage::class, 'user_id', 'id');
    }

    // Get Image Path
    public function  getCommercialPathAttribute()
    {
        if ($this->photo != null) {
            return asset('assets/images/clients/' . $this->photo);
        } else {
            return null;
        }
    } // End of get Image Path

    // Get Image Path
    public function  getTaxcertificatePathAttribute()
    {
        if ($this->photo != null) {
            return asset('assets/images/clients/' . $this->photo);
        } else {
            return null;
        }
    } // End of get Image Path

}