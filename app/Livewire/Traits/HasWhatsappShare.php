<?php

namespace App\Livewire\Traits;

use App\Models\Customer;

trait HasWhatsappShare
{
    /**
     * Generate universal WhatsApp api.whatsapp.com link with pre-filled document details.
     */
    public function getWhatsappUrl(string $docType, string $number, ?Customer $customer, float $total, string $signedUrl, string $currency = 'RM'): string
    {
        $phone = '';
        $customerName = 'Customer';
        if ($customer) {
            $customerName = $customer->name;
            // Clean phone number (keep only digits)
            $phone = preg_replace('/[^0-9]/', '', (string) $customer->phone);
        }

        // Generate specific messages matching the user's templates
        if ($docType === 'Invoice') {
            $message = "Hi {$customerName}, thank you for your purchase, total is {$currency}" . number_format($total, 2) . ". Please click at this link to access your invoice: {$signedUrl}";
        } elseif ($docType === 'Quotation') {
            $message = "Hi {$customerName}, here is your quotation {$number}, total is {$currency}" . number_format($total, 2) . ". Please click at this link to access it: {$signedUrl}";
        } elseif ($docType === 'Sales Order') {
            $message = "Hi {$customerName}, thank you for your order {$number}, total is {$currency}" . number_format($total, 2) . ". Please click at this link to access it: {$signedUrl}";
        } elseif ($docType === 'Delivery Order') {
            $message = "Hi {$customerName}, your delivery order {$number} has been generated. Please click at this link to access it: {$signedUrl}";
        } elseif ($docType === 'Receipt') {
            $message = "Hi {$customerName}, thank you for your payment of {$currency}" . number_format($total, 2) . ". Please click at this link to access your receipt {$number}: {$signedUrl}";
        } else {
            $message = "Hi {$customerName}, here is your {$docType} {$number}: {$signedUrl}";
        }

        return "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . rawurlencode($message);
    }
}
