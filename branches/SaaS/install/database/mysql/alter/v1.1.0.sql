-- ----------------------------------------------------------------------------
-- Este script debe ser ejecutado en una version 1.0.0 después de
-- ejecutar el script de creación de la version 2.0.0
-- ----------------------------------------------------------------------------

-- ----------------------------------------------------------------------------
-- Traspaso de datos
-- ----------------------------------------------------------------------------
REPLACE INTO service (service, name, sqlCreation, status)
    SELECT ser_id, ser_name, ser_sqlcreation, ser_status
    FROM services;
REPLACE INTO package (package, service, name, description, units,
    configuration, recurrence)
    SELECT pac_id, pac_service, pac_name, pac_description, pac_units,
        pac_configuration, pac_recurrence
    FROM packages;
REPLACE INTO exchange (currency, rate, editOn)
    SELECT exc_currency, exc_rate, exc_date
    FROM exchanges;
REPLACE INTO account (account, name, firm, bussiness, street, outside, inside,
    crosses, zip, colony, city, state, country, phone, fax, email, taxKey,
    status, currency, configuration)
    SELECT acc_id, acc_name, acc_firm, acc_bussiness, acc_street, acc_outside,
        acc_inside, acc_crosses, acc_zip, acc_colony, acc_city, acc_state,
        'mx', acc_phone, acc_fax, acc_email, acc_taxid, acc_status,
        acc_currency, acc_configuration
    FROM accounts;
REPLACE INTO member (member, nick, email, password, fullname, language)
    SELECT usu_id, usu_nick, usu_email, usu_password, usu_fullname,
        usu_language
    FROM users;
REPLACE INTO subscription (subscription, package, account, status, createdOn,
    configuration)
    SELECT sus_id, sus_package, sus_account, sus_status, sus_creation,
        sus_configuration
    FROM suscriptions;
REPLACE INTO mapSubscriptionMember (subscription, member, level, email)
    SELECT ssu_suscription, ssu_user, ssu_type, ssu_email
    FROM suscription_users;
REPLACE INTO invoice (invoice, createdOn, account, status, currency, taxes, data)
    SELECT inv_id, inv_creation, inv_account, inv_status, inv_currency,
        inv_taxes , inv_data
    FROM invoices;
REPLACE INTO concept (invoice , concept, description, quantity, price)
    SELECT con_id, con_invoice, con_description, con_amount, con_price
    FROM concepts;
REPLACE INTO payment (invoice, payment, createdAt, type, currency, amount,
    details)
    SELECT pay_id, pay_timestramp , pay_invoice, pay_type, pay_currency,
        pay_amount, pay_details
    FROM payments;
REPLACE INTO token (member, token, loginAt, url)
    SELECT tkn_user, tkn_id, tkn_login, ''
    FROM tokens;

-- ----------------------------------------------------------------------------
-- Borrado de tablas obsoletas
-- ----------------------------------------------------------------------------
DROP VIEW IF EXISTS suscription_login;
DROP VIEW IF EXISTS fullinvoices;
DROP VIEW IF EXISTS invoicessubtotals;
DROP VIEW IF EXISTS invoicessubtotals;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS concepts;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS suscription_users;
DROP TABLE IF EXISTS suscriptions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS exchanges;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS packages;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS tokens;
