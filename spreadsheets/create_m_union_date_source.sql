CREATE OR REPLACE VIEW m_union_date_source AS
SELECT DISTINCT
       `date`                       AS `date`,
       `Site`                       AS `Site`,
       CAST(`channel`    AS CHAR)   AS `source`,
       CAST(`campaignId` AS CHAR)   AS `campaign_id`
FROM m_ad_costs_by_month
UNION
SELECT DISTINCT `year_month` AS `date`,
       `Site`,
       `source`,
       `campaign_id`
FROM m_target_lead_by_month

UNION

SELECT DISTINCT `date`, `Site`, `source`, `campaign_id` FROM m_prepay_by_month
UNION
SELECT DISTINCT `date`, `Site`, `source`, `campaign_id` FROM m_allpay_by_month
UNION
SELECT DISTINCT `date`, `Site`, `source`, `campaign_id` FROM m_returns
UNION
SELECT DISTINCT `date`, `Site`, `source`, `campaign_id` FROM m_returns2;
