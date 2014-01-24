-- ----------------------------------------------------------------------------
-- Table mapSubscriptionMember
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mapSubscriptionMember (
    subscription INT UNSIGNED NOT NULL COMMENT 'Identificador de la suscripción',
    member INT UNSIGNED NOT NULL COMMENT 'Identificador del usuario',
    level ENUM('admin','user','customer','contact') NOT NULL DEFAULT 'user'
        COMMENT 'Relación del usuario a la cuenta',
    email VARCHAR(250) NOT NULL COMMENT 'Correo de contacto en la suscripción',
    PRIMARY KEY (subscription, member),
    INDEX mapSubscriptionMemberMemberSubscription (subscription ASC),
    INDEX mapSubscriptionMemberMemberMember (member ASC),
    INDEX types (level ASC),
    CONSTRAINT mapSubscriptionMemberSubscriptionSubscription
        FOREIGN KEY (subscription)
        REFERENCES subscription (subscription)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT mapSubscriptionMemberMemberMember
        FOREIGN KEY (member)
        REFERENCES member (member)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Relación de usuarios y cuentas';
