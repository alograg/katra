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
