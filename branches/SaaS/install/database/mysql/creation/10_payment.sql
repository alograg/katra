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
