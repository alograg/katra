-- ----------------------------------------------------------------------------
-- Este script debe ser ejecutado en una version 2.0.0
-- ----------------------------------------------------------------------------

-- ----------------------------------------------------------------------------
-- Cambios en campos
-- ----------------------------------------------------------------------------
ALTER TABLE account
    ADD UNIQUE INDEX name (name ASC),
    COMMENT = 'v2.0.1 Cuentas de cliente';
ALTER TABLE member
    CHANGE COLUMN member member INT(10) UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador del miembro',
    COMMENT = 'v2.0.1 Miembros de cuentas';
