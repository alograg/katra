-- ----------------------------------------------------------------------------
-- Table exchange
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS exchange (
    currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Identificador de moneda',
    rate DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT 1.000000
        COMMENT 'Tipo de cambio de unidad a moneda',
    editOn DATE NOT NULL DEFAULT '2012-01-01' COMMENT 'Fecha de ultima actualización',
    PRIMARY KEY (currency)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Intercambio de unidades de cobro';
