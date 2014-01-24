REPLACE INTO service (service, name, sqlCreation, status) VALUES
    (1, 'Orbet', 'orbet/creation_2.0.2', 'public beta');
REPLACE INTO package
    (package, service, name, description, units, configuration, recurrence)
    VALUES
        (1, 1, 'Orbet Beta', 'Acceso publico al beta de <strong>Orbet</strong>',
            0, '{"members":"unlimited"}','year');
REPLACE INTO exchange (currency, rate, editOn) VALUES ('MXP', 21.55109, '2012-01-01');
REPLACE INTO exchange (currency, rate, editOn) VALUES ('USD', 1.5731, '2012-01-01');
REPLACE INTO account (account, name, firm, bussiness, street, outside, inside,
    crosses, zip, colony, city, state, country, phone, fax, email, taxKey,
    status, currency, configuration)
    VALUES
        (1, 'aqua', 'Aqua Interactive', 'MSR Servicios para el desarrollo SC',
            'Tomas V. Gomez', '77', '', 'Entre Hidalgo y Justo Sierra', '44600',
            'Ladrón de Guevara', 'Guadalajara', 'Jalisco', 'MX',
            '+52 (33) 3333 4646', '+52 (33) 3333 4646',
            'henry@aquainteractive.com.mx', 'MSD110124L97', 'free', 'MXP',
            '{"database":"mysql://develop:cYLBmPwb@localhost/develop_%"}');
REPLACE INTO member (member, nick, email, password, fullname, language) VALUES
    (1, 'aquaman', 'henry@aquainteractive.com.mx', MD5('aquainteractive'),
        'Henry I. Galvez T.', 'es');
REPLACE INTO subscription (subscription, package, account, status, createdOn, configuration)
    VALUES (1, 1, 1, 'active', '2012-01-01', '');
REPLACE INTO mapSubscriptionMember (subscription, member, level, email) VALUES
    (1, 1, 'admin', 'henry@aquainteractive.com.mx');
