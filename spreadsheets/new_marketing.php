<?php 
require_once "../db_login.php";
require_once "../functions.php";

// shell_exec('php ../load_in_db/m_yadir.php');
// shell_exec('php ../load_in_db/m_vk.php');
// shell_exec('php ../load_in_db/fb_costs2_in_db.php');

$daterange = json_decode($_REQUEST["range"],true);
$startDate = $_REQUEST["startDate"];
$endDate = $_REQUEST["endDate"];

$stmt = $db->query('SELECT `id`,`name` FROM `users`');
$managerList = $stmt->fetchAll();

$stmt = $db->query('SELECT `CampaignId`,`CampaignName` FROM `m_ad_costs` group by `CampaignId`');
$campaign = $stmt->fetchAll();

$stmt = $db->query('
SELECT
    m_union_date_source.date AS "date",
    m_union_date_source.Site AS "site",
    m_union_date_source.source AS "source",
    m_union_date_source.campaign_id AS "CampaignName",
    sum(m_ad_costs_by_month.cost) AS "cost",
    sum(m_ad_costs_by_month.clicks) AS "clicks",
    sum(m_target_lead_by_month.count_target_lead) AS "count_leads",
    sum(m_prepay_by_month.countprepay) AS "countprepay",
    sum(m_allpay_by_month.countallpay) AS "countallpay",
    sum(m_allpay_by_month.sumallpay) AS "sumallpay",
    sum(m_prepay_by_month.sumprepay) AS "sumprepay",
	sum(m_returns.countreturn) as "countreturn",
	sum(m_returns.sumreturn) as "sumreturn",
	sum(m_returns2.countreturn) as "countreturn2",
	sum(m_returns2.sumreturn) as "sumreturn2"
FROM
    m_union_date_source
LEFT OUTER JOIN m_ad_costs_by_month 
	ON m_union_date_source.date = m_ad_costs_by_month.date 
    	AND m_union_date_source.Site = m_ad_costs_by_month.Site
        AND m_union_date_source.source = m_ad_costs_by_month.channel
        AND m_union_date_source.campaign_id = m_ad_costs_by_month.campaignId
LEFT OUTER JOIN m_target_lead_by_month 
	ON m_union_date_source.date = m_target_lead_by_month.year_month 
    	AND m_union_date_source.Site = m_target_lead_by_month.Site
        AND m_union_date_source.source = m_target_lead_by_month.source
        AND m_union_date_source.campaign_id = m_target_lead_by_month.campaign_id
LEFT OUTER JOIN m_prepay_by_month 
	ON m_union_date_source.date = m_prepay_by_month.date 
    	AND m_union_date_source.Site = m_prepay_by_month.Site
        AND m_union_date_source.source = m_prepay_by_month.source
        AND m_union_date_source.campaign_id = m_prepay_by_month.campaign_id
LEFT OUTER JOIN m_allpay_by_month 
	ON m_union_date_source.date = m_allpay_by_month.date 
    	AND m_union_date_source.Site = m_allpay_by_month.Site
        AND m_union_date_source.source = m_allpay_by_month.source
        AND m_union_date_source.campaign_id = m_allpay_by_month.campaign_id
LEFT OUTER JOIN m_returns
	ON m_union_date_source.date = m_returns.date 
    	AND m_union_date_source.Site = m_returns.Site
        AND m_union_date_source.source = m_returns.source
        AND m_union_date_source.campaign_id = m_returns.campaign_id
LEFT OUTER JOIN m_returns2
	ON m_union_date_source.date = m_returns2.date 
    	AND m_union_date_source.Site = m_returns2.Site
        AND m_union_date_source.source = m_returns2.source
        AND m_union_date_source.campaign_id = m_returns2.campaign_id

WHERE
	UNIX_TIMESTAMP(STR_TO_DATE(m_union_date_source.`date`,"%d.%m.%Y")) >= UNIX_TIMESTAMP(STR_TO_DATE('.$startDate.',"%Y-%m-%d")) and 
	UNIX_TIMESTAMP(STR_TO_DATE(m_union_date_source.`date`,"%d.%m.%Y")) <= UNIX_TIMESTAMP(STR_TO_DATE('.$endDate.',"%Y-%m-%d"))

GROUP BY m_union_date_source.date, m_union_date_source.Site, m_union_date_source.source, m_union_date_source.campaign_id
ORDER BY m_union_date_source.date');
$leadslist = $stmt->fetchAll();
$outputleadslist = [];
$detail_list = [];

foreach($leadslist as $lead) {
			$date = "";
			if(count($daterange) > 0) {
				foreach($daterange as $date_set) {
					if(strtotime($lead["date"]) >= strtotime($date_set[0]) and strtotime($lead["date"]) <= strtotime($date_set[1])) {
						$date = date("m.Y",strtotime($date_set[2]));
					}
				}
			}
			if($date == "") {
				$date = date("m.Y",strtotime($lead["date"]));
			}
			
			array_push($outputleadslist, 
			array(
			$date,
			$lead["site"],
			$lead["source"],
			findCampName($lead["CampaignName"]),
			($lead["cost"] == 0) ? "" : str_replace('.',',',$lead["cost"]),
			($lead["clicks"] == 0) ? "" : str_replace('.',',',$lead["clicks"]),
			($lead["count_leads"] == 0) ? "" : str_replace('.',',',$lead["count_leads"]),
			str_replace('.',',',$lead["countprepay"] + $lead["countallpay"]),
			str_replace('.',',',$lead["sumprepay"] + $lead["sumallpay"]),
			str_replace('.',',',$lead["countreturn"] + $lead["countreturn2"]),
			str_replace('.',',',$lead["sumreturn"] + $lead["sumreturn2"])
			));
}

foreach($leadslist as $lead) {
			$date = "";
			$date = date("d.m.Y",strtotime($lead["date"]));
			array_push($detail_list, 
			array(
			$date,
			$lead["site"],
			$lead["source"],
			findCampName($lead["CampaignName"]),
			($lead["cost"] == 0) ? "" : str_replace('.',',',$lead["cost"]),
			($lead["clicks"] == 0) ? "" : str_replace('.',',',$lead["clicks"]),
			($lead["count_leads"] == 0) ? "" : str_replace('.',',',$lead["count_leads"]),
			str_replace('.',',',$lead["countprepay"] + $lead["countallpay"]),
			str_replace('.',',',$lead["sumprepay"] + $lead["sumallpay"])
			));
}
$outarr = [$outputleadslist];

print_r(json_encode($outarr));

function findCampName($camp_id) {
	global $campaign;
	foreach($campaign as $item) {
		if($item["CampaignId"] == $camp_id) {
			return($item["CampaignName"]);
		}
	}
}
?>