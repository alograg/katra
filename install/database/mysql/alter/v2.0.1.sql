-- ----------------------------------------------------------------------------
-- Este script debe ser ejecutado en una version 2.0.1
-- Despues ejecutar los scripts de creacion:
-- - 12_viewSubscriptionMember.sql
-- - 13_viewMemberService.sql
-- - 14_viewMemberAccount.sql
-- ----------------------------------------------------------------------------

-- ----------------------------------------------------------------------------
-- Borrado de vistas afectadas
-- ----------------------------------------------------------------------------
DROP VIEW IF EXISTS viewMemberAccount;
DROP VIEW IF EXISTS viewMemberService;
DROP VIEW IF EXISTS viewSubscriptionMember;

-- ----------------------------------------------------------------------------
-- Cambios en campos
-- ----------------------------------------------------------------------------
ALTER TABLE member 
    CHANGE COLUMN fullname fullName VARCHAR(250)
        NOT NULL COMMENT 'Nombre completo del miembro',
    COMMENT = 'v2.0.2 Miembros de cuentas';
