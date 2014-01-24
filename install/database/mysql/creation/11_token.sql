-- ----------------------------------------------------------------------------
-- Table token
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS token (
    member INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador de miembro',
    token CHAR(32) NOT NULL COMMENT 'El token',
    loginAt TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Momento de ultimo ingreso',
    url VARCHAR(250) NOT NULL COMMENT 'Dirección del acceso al token',
    PRIMARY KEY (member),
    INDEX tokenMember (member ASC),
    INDEX token (token ASC),
    CONSTRAINT tokenMemberMember
        FOREIGN KEY (member)
        REFERENCES member (member)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Contiene las conexiones activas';
