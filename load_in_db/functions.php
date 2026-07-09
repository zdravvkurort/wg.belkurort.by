<?php
				function set_costs_in_db($responseBody, $channel) {
					global $db;
					foreach($responseBody as $data) {
						$stmt = $db->query("SELECT COUNT(*) FROM `ad_costs` WHERE Date = '".$data["Date"]."' and Channel = '".$channel."'"); 
						$callback = $stmt->fetchAll()[0];
						if($callback["COUNT(*)"] == 0) {
							$db->query("INSERT INTO ad_costs SET Date='".$data["Date"]."',
										Channel='".$channel."',
										Impressions='".$data["Impressions"]."',
										Clicks='".$data["Clicks"]."',
										Cost='".$data["Cost"]."'");
							$insertId=$db->lastInsertId();
						} else {
							$sql = "UPDATE ad_costs SET 
									Impressions='".$data["Impressions"]."',
									Clicks='".$data["Clicks"]."',
									Cost='".$data["Cost"]."'
									WHERE Date='".$data["Date"]."' and Channel='".$channel."'";
							$stmt = $db->prepare($sql);
							$stmt->execute();
						}
					}
				}
				
				function m_set_costs_in_db($responseBody, $channel) {
					global $db;
					foreach($responseBody as $data) {
						$stmt = $db->query("SELECT COUNT(*) FROM `m_ad_costs` WHERE `Date` = '".$data["Date"]."' and `Channel` = '".$channel."' and `Site`='".$data["Site"]."' and `CampaignId`='".$data["CampaignId"]."'"); 
						$callback = $stmt->fetchAll()[0];
						if($callback["COUNT(*)"] == 0) {
							$db->query("INSERT INTO m_ad_costs SET 
										Date='".$data["Date"]."',
										Channel='".$channel."',
										CampaignId='".$data["CampaignId"]."',
										CampaignName='".$data["CampaignName"]."',
										Site='".$data["Site"]."',
										Impressions='".$data["Impressions"]."',
										Clicks='".$data["Clicks"]."',
										Cost='".$data["Cost"]."'");
							$insertId=$db->lastInsertId();
						} else {
							$sql = "UPDATE m_ad_costs SET 
									Impressions='".$data["Impressions"]."',
									Clicks='".$data["Clicks"]."',
									Cost='".$data["Cost"]."'
									WHERE Date='".$data["Date"]."' 
									and Channel='".$channel."' 
									and CampaignId='".$data["CampaignId"]."' 
									and Site='".$data["Site"]."'";
							$stmt = $db->prepare($sql);
							$stmt->execute();
						}
					}
				}
				
				function koefCalc($rk, $koef = [['from' => '0000-00-00', "koef" => 1]]) {
					foreach($rk as $item => $value) {
						$koefficient = $koef[0]['koef'];
						foreach($koef as $k) {
							if(strtotime($k['from']) <= strtotime($rk[$item]["Date"])) {
								$koefficient = $k["koef"];
							} else {
								break;
							}
						}
						$rk[$item]['Cost'] = $rk[$item]['Cost']*$koefficient;
					}
					return $rk;
				}	

				function change_attr($arr,$it,$th) {
					if(isset($arr[$it])) {
						$arr[$th] = $arr[$it];
						unset($arr[$it]);
					} else {
						$arr[$th] = '0';
					}
					return $arr;
				}
				function find_currency($currency, $tdate) {
					if(count($currency)>0) {
						foreach($currency as $cur) {
							if(strtotime($tdate) == strtotime($cur["Date"])) {
								return $cur["Cur_OfficialRate"];
							}
						}
					}
					return null;
				};
?>