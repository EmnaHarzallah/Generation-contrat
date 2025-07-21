<?php

    namespace App\Service;

use PhpOffice\PhpWord\TemplateProcessor;

class ContractService
{
    public function generateContract($user, $subscriptionPlan): string
    {
        $templatePath = storage_path('app/WEEFIZZ-ContratBetaTest.docx');
        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('user_name', $user->name);
        $templateProcessor->setValue('plan_name', $subscriptionPlan->name);
        $templateProcessor->setValue('plan_price', $subscriptionPlan->price);
        $templateProcessor->setValue('plan_duration', $subscriptionPlan->duration);
        $templateProcessor->setValue('plan_features', $subscriptionPlan->features);
        $templateProcessor->setValue('plan_contract_template', $subscriptionPlan->contract_template);

        $outputPath = storage_path('app/contracts/generated/contract_' . $user->id . '.docx');
        $templateProcessor->saveAs($outputPath);

        return $outputPath;
    }
}
?>