-- ----------------------------------------------------------------------------
-- View viewAccountInvoice
-- v2.0.1
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewAccountInvoice AS
    SELECT account.account, account.name as accountName, account.firm,
        account.bussiness, account.street, account.outside, account.inside,
        account.crosses, account.zip, account.colony, account.city, account.state,
        account.country, account.phone, account.fax, account.email as accountEmail,
        account.taxKey, account.status as accountStatus, viewInvoiceBalance.invoice,
        viewInvoiceBalance.createdOn, viewInvoiceBalance.status as invoiceStatus,
        viewInvoiceBalance.currency, viewInvoiceBalance.taxes, viewInvoiceBalance.data,
        viewInvoiceBalance.quantityOfConcepts, viewInvoiceBalance.subtotal,
        viewInvoiceBalance.quantityOfPayments, viewInvoiceBalance.failed,
        viewInvoiceBalance.successed, viewInvoiceBalance.total,
        viewInvoiceBalance.underpayment
    FROM account
    LEFT JOIN viewInvoiceBalance USING(account);
