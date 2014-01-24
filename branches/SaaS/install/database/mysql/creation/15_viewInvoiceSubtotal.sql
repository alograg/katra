-- ----------------------------------------------------------------------------
-- View viewInvoiceSubtotal
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoiceSubtotal AS
    SELECT concept.invoice, COUNT(concept.concept) as quantity,
        SUM(concept.quantity*concept.price) as subtotal
    FROM concept
    GROUP BY concept.invoice;
