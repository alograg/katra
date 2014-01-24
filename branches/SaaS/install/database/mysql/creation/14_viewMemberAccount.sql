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
