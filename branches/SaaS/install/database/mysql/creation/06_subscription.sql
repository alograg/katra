-- ----------------------------------------------------------------------------
-- Table subscription
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscription (
    subscription INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador de la suscripción',
    package INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador del paquete',
    account INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador de la cuenta',
    status ENUM('active','blocked','canceled') NOT NULL DEFAULT 'blocked'
        COMMENT 'Estado de la suscripción',
    createdOn DATE NOT NULL DEFAULT '2012-01-01'
        COMMENT 'Fecha de generación de la suscripción',
    configuration BLOB NULL COMMENT 'Configuración a la base de datos (encriptado)',
    PRIMARY KEY (subscription),
    INDEX subscriptionPackage (package ASC),
    INDEX subscriptionAccount (account ASC),
    INDEX relation (package ASC, account ASC),
    INDEX status (status ASC),
    CONSTRAINT subscriptionPackagePackage
        FOREIGN KEY (package)
        REFERENCES package (package)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT subscriptionAccountAccount
        FOREIGN KEY (account)
        REFERENCES account (account)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Suscripciones por parte de las cuentas';
