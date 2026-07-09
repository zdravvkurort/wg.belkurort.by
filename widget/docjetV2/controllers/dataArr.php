<?php 
	function makeData($lead) {
		$data = [];
		$data["status_id"] = $lead["status_id"];
		$data["pipeline_id"] = $lead["pipeline_id"];
		$data['card_id'] = $data['leads_number'] = $lead['id'];
		$data['nomer_dogovora'] = findcustomfieldval($lead, 305285);
		$data['data_dogovora'] = findcustomfieldval($lead, 305287);
		$data['kvota'] = (findcustomfieldval($lead, 351975)) ? findcustomfieldval($lead, 351975) : false;
		$data['kolichestvo_turistov'] = findcustomfieldval($lead, 305195);
		$data['cena_uslug'] = findcustomfieldval($lead, 305337);
		$data['valyuta'] = findcustomfieldval($lead, 305333);
		$data['turobsluzhivanie'] = findcustomfieldval($lead, 305091); 
		$data['infouslugi'] = findcustomfieldval($lead, 305093);
		$data['stoimost_sanatoriya'] = (findcustomfieldval($lead, 305095)) ? findcustomfieldval($lead, 305095) : '';
		$data['turist_dogovor_fio_pasport_propiska'] = findcustomfieldval($lead, 305299);
		$data['data_zaezda'] = findcustomfieldval($lead, 305203);
		$data['data_vyezda'] = findcustomfieldval($lead, 305205);
		$data['tip_nomera'] = findcustomfieldval($lead, 313921);
		$data['kolichestvo_nomerov'] = findcustomfieldval($lead, 305323);
		$data['pitanie'] = findcustomfieldval($lead, 313885);
		$data['ekvayring'] = findcustomfieldval($lead, 305139);
		$data['turist_2'] = findcustomfieldval($lead, 305301);
		$data['turist_3'] = findcustomfieldval($lead, 305303);
		$data['turist_4'] = findcustomfieldval($lead, 305305);
		$data['turist_5'] = findcustomfieldval($lead, 305307);
		$data['tip_putevki'] = findcustomfieldval($lead, 305179);
		$data['kolichestvo_dney'] = findcustomfieldval($lead, 313133);
		$data['primechanie_v_zayavke'] = (findcustomfieldval($lead, 327491)) ? findcustomfieldval($lead, 327491) : "";
		$data['type_oplaty'] = findcustomfieldval($lead, 305173);
		$data['mail_note'] = (findcustomfieldval($lead, 334611)) ? findcustomfieldval($lead, 334611) : "";
		$data['sum_novogod_banketa'] = (findcustomfieldval($lead, 362303)) ? findcustomfieldval($lead, 362303) : 0;
		$data['sum_novogod_program'] = (findcustomfieldval($lead, 758042)) ? findcustomfieldval($lead, 758042) : 0;
		$data['sum_novogod_utrennika'] = (findcustomfieldval($lead, 396460)) ? findcustomfieldval($lead, 396460) : 0;
		$data['sum_predopl_dlya_dogovora'] = findcustomfieldval($lead, 372377);
		$data['sum_predopl_fact'] = findcustomfieldval($lead, 305359);
		$data['data_predopl_fact'] = findcustomfieldval($lead, 305361);
		$data['sum_all_pay_fact'] = findcustomfieldval($lead, 305363);
		$data['data_all_pay_fact'] = findcustomfieldval($lead, 305367);
		$data['hypot_date_pay'] = findcustomfieldval($lead, 305339);
		$data['sanid'] = findcustomfieldvalid($lead, 305089);
		$data['idtouroperator'] = findcustomfieldvalid($lead, 339925);
		$data['primechanie_v_annul'] = findcustomfieldval($lead, 745938);
		$data['banket_list'] = findcustomfieldval($lead, 762126);
		return $data;
	}
?>