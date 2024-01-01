<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;


    protected $fillable = [
        'business_id',
        'branch_id',
        'product_id',
        'buying_price',
        'quantity',
        'old_quantity',
        'old_buying_price',
        'old_selling_price',
        'date',
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id','id');
    }
}
