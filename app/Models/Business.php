<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

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
    
}
