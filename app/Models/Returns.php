<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    use HasFactory;
    
    public function product(){
        return $this->belongsTo(Product::class, 'product_id','id');
    }

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id','id');
    }
}
