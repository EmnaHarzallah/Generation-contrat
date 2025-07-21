<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpWord\TemplateProcessor;

class Contract extends Model
{

   public function generateContract($user, $subscriptionPlan)           
   {
       $templatePath = storage_path('storage\app\WEEFIZZ-ContratBetaTest.docx');
       $templateProcessor = new TemplateProcessor($templatePath);

       // Replace placeholders in your .docx template
       $templateProcessor->setValue('user_name', $user->name);
       $templateProcessor->setValue('plan_name', $subscriptionPlan->name);
       $templateProcessor->setValue('plan_price', $subscriptionPlan->price);
       $templateProcessor->setValue('plan_duration', $subscriptionPlan->duration);
       $templateProcessor->setValue('plan_features', $subscriptionPlan->features);
       $templateProcessor->setValue('plan_contract_template', $subscriptionPlan->contract_template);
       // Add more as needed

       $outputPath = storage_path('app/contracts/generated/contract_' . $user->id . '.docx');
       $templateProcessor->saveAs($outputPath);

       // You can now offer this file for download or store it
       return response()->download($outputPath);
   }

   public function user()
   {
       return $this->belongsTo(User::class);
   }

   public function subscriptionPlan()
   {
       return $this->belongsTo(SubscriptionPlan::class);
   }
} 