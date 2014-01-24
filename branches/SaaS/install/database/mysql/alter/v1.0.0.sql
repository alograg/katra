-- ----------------------------------------------------------------------------
-- Este script debe ser ejecutado en una version 1.0.0
-- ----------------------------------------------------------------------------
UPDATE users SET usu_password = MD5(DECODE(usu_password, usu_email));
ALTER TABLE users MODIFY
    COLUMN usu_password CHAR(32) NOT NULL COMMENT 'Contraseña de acceso.(encriptado)',
    COMMENT = 'v1.1.0 Usuarios del sistema';
