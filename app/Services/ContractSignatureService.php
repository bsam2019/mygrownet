<?php

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\ContractModel;
use App\Infrastructure\Persistence\Eloquent\BMS\ContractSignatureModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractSignatureService
{
    public function sign(ContractModel $contract, string $party, string $signatureData, ?string $signerName = null, ?string $signerEmail = null, ?string $ipAddress = null, ?string $userAgent = null): ContractSignatureModel
    {
        $signature = ContractSignatureModel::create([
            'contract_id' => $contract->id,
            'party' => $party,
            'signer_name' => $signerName,
            'signer_email' => $signerEmail,
            'signature_data' => $signatureData,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'signed_at' => now(),
        ]);

        if ($party === 'customer') {
            $contract->update(['signed_by_customer' => true]);
        } else {
            $contract->update(['signed_by_company' => true]);
        }

        if ($contract->signed_by_customer && $contract->signed_by_company) {
            $contract->update(['signed_at' => now()]);

            if ($contract->fresh()->signed_pdf_path === null) {
                $this->generateSignedPdf($contract);
            }
        }

        return $signature;
    }

    public function generateSigningToken(ContractModel $contract): string
    {
        $token = Str::random(64);
        $contract->update(['signing_token' => $token]);

        return $token;
    }

    public function generateSignedPdf(ContractModel $contract): string
    {
        $companySignatures = $contract->signatures()->where('party', 'company')->get();
        $customerSignatures = $contract->signatures()->where('party', 'customer')->get();

        $pdf = Pdf::loadView('pdf.contract', [
            'contract' => $contract,
            'companySignatures' => $companySignatures,
            'customerSignatures' => $customerSignatures,
        ]);

        $path = 'contracts/signed/' . $contract->contract_number . '-' . now()->format('Ymd-His') . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $contract->update(['signed_pdf_path' => $path]);

        return $path;
    }

    public function getSignedPdfUrl(ContractModel $contract): ?string
    {
        if (!$contract->signed_pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($contract->signed_pdf_path);
    }
}
