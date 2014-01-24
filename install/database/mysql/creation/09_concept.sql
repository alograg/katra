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
