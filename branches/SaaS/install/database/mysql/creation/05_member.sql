-- ----------------------------------------------------------------------------
-- Table member
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS member (
    member INT  UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador del miembro',
    nick VARCHAR(50) NOT NULL COMMENT 'Nick de ingreso',
    email VARCHAR(250) NOT NULL COMMENT 'Correo electrónico del usuario',
    password CHAR(32) NOT NULL COMMENT 'Contraseña de acceso (encriptado)',
    fullName VARCHAR(250) NOT NULL COMMENT 'Nombre completo del miembro',
    language ENUM('es','en','pt') NOT NULL DEFAULT 'es'
        COMMENT 'Lenguaje preferido del miembro',
    PRIMARY KEY (member),
    UNIQUE INDEX nick (nick ASC),
    INDEX email (email ASC),
    INDEX access (nick ASC, password ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.2 Miembros de cuentas';
