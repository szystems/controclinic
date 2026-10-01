<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /**
     * Genera el próximo número de factura para la clínica.
     * Formato: {prefix}{numero_con_ceros} — ej: CC-000123
     * Usa una transacción con SELECT FOR UPDATE para evitar duplicados.
     */
    public function nextInvoiceNumber(Clinic $clinic): string
    {
        return DB::transaction(function () use ($clinic) {
            $locked = Clinic::whereKey($clinic->id)->lockForUpdate()->first();
            $settings = $locked->settings ?? [];
            $prefix = $settings['invoice_prefix'] ?? 'INV-';
            $next = (int) ($settings['next_invoice_number'] ?? 1);

            $number = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);

            $settings['next_invoice_number'] = $next + 1;
            $locked->update(['settings' => $settings]);
            $clinic->refresh();

            return $number;
        });
    }

    /**
     * Recalcula subtotal, discount, tax y total de la factura
     * a partir de sus líneas y actualiza los campos en BD.
     */
    public function recalculate(Invoice $invoice): void
    {
        $invoice->loadMissing('items');

        $subtotal = 0.0;
        $discount = 0.0;
        $tax = 0.0;

        foreach ($invoice->items as $item) {
            $base = (float) $item->unit_price * (float) $item->quantity;
            $itemDisc = min((float) $item->discount_amount, max($base, 0));
            $net = max(0, $base - $itemDisc);
            $itemTax = $net * ((float) $item->tax_rate / 100);
            $itemTotal = round($net + $itemTax, 2);

            // Actualizar total de la línea si cambió
            if ((float) $item->total !== $itemTotal) {
                $item->update(['total' => $itemTotal]);
            }

            $subtotal += $base;
            $discount += $itemDisc;
            $tax += $itemTax;
        }

        $total = round($subtotal - $discount + $tax, 2);

        $invoice->update([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => round($tax, 2),
            'total' => $total,
        ]);
    }

    /**
     * Registra un pago y actualiza paid_amount + status de la factura.
     */
    public function recordPayment(Invoice $invoice, array $data): void
    {
        if (! in_array($invoice->status, [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL], true)) {
            return;
        }

        DB::transaction(function () use ($invoice, $data) {
            $invoice->payments()->create($data);

            $paid = (float) $invoice->payments()->sum('amount');

            $invoice->update([
                'paid_amount' => round($paid, 2),
                'status' => $this->statusForPaidAmount($invoice, $paid),
            ]);
        });
    }

    /**
     * Anula (elimina) un pago y recalcula el estado de la factura.
     */
    public function deletePayment(Invoice $invoice, string $paymentId): void
    {
        DB::transaction(function () use ($invoice, $paymentId) {
            $payment = $invoice->payments()->findOrFail($paymentId);
            $payment->delete();

            $paid = (float) $invoice->payments()->sum('amount');

            $invoice->update([
                'paid_amount' => round($paid, 2),
                'status' => $this->statusForPaidAmount($invoice, $paid),
            ]);
        });
    }

    private function statusForPaidAmount(Invoice $invoice, float $paid): string
    {
        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            return Invoice::STATUS_CANCELLED;
        }

        return match (true) {
            $paid <= 0 => Invoice::STATUS_PENDING,
            $paid < (float) $invoice->total => Invoice::STATUS_PARTIAL,
            default => Invoice::STATUS_PAID,
        };
    }

    /**
     * Cancela una factura (solo si no está pagada completamente).
     */
    public function cancel(Invoice $invoice): void
    {
        if ($invoice->status === Invoice::STATUS_PAID || $invoice->status === Invoice::STATUS_CANCELLED) {
            return;
        }

        if ((float) $invoice->paid_amount > 0) {
            throw ValidationException::withMessages([
                'invoice' => __('invoices.cannot_cancel_with_payments'),
            ]);
        }

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
    }

    /**
     * Devuelve los tipos de ítem disponibles con sus labels.
     */
    public static function itemTypes(): array
    {
        return [
            InvoiceItem::TYPE_CONSULTATION => __('invoices.item_type_consultation'),
            InvoiceItem::TYPE_PROCEDURE => __('invoices.item_type_procedure'),
            InvoiceItem::TYPE_MEDICATION => __('invoices.item_type_medication'),
            InvoiceItem::TYPE_LAB => __('invoices.item_type_lab'),
            InvoiceItem::TYPE_OTHER => __('invoices.item_type_other'),
        ];
    }

    /**
     * Devuelve los métodos de pago disponibles con sus labels.
     */
    public static function paymentMethods(): array
    {
        return [
            'cash' => __('invoices.payment_method_cash'),
            'card' => __('invoices.payment_method_card'),
            'transfer' => __('invoices.payment_method_transfer'),
            'insurance' => __('invoices.payment_method_insurance'),
            'other' => __('invoices.payment_method_other'),
        ];
    }
}
