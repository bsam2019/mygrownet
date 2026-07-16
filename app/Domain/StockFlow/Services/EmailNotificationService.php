<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaEmailSettingModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class EmailNotificationService
{
    public function sendInvoice(int $companyId, array $invoice, string $toEmail, ?string $pdfPath = null): bool
    {
        return $this->sendWithStoredSettings($companyId, $toEmail, "Invoice #{$invoice['invoice_number']}", 'Your invoice is attached.', $pdfPath);
    }

    public function sendQuotation(int $companyId, array $quotation, string $toEmail, ?string $pdfPath = null): bool
    {
        return $this->sendWithStoredSettings($companyId, $toEmail, "Quotation #{$quotation['quotation_number']}", 'Your quotation is attached.', $pdfPath);
    }

    public function sendReceipt(int $companyId, array $receipt, string $toEmail, ?string $pdfPath = null): bool
    {
        return $this->sendWithStoredSettings($companyId, $toEmail, "Receipt #{$receipt['receipt_number']}", 'Your receipt is attached.', $pdfPath);
    }

    public function sendLowStockAlert(int $companyId, string $toEmail, array $items): bool
    {
        $subject = 'Low Stock Alert';
        $body = "The following items are low on stock:\n\n";
        foreach ($items as $item) {
            $body .= "- {$item['name']}: {$item['system_quantity']} remaining\n";
        }
        return $this->sendWithStoredSettings($companyId, $toEmail, $subject, $body);
    }

    private function sendWithStoredSettings(int $companyId, string $toEmail, string $subject, string $body, ?string $attachmentPath = null): bool
    {
        try {
            $settings = SaEmailSettingModel::where('sa_company_id', $companyId)->where('verified', true)->first();
            if (!$settings) return false;

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings->smtp_host,
                'mail.mailers.smtp.port' => $settings->smtp_port,
                'mail.mailers.smtp.username' => $settings->smtp_username,
                'mail.mailers.smtp.password' => $settings->smtp_password,
                'mail.mailers.smtp.encryption' => $settings->smtp_encryption,
                'mail.from.address' => $settings->from_address,
                'mail.from.name' => $settings->from_name,
            ]);

            Mail::raw($body, function (Message $message) use ($toEmail, $subject, $attachmentPath) {
                $message->to($toEmail)->subject($subject);
                if ($attachmentPath && file_exists($attachmentPath)) {
                    $message->attach($attachmentPath);
                }
            });

            return true;
        } catch (\Throwable $e) {
            throw new OperationFailedException('send email', $e->getMessage());
        }
    }
}
