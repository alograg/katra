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
