-- ----------------------------------------------------------------------------
-- View viewInvoiceBalance
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoiceBalance AS
    SELECT invoice.invoice, invoice.createdOn, invoice.account, invoice.status,
        invoice.currency, invoice.taxes, invoice.data,
        viewInvoiceSubtotal.quantity as quantityOfConcepts,
        viewInvoiceSubtotal.subtotal,
        viewInvoicePayment.quantity as quantityOfPayments,
        viewInvoicePayment.failed, viewInvoicePayment.successed,
        viewInvoicePayment.total,
        (viewInvoiceSubtotal.subtotal+invoice.taxes)
            - viewInvoicePayment.total > 0 as underpayment
    FROM invoice
    LEFT JOIN viewInvoiceSubtotal USING(invoice)
    LEFT JOIN viewInvoicePayment USING(invoice, currency);
