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
-- ----------------------------------------------------------------------------
-- Table package
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS package (
    package INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico del paquete',
    service INT UNSIGNED NOT NULL COMMENT 'Identificador del servicio',
    name VARCHAR(250) NOT NULL COMMENT 'Nombre',
    description TINYTEXT NOT NULL COMMENT 'Descripción',
    units DECIMAL(5,2) NOT NULL DEFAULT 1.00
        COMMENT 'Unidades para el calculo de precio',
    configuration TINYTEXT NOT NULL COMMENT 'Configuración para el paquete',
    recurrence ENUM('unic','month','year') NOT NULL DEFAULT 'month'
        COMMENT 'Recurrencia del cobro',
    PRIMARY KEY (package),
    INDEX packageService (service ASC),
    INDEX recurrence (recurrence ASC),
    CONSTRAINT pagakeServiceService
        FOREIGN KEY (service)
        REFERENCES service (service)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Información de los paquetes disponibles.';
-- ----------------------------------------------------------------------------
-- Table exchange
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS exchange (
    currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Identificador de moneda',
    rate DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT 1.000000
        COMMENT 'Tipo de cambio de unidad a moneda',
    editOn DATE NOT NULL DEFAULT '2012-01-01' COMMENT 'Fecha de ultima actualización',
    PRIMARY KEY (currency)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Intercambio de unidades de cobro';
-- ----------------------------------------------------------------------------
-- Table account
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS account (
    account INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico de cuenta',
    name VARCHAR(250) NOT NULL COMMENT 'Nombre de la cuenta',
    firm VARCHAR(250) NOT NULL COMMENT 'Nombre de la empresa',
    bussiness VARCHAR(250) NOT NULL COMMENT 'Razón social',
    street VARCHAR(250) NOT NULL COMMENT 'Calle',
    outside VARCHAR(250) NOT NULL COMMENT 'Numero exterior',
    inside VARCHAR(250) NOT NULL COMMENT 'Interior',
    crosses TEXT COMMENT 'Cruces',
    zip CHAR(5) NOT NULL COMMENT 'Código postal',
    colony VARCHAR(250) NOT NULL COMMENT 'Colonia',
    city VARCHAR(250) NOT NULL COMMENT 'Ciudad',
    state VARCHAR(250) NOT NULL COMMENT 'Estado/Region',
    country CHAR(2) NOT NULL COMMENT 'País',
    phone VARCHAR(250) NOT NULL COMMENT 'Teléfono(s)',
    fax VARCHAR(250) NOT NULL COMMENT 'Fax(s)',
    email VARCHAR(250) NOT NULL COMMENT 'Correo electrónico administrativo',
    taxKey VARCHAR(250) NOT NULL COMMENT 'RFC o identificador fiscal',
    status ENUM('free','active','blocked','canceled') NOT NULL DEFAULT 'free'
        COMMENT 'Estado de la cuenta',
    currency CHAR(3) NOT NULL COMMENT 'Moneda de la cuenta',
    configuration BLOB NOT NULL
        COMMENT 'Datos de configuración de la cuenta (encriptado)',
    PRIMARY KEY (account),
    UNIQUE INDEX name (name ASC),
    INDEX country (country ASC),
    INDEX status (status ASC),
    INDEX currency (currency ASC),
    INDEX state (state ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.1 Cuentas de cliente';
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
-- ----------------------------------------------------------------------------
-- Table subscription
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscription (
    subscription INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador de la suscripción',
    package INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador del paquete',
    account INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Identificador de la cuenta',
    status ENUM('active','blocked','canceled') NOT NULL DEFAULT 'blocked'
        COMMENT 'Estado de la suscripción',
    createdOn DATE NOT NULL DEFAULT '2012-01-01'
        COMMENT 'Fecha de generación de la suscripción',
    configuration BLOB NULL COMMENT 'Configuración a la base de datos (encriptado)',
    PRIMARY KEY (subscription),
    INDEX subscriptionPackage (package ASC),
    INDEX subscriptionAccount (account ASC),
    INDEX relation (package ASC, account ASC),
    INDEX status (status ASC),
    CONSTRAINT subscriptionPackagePackage
        FOREIGN KEY (package)
        REFERENCES package (package)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT subscriptionAccountAccount
        FOREIGN KEY (account)
        REFERENCES account (account)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Suscripciones por parte de las cuentas';
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
-- ----------------------------------------------------------------------------
-- Table invoice
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice (
    invoice INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico de facturación del sistema',
    createdOn DATE NOT NULL DEFAULT '2012-01-01' COMMENT 'Fecha de la factura',
    account INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Cuenta',
    status ENUM('current','paid','late','canceled') NOT NULL DEFAULT 'current'
        COMMENT 'Estado de la factura',
    currency CHAR(3) NOT NULL DEFAULT 'MXP'
        COMMENT 'Moneda de los montos de los conceptos',
    taxes DECIMAL(5,2) NOT NULL DEFAULT 0.00
        COMMENT 'Impuesto cobrables por la factura',
    data TINYTEXT NOT NULL COMMENT 'Datos de factura física y/o electrónica',
    PRIMARY KEY (invoice),
    INDEX invoiceAccount (account ASC),
    INDEX status (status ASC),
    INDEX currency (currency ASC),
    CONSTRAINT invoiceAccountAccount
        FOREIGN KEY (account)
        REFERENCES account (account)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Facturas a las cuentas';
-- ----------------------------------------------------------------------------
-- Table concept
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS concept (
    invoice INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Numero de factura del sistema',
    concept INT UNSIGNED NOT NULL AUTO_INCREMENT
        COMMENT 'Identificador auto numérico del concepto',
    description TINYTEXT NOT NULL COMMENT 'Descripción del cobro',
    quantity INT NOT NULL DEFAULT 1 COMMENT 'Cantidad de elementos',
    price DECIMAL(5,2) NOT NULL DEFAULT 1 COMMENT 'Precio del elemento',
    PRIMARY KEY (concept),
    INDEX conceptInvoiceInvoice (invoice ASC),
    CONSTRAINT conceptInvoiceInvoice
        FOREIGN KEY (invoice)
        REFERENCES invoice (invoice)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Conceptos de las facturas del sistema';
-- ----------------------------------------------------------------------------
-- Table payment
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment (
    invoice INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Identificador de la factura del sistema',
    payment INT UNSIGNED NOT NULL COMMENT 'Identificador del intento de pago',
    createdAt TIMESTAMP NOT NULL DEFAULT NOW()
        COMMENT 'Fecha y tiempo del intento',
    type ENUM('failed','successful','manual') NOT NULL DEFAULT 'failed'
        COMMENT 'Resultado del intento de pago',
    currency CHAR(3) NOT NULL DEFAULT 'MXP' COMMENT 'Moneda del pago',
    amount DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Monto del pago',
    details TEXT NULL COMMENT 'Detalles del intento',
    PRIMARY KEY (payment),
    INDEX paymentInvoiceInvoice (invoice ASC),
    INDEX results (type ASC),
    CONSTRAINT paymentInvoiceInvoice
        FOREIGN KEY (invoice)
        REFERENCES invoice (invoice)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_spanish2_ci
COMMENT = 'v2.0.0 Intentos y pagos realizados';
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
-- ----------------------------------------------------------------------------
-- View viewSubscriptionMember
-- v2.0.2
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewSubscriptionMember AS
    SELECT mapSubscriptionMember.subscription, mapSubscriptionMember.member,
        mapSubscriptionMember.level, mapSubscriptionMember.email,
        subscription.package, subscription.account, subscription.status,
        subscription.createdOn, member.nick,
        member.password, member.fullName, member.language
    FROM mapSubscriptionMember
        LEFT JOIN subscription USING(subscription)
        LEFT JOIN member USING(member);
-- ----------------------------------------------------------------------------
-- View viewMeberService
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewMemberService AS
    SELECT viewSubscriptionMember.subscription, viewSubscriptionMember.member,
        viewSubscriptionMember.level, viewSubscriptionMember.email,
        viewSubscriptionMember.package, viewSubscriptionMember.account,
        viewSubscriptionMember.status, viewSubscriptionMember.createdOn,
        viewSubscriptionMember.nick, viewSubscriptionMember.password,
        viewSubscriptionMember.fullName, viewSubscriptionMember.language,
        package.service, package.name as packageName,
        service.name as serviceName, service.status as serviceSatatus
    FROM viewSubscriptionMember
        LEFT JOIN package USING(package)
        LEFT JOIN service USING(service);
-- ----------------------------------------------------------------------------
-- View viewMemberAccount
-- v2.0.2
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewMemberAccount AS
    SELECT viewSubscriptionMember.subscription, viewSubscriptionMember.member,
        viewSubscriptionMember.level, viewSubscriptionMember.email,
        viewSubscriptionMember.package, viewSubscriptionMember.account,
        viewSubscriptionMember.status, viewSubscriptionMember.createdOn,
        viewSubscriptionMember.nick, viewSubscriptionMember.password, 
        viewSubscriptionMember.fullName, viewSubscriptionMember.language, 
        account.name as accountName, account.firm, account.bussiness, 
        account.street, account.outside, account.inside, account.crosses, 
        account.zip, account.colony, account.city, account.state, 
        account.country, account.phone, account.fax, account.email as accountEmail, 
        account.taxKey, account.status as accountStatus, account.currency
    FROM viewSubscriptionMember
        LEFT JOIN account USING(account);
-- ----------------------------------------------------------------------------
-- View viewInvoiceSubtotal
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoiceSubtotal AS
    SELECT concept.invoice, COUNT(concept.concept) as quantity,
        SUM(concept.quantity*concept.price) as subtotal
    FROM concept
    GROUP BY concept.invoice;
-- ----------------------------------------------------------------------------
-- View viewInvoicePayments
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoicePayment AS
    SELECT payment.invoice, payment.currency, COUNT(payment.payment) as quantity,
        SUM(IF(payment.type+0=1,1,0)) as failed,
        SUM(IF(payment.type+0>=2,1,0)) as successed,
        SUM(IF(payment.type+0>=2,payment.amount,0)) as total
    FROM payment
    GROUP BY payment.invoice, payment.currency;
-- ----------------------------------------------------------------------------
-- View viewInvoiceBalance
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewInvoiceBalance AS
    SELECT invoice.invoice, invoice.createdOn, invoice.account, invoice.status,
        invoice.currency, invoice.taxes, invoice.data,
        viewInvoiceSubtotal.quantity as quantityOfConcepts,
        viewInvoiceSubtotal.subtotal,
        viewInvoicePayment.quantity as quantityOfPayments,
        viewInvoicePayment.failed, viewInvoicePayment.successed,
        viewInvoicePayment.total,
        (viewInvoiceSubtotal.subtotal+invoice.taxes)
            - viewInvoicePayment.total > 0 as underpayment
    FROM invoice
    LEFT JOIN viewInvoiceSubtotal USING(invoice)
    LEFT JOIN viewInvoicePayment USING(invoice, currency);
-- ----------------------------------------------------------------------------
-- View viewAccountInvoice
-- v2.0.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewAccountInvoice AS
    SELECT account.name as accountName, account.firm, account.bussiness,
        account.street, account.outside, account.inside, account.crosses,
        account.zip, account.colony, account.city, account.state,
        account.country, account.phone, account.fax, account.email as accountEmail,
        account.taxKey, account.status as accountStatus, viewInvoiceBalance.invoice,
        viewInvoiceBalance.createdOn, viewInvoiceBalance.status as invoiceStatus,
        viewInvoiceBalance.currency, viewInvoiceBalance.taxes, viewInvoiceBalance.data,
        viewInvoiceBalance.quantityOfConcepts, viewInvoiceBalance.subtotal,
        viewInvoiceBalance.quantityOfPayments, viewInvoiceBalance.failed,
        viewInvoiceBalance.successed, viewInvoiceBalance.total,
        viewInvoiceBalance.underpayment
    FROM account
    LEFT JOIN viewInvoiceBalance USING(account);
