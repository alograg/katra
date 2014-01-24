SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';
DROP TABLE IF EXISTS tokens;
CREATE TABLE IF NOT EXISTS tokens (
	tkn_user INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador de usuario',
	tkn_login TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Momento de ultimo login',
	tkn_id CHAR(32) DEFAULT NULL COMMENT 'El token',
	PRIMARY KEY (tkn_user),
	KEY tokenID (tkn_id)
)
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Contiene las conecciones activas';
DROP TABLE IF EXISTS services ;
CREATE TABLE IF NOT EXISTS services (
	ser_id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT COMMENT 'Identificador autonumerico del servicio',
	ser_name VARCHAR(250) NULL DEFAULT NULL COMMENT 'Nombre del servicio',
	ser_sqlcreation TEXT NULL COMMENT 'Contiene el SQL de creacion de tablas necesarias para el funcionamiento del servicio.',
	ser_status ENUM('off','private beta','public beta','release') NULL DEFAULT 'off' COMMENT 'Estado de la aplicacion.',
	PRIMARY KEY (ser_id),
	INDEX status (ser_status ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Servicios disponibles por SaaS';
DROP TABLE IF EXISTS packages;
CREATE  TABLE IF NOT EXISTS packages (
	pac_id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT COMMENT 'Identificador autonumerico del paquete.',
	pac_service INT UNSIGNED NULL DEFAULT 0 COMMENT 'Identificador del servicio',
	pac_name VARCHAR(250) NULL DEFAULT NULL COMMENT 'Nombre',
	pac_description TINYTEXT NULL DEFAULT NULL COMMENT 'Descripcion',
	pac_units DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Unidades para el calculo de precio segun moneda.',
	pac_configuration TINYTEXT NULL DEFAULT NULL COMMENT 'Configuracion para el paquete.',
	pac_recurrence ENUM('unic','month','year') NULL DEFAULT 'month' COMMENT 'Recurrencia del cobro.',
	PRIMARY KEY (pac_id),
	INDEX service (pac_service ASC),
	INDEX recurrence (pac_recurrence ASC),
	CONSTRAINT pac_service_constraint
		FOREIGN KEY (pac_service)
		REFERENCES services (ser_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Informacion de los paquetes disponibles.';
DROP TABLE IF EXISTS exchanges;
CREATE TABLE IF NOT EXISTS exchanges (
	exc_currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Identificador de moneda',
	exc_rate DECIMAL(12,6) NOT NULL DEFAULT '1.000000' COMMENT 'Tipo de cambio de unidad a moneta',
	exc_date DATE NULL DEFAULT NULL COMMENT 'Fecha de ultima actualizacion',
	PRIMARY KEY (exc_currency)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Intercambio de unidades de cobro.';
DROP TABLE IF EXISTS accounts;
CREATE TABLE IF NOT EXISTS accounts (
	acc_id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT COMMENT 'Identificador autonumerico de cuenta.',
	acc_name VARCHAR(250) NULL DEFAULT NULL COMMENT 'Nombre de la cuenta',
	acc_firm VARCHAR(250) NULL DEFAULT NULL COMMENT 'Nombre de la empreza',
	acc_bussiness VARCHAR(250) NULL DEFAULT NULL COMMENT 'Razon social',
	acc_street TINYTEXT NULL DEFAULT NULL COMMENT 'Calle',
	acc_outside VARCHAR(250) NULL COMMENT 'Numero exterior',
	acc_inside VARCHAR(250) NULL COMMENT 'Interior',
	acc_crosses TEXT NULL COMMENT 'Cruces',
	acc_zip CHAR(5) NULL DEFAULT NULL COMMENT 'Codigo postal',
	acc_colony VARCHAR(250) NULL DEFAULT NULL COMMENT 'Colonia',
	acc_city VARCHAR(250) NULL DEFAULT NULL COMMENT 'Ciudad',
	acc_state VARCHAR(250) NULL DEFAULT NULL COMMENT 'Estado/Region',
	acc_country VARCHAR(250) NULL DEFAULT NULL COMMENT 'Pais',
	acc_phone VARCHAR(250) NULL DEFAULT NULL COMMENT 'Telefono(s)',
	acc_fax VARCHAR(250) NULL DEFAULT NULL COMMENT 'Fax(s)',
	acc_email VARCHAR(250) NULL DEFAULT NULL COMMENT 'Correo electronico de la cuenta administrativa.',
	acc_taxid VARCHAR(250) NULL DEFAULT NULL COMMENT 'RFC o identificador fiscal.',
	acc_status ENUM('free','active','blocked','canceled') NOT NULL DEFAULT 'free' COMMENT 'Estado de la cuenta',
	acc_currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Moneda de la cuenta.',
	acc_configuration BLOB NULL COMMENT 'Datos de configuracion de la cuenta.(encriptado)',
	PRIMARY KEY (acc_id),
	INDEX country (acc_country ASC),
	INDEX state (acc_state ASC),
	INDEX status (acc_status ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Cuentas de cliente.';
DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
	usu_id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador de usuario',
	usu_nick VARCHAR(50) NOT NULL COMMENT 'Nick de ingreso',
	usu_email VARCHAR(250) NOT NULL COMMENT 'Correo electronico del usuario',
	usu_password BLOB NOT NULL COMMENT 'Contrasena de acceso.(encriptado)',
	usu_fullname VARCHAR(250) NULL COMMENT 'Nombre completo del usuario',
	usu_language ENUM('es','en','pt') NULL DEFAULT 'es' COMMENT 'Lenguaje preferido del usuario',
	PRIMARY KEY (usu_id),
	UNIQUE INDEX nick (usu_nick ASC),
	INDEX email (usu_email ASC),
	INDEX access (usu_nick ASC, usu_password(50) ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Usuarios del sistema';
DROP TABLE IF EXISTS suscriptions;
CREATE TABLE IF NOT EXISTS suscriptions (
	sus_id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la suscripcion',
	sus_package INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador del paquete',
	sus_account INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador de la cuenta',
	sus_status ENUM('active','blocked','canceled') NOT NULL DEFAULT 'blocked' COMMENT 'Estado de la suscripcion',
	sus_creation DATE NOT NULL DEFAULT 20120101 COMMENT 'Fecha de generacion de la suscripcion',
	sus_configuration BLOB NULL COMMENT 'Configuracion a la base de datos.(encriptado)',
	PRIMARY KEY (sus_id),
	INDEX relation (sus_package, sus_account),
	INDEX status (sus_status ASC),
	INDEX packages (sus_package ASC),
	INDEX account (sus_account ASC),
	CONSTRAINT sus_packages_constraint
		FOREIGN KEY (sus_package)
		REFERENCES packages (pac_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT sus_account_constraint
		FOREIGN KEY (sus_account)
		REFERENCES accounts (acc_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Suscripciones por parte de las cuentas';
DROP TABLE IF EXISTS suscription_users;
CREATE TABLE IF NOT EXISTS suscription_users (
	ssu_suscription INT UNSIGNED NOT NULL COMMENT 'Identificador de la suscripcion.',
	ssu_user INT UNSIGNED NOT NULL COMMENT 'Identificador del usuario',
	ssu_type ENUM('admin','user','customer','contact') NOT NULL DEFAULT 'user' COMMENT 'Relacion del usuario a la cuenta.',
	ssu_email VARCHAR(250) NULL COMMENT 'Correo de contacto a la suscripcion',
	PRIMARY KEY (ssu_suscription, ssu_user),
	INDEX suscription (ssu_suscription ASC),
	INDEX userid (ssu_user ASC),
	INDEX types (ssu_type ASC),
	CONSTRAINT ssu_user_constraint
		FOREIGN KEY (ssu_user)
		REFERENCES users (usu_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT ssu_suscriptions_constraint
		FOREIGN KEY (ssu_suscription)
		REFERENCES suscriptions (sus_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Relacion de usuarios y cuentas';
DROP TABLE IF EXISTS invoices;
CREATE TABLE IF NOT EXISTS invoices (
	inv_id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT COMMENT 'Identificador autonumerico de facturacion del sistema',
	inv_creation DATE NOT NULL DEFAULT 20100101 COMMENT 'Fecha de la factura',
	inv_account INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'identificador de la suscripcion.',
	inv_status ENUM('current','paid','late','canceled') NOT NULL DEFAULT 'current' COMMENT 'Estado de la factura',
	inv_currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Moneda de los montos de los conceptos',
	inv_taxes DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Impuesto cobrables por la factura',
	inv_data TINYTEXT NULL DEFAULT NULL COMMENT 'Datos de factura fisica y/o electronica',
	PRIMARY KEY (inv_id),
	INDEX status (inv_status ASC),
	INDEX currency (inv_currency ASC),
	INDEX account (inv_account ASC),
	CONSTRAINT inv_account_constraint
		FOREIGN KEY (inv_account)
		REFERENCES accounts (acc_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Facturas a las cuentas';
DROP TABLE IF EXISTS concepts;
CREATE TABLE IF NOT EXISTS concepts (
	con_id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT COMMENT 'Identificador autonumerico del concepto',
	con_invoice INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Numero de factura del sistema',
	con_description TINYTEXT NULL DEFAULT NULL COMMENT 'Descripcion del cobro',
	con_amount INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Cantidad de elementos.',
	con_price DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Precio del elemento',
	PRIMARY KEY (con_id),
	INDEX invoice (con_invoice ASC),
	CONSTRAINT con_invoice_constraint
		FOREIGN KEY (con_invoice)
		REFERENCES invoices (inv_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Conceptos de las facturas del sistema (invoices)';
DROP TABLE IF EXISTS payments;
CREATE TABLE IF NOT EXISTS payments (
	pay_id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT COMMENT 'Identificador del intento de pago',
	pay_timestramp TIMESTAMP NOT NULL DEFAULT NOW() COMMENT 'Fecha y tiempo del intento',
	pay_invoice INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador de la factura del sistema',
	pay_type ENUM('failed','successful','manual') NOT NULL DEFAULT 'failed' COMMENT 'Resultado del intento de pago',
	pay_currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Moneda del pago',
	pay_amount DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'Monto del pago',
	pay_details TEXT NULL DEFAULT NULL COMMENT 'Detalles del intento.',
	PRIMARY KEY (pay_id),
	INDEX invoice (pay_invoice ASC),
	INDEX results (pay_type ASC),
	CONSTRAINT pay_invoice_constraint
		FOREIGN KEY (pay_invoice)
		REFERENCES invoices (inv_id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci
COMMENT = 'v1.0.0 Intentos y pagos realizados';
DROP VIEW IF EXISTS invoicessubtotals ;
DROP TABLE IF EXISTS invoicessubtotals;
CREATE OR REPLACE ALGORITHM = MERGE VIEW invoicessubtotals AS 
	SELECT con_invoice as inv_id, SUM(con_price*con_amount)+0 as inv_subtotal
	FROM concepts
	GROUP BY con_invoice
	ORDER BY con_invoice;
DROP VIEW IF EXISTS fullinvoices ;
DROP TABLE IF EXISTS fullinvoices;
CREATE OR REPLACE ALGORITHM = MERGE VIEW fullinvoices AS 
	SELECT invoices.*, inv_subtotal
	FROM invoices,invoicessubtotals
	WHERE invoices.inv_id = invoicessubtotals.inv_id;
DROP VIEW IF EXISTS suscription_login ;
DROP TABLE IF EXISTS suscription_login;
CREATE OR REPLACE ALGORITHM = MERGE VIEW suscription_login AS
	SELECT *
	FROM suscription_users
		LEFT JOIN users ON ssu_user = usu_id
		LEFT JOIN suscriptions ON ssu_suscription = sus_id
		LEFT JOIN accounts ON sus_account = acc_id
		LEFT JOIN packages ON sus_package = pac_id
		LEFT JOIN services ON pac_service = ser_id
	WHERE sus_status = 'active' AND acc_status IN ('free', 'active');
SET SQL_MODE=@OLD_SQL_MODE; 
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS; 
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS; 
