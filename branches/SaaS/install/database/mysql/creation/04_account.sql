-- ----------------------------------------------------------------------------
-- Table account
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS account (
    account INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico de cuenta',
    name VARCHAR(250) NOT NULL COMMENT 'Nombre de la cuenta',
    firm VARCHAR(250) NOT NULL COMMENT 'Nombre de la empresa',
    bussiness VARCHAR(250) NOT NULL COMMENT 'Razón social',
    street VARCHAR(250) NOT NULL COMMENT 'Calle',
    outside VARCHAR(250) NOT NULL COMMENT 'Numero exterior',
    inside VARCHAR(250) NOT NULL COMMENT 'Interior',
    crosses TEXT COMMENT 'Cruces',
    zip CHAR(5) NOT NULL COMMENT 'Código postal',
    colony VARCHAR(250) NOT NULL COMMENT 'Colonia',
    city VARCHAR(250) NOT NULL COMMENT 'Ciudad',
    state VARCHAR(250) NOT NULL COMMENT 'Estado/Region',
    country CHAR(2) NOT NULL COMMENT 'País',
    phone VARCHAR(250) NOT NULL COMMENT 'Teléfono(s)',
    fax VARCHAR(250) NOT NULL COMMENT 'Fax(s)',
    email VARCHAR(250) NOT NULL COMMENT 'Correo electrónico administrativo',
    taxKey VARCHAR(250) NOT NULL COMMENT 'RFC o identificador fiscal',
    status ENUM('free','active','blocked','canceled') NOT NULL DEFAULT 'free'
        COMMENT 'Estado de la cuenta',
    currency CHAR(3) NOT NULL COMMENT 'Moneda de la cuenta',
    configuration BLOB NOT NULL
        COMMENT 'Datos de configuración de la cuenta (encriptado)',
    PRIMARY KEY (account),
    UNIQUE INDEX name (name ASC),
    INDEX country (country ASC),
    INDEX status (status ASC),
    INDEX currency (currency ASC),
    INDEX state (state ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.1 Cuentas de cliente';
