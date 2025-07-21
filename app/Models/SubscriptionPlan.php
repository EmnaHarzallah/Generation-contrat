<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'features', 
        'contract_template',
        'duration',

    ];
    public function contracts()
{
    return $this->hasMany(Contract::class);
}
} 