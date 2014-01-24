REPLACE INTO service (service, name, sqlCreation, status) VALUES
    (1, 'Orbet', '2.0.2', 'public beta');
REPLACE INTO package
    (package, service, name, description, units, configuration, recurrence)
    VALUES
        (1, 1, 'Orbet Beta', 'Acceso publico al beta de <strong>Orbet</strong>',
            0, '{"members":"unlimited"}','year');
REPLACE INTO exchange (currency, rate, editOn) VALUES ('MXP', 21.55109, '2012-01-01');
REPLACE INTO exchange (currency, rate, editOn) VALUES ('USD', 1.5731, '2012-01-01');
