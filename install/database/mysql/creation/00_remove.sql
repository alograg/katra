-- ----------------------------------------------------------------------------
-- Eliminación de las tablas y vistas de SaaS
-- v2.0.0
-- ----------------------------------------------------------------------------
DROP VIEW IF EXISTS viewAccountInvoice;
DROP VIEW IF EXISTS viewInvoiceBalance;
DROP VIEW IF EXISTS viewInvoicePayments;
DROP VIEW IF EXISTS viewInvoiceSubtotal;
DROP VIEW IF EXISTS viewMemberAccount;
DROP VIEW IF EXISTS viewMemberService;
DROP VIEW IF EXISTS viewSubscriptionMember;
DROP TABLE IF EXISTS token;
DROP TABLE IF EXISTS payment;
DROP TABLE IF EXISTS concept;
DROP TABLE IF EXISTS invoice;
DROP TABLE IF EXISTS mapSubscriptionMember;
DROP TABLE IF EXISTS subscription;
DROP TABLE IF EXISTS member;
DROP TABLE IF EXISTS account;
DROP TABLE IF EXISTS exchange;
DROP TABLE IF EXISTS package;
DROP TABLE IF EXISTS service;
