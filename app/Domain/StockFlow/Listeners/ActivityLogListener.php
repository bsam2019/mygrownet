<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Listeners;

use App\Domain\StockFlow\Events\CashDiscrepancyDetected;
use App\Domain\StockFlow\Events\PurchaseOrderReceived;
use App\Domain\StockFlow\Events\SaleCompleted;
use App\Domain\StockFlow\Events\StockAdjusted;
use App\Domain\StockFlow\Events\StockCountFinalized;
use App\Domain\StockFlow\Services\ActivityLogService;
use App\Domain\StockFlow\Services\StockFlowNotificationService;

class ActivityLogListener
{
    public function __construct(
        private ActivityLogService $logService,
        private StockFlowNotificationService $notifier,
    ) {}

    public function onSaleCompleted(SaleCompleted $event): void
    {
        $this->logService->record(
            event: $event,
            context: 'sales',
            eventName: 'SaleCompleted',
            description: "Sale #{$event->getReceiptNumber()} completed — {$event->getPaymentMethod()}, total " . number_format($event->getTotal(), 2),
            subjectType: 'Sale',
            subjectId: $event->getSaleId(),
            actorUserId: $event->getSoldBy(),
            extraPayload: [
                'total' => $event->getTotal(),
                'payment_method' => $event->getPaymentMethod(),
                'items_count' => count($event->getItems()),
            ],
        );

        $this->notifier->create(
            companyId: $event->getCompanyId(),
            userId: $event->getSoldBy(),
            type: 'sale.completed',
            title: 'Sale Completed',
            message: "Sale #{$event->getReceiptNumber()} — {$event->getPaymentMethod()}, " . number_format($event->getTotal(), 2),
            actionUrl: '/stock-audit/sales/' . $event->getSaleId(),
            actionText: 'View Sale',
            data: ['total' => $event->getTotal(), 'receipt' => $event->getReceiptNumber()],
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
            actionUrl: '/stock-audit/purchases/' . $event->getPurchaseOrderId(),
            actionText: 'View PO',
            data: ['items_count' => count($event->getItems())],
        );
    }

    public function onStockCountFinalized(StockCountFinalized $event): void
    {
        $totals = $event->getTotals();
        $this->logService->record(
            event: $event,
            context: 'audit',
            eventName: 'StockCountFinalized',
            description: "Stock count finalized — variance: " . number_format($totals['total_variance'] ?? 0, 2),
            subjectType: 'PhysicalCount',
            subjectId: $event->getPhysicalCountId(),
            actorUserId: $event->getFinalizedBy(),
            extraPayload: $totals,
        );

        $variance = $totals['total_variance'] ?? 0;
        $priority = abs($variance) > 1000 ? 'high' : 'normal';
        $this->notifier->notifyAllAdmins(
            companyId: $event->getCompanyId(),
            type: 'count.finalized',
            title: 'Stock Count Finalized',
            message: 'Variance: ' . number_format($variance, 2),
            actionUrl: '/stock-audit/physical-counts/' . $event->getPhysicalCountId(),
            actionText: 'View Count',
            data: $totals,
            priority: $priority,
        );
    }

    public function onCashDiscrepancyDetected(CashDiscrepancyDetected $event): void
    {
        $this->logService->record(
            event: $event,
            context: 'sales',
            eventName: 'CashDiscrepancyDetected',
            description: "Cash discrepancy on {$event->getRegisterDate()}: expected " . number_format($event->getExpectedAmount(), 2) . ", counted " . number_format($event->getCountedAmount(), 2) . ", variance " . number_format($event->getVariance(), 2),
            subjectType: 'CashRegister',
            subjectId: $event->getCashRegisterId(),
            extraPayload: [
                'expected' => $event->getExpectedAmount(),
                'counted' => $event->getCountedAmount(),
                'variance' => $event->getVariance(),
            ],
        );

        $this->notifier->notifyAllAdmins(
            companyId: $event->getCompanyId(),
            type: 'cash.discrepancy',
            title: 'Cash Discrepancy Detected',
            message: 'Variance: ' . number_format($event->getVariance(), 2),
            actionUrl: '/stock-audit/cash/' . $event->getCashRegisterId(),
            actionText: 'View Register',
            data: ['expected' => $event->getExpectedAmount(), 'counted' => $event->getCountedAmount(), 'variance' => $event->getVariance()],
            priority: 'high',
        );
    }

    public function onStockAdjusted(StockAdjusted $event): void
    {
        $this->logService->record(
            event: $event,
            context: 'inventory',
            eventName: 'StockAdjusted',
            description: "Item #{$event->getItemId()} adjusted ({$event->getAdjustmentType()}): {$event->getQuantityBefore()} → {$event->getQuantityAfter()} — {$event->getReason()}",
            subjectType: 'Item',
            subjectId: $event->getItemId(),
            actorUserId: $event->getAdjustedBy(),
            extraPayload: [
                'type' => $event->getAdjustmentType(),
                'before' => $event->getQuantityBefore(),
                'after' => $event->getQuantityAfter(),
            ],
        );

        $this->notifier->create(
            companyId: $event->getCompanyId(),
            userId: $event->getAdjustedBy(),
            type: 'stock.adjusted',
            title: 'Stock Adjusted',
            message: "{$event->getAdjustmentType()}: {$event->getQuantityBefore()} → {$event->getQuantityAfter()} — {$event->getReason()}",
            actionUrl: '/stock-audit/items/' . $event->getItemId(),
            actionText: 'View Item',
            data: ['type' => $event->getAdjustmentType(), 'before' => $event->getQuantityBefore(), 'after' => $event->getQuantityAfter()],
        );
    }
}
