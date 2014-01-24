-- ----------------------------------------------------------------------------
-- View viewInvoicePayments
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoicePayment AS
    SELECT payment.invoice, payment.currency, COUNT(payment.payment) as quantity,
        SUM(IF(payment.type+0=1,1,0)) as failed,
        SUM(IF(payment.type+0>=2,1,0)) as successed,
        SUM(IF(payment.type+0>=2,payment.amount,0)) as total
    FROM payment
    GROUP BY payment.invoice, payment.currency;
