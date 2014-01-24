-- ----------------------------------------------------------------------------
-- Table service
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS service (
    service INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico del servicio',
    name VARCHAR(250) NOT NULL COMMENT 'Nombre del servicio',
    sqlCreation TEXT NULL
        COMMENT 'Contiene el SQL de creación de tablas para el servicio',
    status ENUM('off','private beta','public beta','release') NOT NULL
        DEFAULT 'off' COMMENT 'Estado de la aplicación' ,
    PRIMARY KEY (service),
    INDEX status (status ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Servicios disponibles por SaaS';
