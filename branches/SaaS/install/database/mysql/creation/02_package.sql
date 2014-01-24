-- ----------------------------------------------------------------------------
-- Table package
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS package (
    package INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico del paquete',
    service INT UNSIGNED NOT NULL COMMENT 'Identificador del servicio',
    name VARCHAR(250) NOT NULL COMMENT 'Nombre',
    description TINYTEXT NOT NULL COMMENT 'Descripción',
    units DECIMAL(7,2) NOT NULL DEFAULT 1.00
        COMMENT 'Unidades para el calculo de precio',
    configuration TINYTEXT NOT NULL COMMENT 'Configuración para el paquete',
    recurrence ENUM('unic','month','year') NOT NULL DEFAULT 'month'
        COMMENT 'Recurrencia del cobro',
    PRIMARY KEY (package),
    INDEX packageService (service ASC),
    INDEX recurrence (recurrence ASC),
    CONSTRAINT pagakeServiceService
        FOREIGN KEY (service)
        REFERENCES service (service)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.3 Información de los paquetes disponibles.';
