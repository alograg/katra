-- ----------------------------------------------------------------------------
-- Eliminación de las tablas y vistas de SaaS
-- v2.0.0
-- ----------------------------------------------------------------------------
DROP VIEW IF EXISTS viewAccountInvoice;
DROP VIEW IF EXISTS viewInvoiceBalance;
DROP VIEW IF EXISTS viewInvoicePayments;
DROP VIEW IF EXISTS viewInvoiceSubtotal;
DROP TABLE IF EXISTS payment;
DROP TABLE IF EXISTS concept;
DROP TABLE IF EXISTS invoice;
-- ----------------------------------------------------------------------------
-- Table invoice
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice (
    invoice INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico de facturación del sistema',
    createdOn DATE NOT NULL DEFAULT '2012-01-01' COMMENT 'Fecha de la factura',
    account INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Cuenta',
    status ENUM('current','paid','late','canceled') NOT NULL DEFAULT 'current'
        COMMENT 'Estado de la factura',
    currency CHAR(3) NOT NULL DEFAULT 'MXP'
        COMMENT 'Moneda de los montos de los conceptos',
    taxes DECIMAL(5,2) NOT NULL DEFAULT 0.00
        COMMENT 'Impuesto cobrables por la factura',
    data TINYTEXT NOT NULL COMMENT 'Datos de factura física y/o electrónica',
    PRIMARY KEY (invoice),
    INDEX invoiceAccount (account ASC),
    INDEX status (status ASC),
    INDEX currency (currency ASC),
    CONSTRAINT invoiceAccountAccount
        FOREIGN KEY (account)
        REFERENCES account (account)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Facturas a las cuentas';
-- ----------------------------------------------------------------------------
-- Table concept
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS concept (
    invoice INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Numero de factura del sistema',
    concept INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico del concepto',
    description TINYTEXT NOT NULL COMMENT 'Descripción del cobro',
    quantity INT NOT NULL DEFAULT 1 COMMENT 'Cantidad de elementos',
    price DECIMAL(5,2) NOT NULL DEFAULT 1 COMMENT 'Precio del elemento',
    PRIMARY KEY (concept),
    INDEX conceptInvoiceInvoice (invoice ASC),
    CONSTRAINT conceptInvoiceInvoice
        FOREIGN KEY (invoice)
        REFERENCES invoice (invoice)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Conceptos de las facturas del sistema';
-- ----------------------------------------------------------------------------
-- Table payment
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment (
    invoice INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Identificador de la factura del sistema',
    payment INT UNSIGNED NOT NULL COMMENT 'Identificador del intento de pago',
    createdAt TIMESTAMP NOT NULL DEFAULT NOW()
        COMMENT 'Fecha y tiempo del intento',
    type ENUM('failed','successful','manual') NOT NULL DEFAULT 'failed'
        COMMENT 'Resultado del intento de pago',
    currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Moneda del pago',
    amount DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Monto del pago',
    details TEXT NULL COMMENT 'Detalles del intento',
    PRIMARY KEY (payment),
    INDEX paymentInvoiceInvoice (invoice ASC),
    INDEX results (type ASC),
    CONSTRAINT paymentInvoiceInvoice
        FOREIGN KEY (invoice)
        REFERENCES invoice (invoice)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Intentos y pagos realizados';
-- ----------------------------------------------------------------------------
-- View viewInvoiceSubtotal
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoiceSubtotal AS
    SELECT concept.invoice, COUNT(concept.concept) as quantity,
        SUM(concept.quantity*concept.price) as subtotal
    FROM concept
    GROUP BY concept.invoice;
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
-- ----------------------------------------------------------------------------
-- View viewAccountInvoice
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewAccountInvoice AS
    SELECT account.name as accountName, account.firm, account.bussiness,
        account.street, account.outside, account.inside, account.crosses,
        account.zip, account.colony, account.city, account.state,
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
