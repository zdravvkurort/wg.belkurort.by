CREATE OR REPLACE VIEW `m_union_date_source` AS
SELECT `m_target_lead_by_month`.`year_month`  AS `date`,
       `m_target_lead_by_month`.`Site`        AS `Site`,
       `m_target_lead_by_month`.`source`      AS `source`,
       `m_target_lead_by_month`.`campaign_id` AS `campaign_id`
FROM `m_target_lead_by_month`
UNION
SELECT `m_ad_costs_by_month`.`date`        AS `date`,
       `m_ad_costs_by_month`.`Site`        AS `Site`,
       `m_ad_costs_by_month`.`channel`     AS `source`,
       `m_ad_costs_by_month`.`campaignId`  AS `campaign_id`
FROM `m_ad_costs_by_month`
UNION
SELECT `m_prepay_by_month`.`date`        AS `date`,
       `m_prepay_by_month`.`Site`        AS `Site`,
       `m_prepay_by_month`.`source`      AS `source`,
       `m_prepay_by_month`.`campaign_id` AS `campaign_id`
FROM `m_prepay_by_month`
UNION
SELECT `m_allpay_by_month`.`date`        AS `date`,
       `m_allpay_by_month`.`Site`        AS `Site`,
       `m_allpay_by_month`.`source`      AS `source`,
       `m_allpay_by_month`.`campaign_id` AS `campaign_id`
FROM `m_allpay_by_month`
UNION
SELECT `m_returns`.`date`        AS `date`,
       `m_returns`.`Site`        AS `Site`,
       `m_returns`.`source`      AS `source`,
       `m_returns`.`campaign_id` AS `campaign_id`
FROM `m_returns`
ORDER BY `date`;
