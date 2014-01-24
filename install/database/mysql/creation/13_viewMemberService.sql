-- ----------------------------------------------------------------------------
-- View viewMeberService
-- v2.1.0
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW viewMemberService AS
    SELECT viewSubscriptionMember.subscription, viewSubscriptionMember.member,
        viewSubscriptionMember.level, viewSubscriptionMember.email,
        viewSubscriptionMember.package, viewSubscriptionMember.account,
        viewSubscriptionMember.status, viewSubscriptionMember.createdOn,
        viewSubscriptionMember.nick, viewSubscriptionMember.password,
        viewSubscriptionMember.fullName, viewSubscriptionMember.language,
        package.service, package.name as packageName, package.description as packageDescription,
        service.name as serviceName, service.status as serviceSatatus
    FROM viewSubscriptionMember
        LEFT JOIN package USING(package)
        LEFT JOIN service USING(service);
