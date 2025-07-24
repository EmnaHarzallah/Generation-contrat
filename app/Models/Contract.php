<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpWord\TemplateProcessor;

class Contract extends Model
{

   
   public function user()
   {
       return $this->belongsTo(User::class);
   }

   public function subscriptionPlan()
   {
       return $this->belongsTo(SubscriptionPlan::class);
   }
} 