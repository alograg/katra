SELECT DATE_FORMAT(NOW(), '%Y-%m-%d') as creationOn, account.account,
    subscription.package, 
    CONCAT(package.name, ': ',package.description) AS descriotion,
    exchange.rate * package.units AS price
FROM subscription
LEFT JOIN account USING(account)
LEFT JOIN package USING(package)
LEFT JOIN exchange ON account.currency = exchange.currency
WHERE subscription.status = 'active'
    AND DATE_FORMAT(DATE_ADD(subscription.createdOn, INTERVAL 1 MONTH), '%Y%m')
        <= DATE_FORMAT(NOW(), '%Y%m')
    AND subscription.account NOT IN (
        SELECT invoice.account
        FROM invoice
        WHERE DATE_FORMAT(invoice.createdOn, '%Y%m') = DATE_FORMAT(NOW(), '%Y%m'));
