<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Listeners;

use App\Domain\StockFlow\Events\PurchaseOrderReceived;
use App\Domain\StockFlow\Services\ActivityLogService;
use App\Domain\StockFlow\Services\StockFlowNotificationService;

class ActivityLogListener
{
    public function __construct(
        private ActivityLogService $logService,
        private StockFlowNotificationService $notifier,
    ) {}

    public function onSaleCompleted(array $payload): void
    {
        $this->logService->record(
            event: $payload,
            context: 'sales',
            eventName: 'SaleCompleted',
            description: "Sale #{$payload['receipt_number']} completed — {$payload['payment_method']}, total " . number_format($payload['total'], 2),
            subjectType: 'Sale',
            subjectId: $payload['sale_id'],
            actorUserId: $payload['sold_by'],
            extraPayload: [
                'total' => $payload['total'],
                'payment_method' => $payload['payment_method'],
                'items_count' => count($payload['items']),
            ],
        );

        $this->notifier->create(
            companyId: $payload['company_id'],
            userId: $payload['sold_by'],
            type: 'sale.completed',
            title: 'Sale Completed',
            message: "Sale #{$payload['receipt_number']} — {$payload['payment_method']}, " . number_format($payload['total'], 2),
            actionUrl: '/stockflow/sales/' . $payload['sale_id'],
            actionText: 'View Sale',
            data: ['total' => $payload['total'], 'receipt' => $payload['receipt_number']],
        );
    }

    public function onPurchaseOrderReceived(PurchaseOrderReceived $event): void
    {
        $this->logService->record(
            event: $event,
            context: 'purchasing',
            eventName: 'PurchaseOrderReceived',
            description: "PO #{$event->getOrderNumber()} fully received — " . count($event->getItems()) . ' items',
            subjectType: 'PurchaseOrder',
            subjectId: $event->getPurchaseOrderId(),
            actorUserId: $event->getReceivedBy(),
            extraPayload: ['items_count' => count($event->getItems())],
        );

        $this->notifier->create(
            companyId: $event->getCompanyId(),
            userId: $event->getReceivedBy(),
            type: 'po.received',
            title: 'Purchase Order Received',
            message: "PO #{$event->getOrderNumber()} — " . count($event->getItems()) . ' items received',
            actionUrl: '/stockflow/purchases/' . $event->getPurchaseOrderId(),
            actionText: 'View PO',
            data: ['items_count' => count($event->getItems())],
        );
    }

    public function onStockCountFinalized(array $payload): void
    {
        $totals = $payload['totals'];
        $this->logService->record(
            event: $payload,
            context: 'audit',
            eventName: 'StockCountFinalized',
            description: "Stock count finalized — variance: " . number_format($totals['total_variance'] ?? 0, 2),
            subjectType: 'PhysicalCount',
            subjectId: $payload['physical_count_id'],
            actorUserId: $payload['finalized_by'],
            extraPayload: $totals,
        );

        $variance = $totals['total_variance'] ?? 0;
        $priority = abs($variance) > 1000 ? 'high' : 'normal';
        $this->notifier->notifyAllAdmins(
            companyId: $payload['company_id'],
            type: 'count.finalized',
            title: 'Stock Count Finalized',
            message: 'Variance: ' . number_format($variance, 2),
            actionUrl: '/stockflow/physical-counts/' . $payload['physical_count_id'],
            actionText: 'View Count',
            data: $totals,
            priority: $priority,
        );
    }

    public function onCashDiscrepancyDetected(array $payload): void
    {
        $this->logService->record(
            event: $payload,
            context: 'sales',
            eventName: 'CashDiscrepancyDetected',
            description: "Cash discrepancy on {$payload['register_date']}: expected " . number_format($payload['expected_amount'], 2) . ", counted " . number_format($payload['counted_amount'], 2) . ", variance " . number_format($payload['variance'], 2),
            subjectType: 'CashRegister',
            subjectId: $payload['cash_register_id'],
            extraPayload: [
                'expected' => $payload['expected_amount'],
                'counted' => $payload['counted_amount'],
                'variance' => $payload['variance'],
            ],
        );

        $this->notifier->notifyAllAdmins(
            companyId: $payload['company_id'],
            type: 'cash.discrepancy',
            title: 'Cash Discrepancy Detected',
            message: 'Variance: ' . number_format($payload['variance'], 2),
            actionUrl: '/stockflow/cash/' . $payload['cash_register_id'],
            actionText: 'View Register',
            data: ['expected' => $payload['expected_amount'], 'counted' => $payload['counted_amount'], 'variance' => $payload['variance']],
            priority: 'high',
        );
    }

    public function onStockAdjusted(array $payload): void
    {
        $this->logService->record(
            event: $payload,
            context: 'inventory',
            eventName: 'StockAdjusted',
            description: "Item #{$payload['item_id']} adjusted ({$payload['adjustment_type']}): {$payload['quantity_before']} → {$payload['quantity_after']} — {$payload['reason']}",
            subjectType: 'Item',
            subjectId: $payload['item_id'],
            actorUserId: $payload['adjusted_by'],
            extraPayload: [
                'type' => $payload['adjustment_type'],
                'before' => $payload['quantity_before'],
                'after' => $payload['quantity_after'],
            ],
        );

        $this->notifier->create(
            companyId: $payload['company_id'],
            userId: $payload['adjusted_by'],
            type: 'stock.adjusted',
            title: 'Stock Adjusted',
            message: "{$payload['adjustment_type']}: {$payload['quantity_before']} → {$payload['quantity_after']} — {$payload['reason']}",
            actionUrl: '/stockflow/items/' . $payload['item_id'],
            actionText: 'View Item',
            data: ['type' => $payload['adjustment_type'], 'before' => $payload['quantity_before'], 'after' => $payload['quantity_after']],
        );
    }
}
