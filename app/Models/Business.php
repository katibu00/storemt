<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $dates = ['subscription_start_date', 'subscription_end_date'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class,'business_id');
    }

    public function isSubscribed()
    {
        // Check if the current date is within the subscription period
        return $this->subscription_status === 'active' && now()->between($this->subscription_start_date, $this->subscription_end_date);
    }


    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
    
}
