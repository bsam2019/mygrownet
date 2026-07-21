<?php

declare(strict_types=1);

namespace App\Domain\BMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\MaterialPurchaseOrderModel;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfPurchaseOrderService
{
    public function download(MaterialPurchaseOrderModel $purchaseOrder)
    {
        $pdf = $this->generate($purchaseOrder);

        return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
    }

    public function stream(MaterialPurchaseOrderModel $purchaseOrder)
    {
        $pdf = $this->generate($purchaseOrder);

        return $pdf->stream("PO-{$purchaseOrder->po_number}.pdf");
    }

    private function generate(MaterialPurchaseOrderModel $purchaseOrder)
    {
        $purchaseOrder->load(['company', 'items', 'job']);

        $data = [
            'purchaseOrder' => $purchaseOrder,
            'company' => $purchaseOrder->company,
            'items' => $purchaseOrder->items,
        ];

        return Pdf::loadView('pdf.cms.purchase-order', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);
    }
}
