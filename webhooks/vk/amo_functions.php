<?php 

function lg($obj)
	{
		if(is_array($obj)){
			$obj = json_encode($obj);
		}
		$t = "[".date('Y-m-d H:i:s')."]: ";
		file_put_contents('message.log', $t.$obj."\n", FILE_APPEND);
	}


function normalize_phone($phone)
{
	$initial_phone = $phone;
	$phone = preg_replace("/[^0-9]/", "", $phone);
/*    $phone = intval($phone);
    $f = substr($phone, 0, 1);
    if ($f!=='7' and $f!== '8') return $initial_phone;        
    $phone = substr($phone, 1);
    $phone = "7".$phone;
    if(strlen($phone) !== 11){
        return $initial_phone;
    }*/
    return $phone;
}



function create_contact($name, $phone, $email, $responsible_id, $utm_data=[], $vk = "")
	{
		global $amo;
		$contact = $amo->contact;
		// $contact->debug(true); 
	    $contact['name'] = $name;
	    $contact['responsible_user_id'] = $responsible_id;
	    $contact->addCustomField(183781, [
	        [$phone, 'WORK'],
	    ]);

	    //TODO

	    $contact->addCustomField(183783, [
	        [$email, 'WORK'],
	    ]);
		
		$contact->addCustomField(376083, $vk);
	    

	    $amo_id = $contact->apiAdd();
	    return $amo_id;
	}

	function create_lead($phone, $responsible_id, $pipeline_id=0, $utm_data=[], $form_name, $roistat, $guid)
	{
		global $amo;
		$lead = $amo->lead;
	    $lead['name'] = 'Новый лид из ВК';
	    $lead['responsible_user_id'] = $responsible_id;
	    $lead['pipeline_id'] = $pipeline_id;
		$lead['status_id'] = 26081347;

	    $lead['tags'] = ["Vk","Лидформа"];
		
		$lead->addCustomField(305083, 487489);
		$lead->addCustomField(305085, 446095);
/*
	    if(isset($utm_data['utm_source'])) $lead->addCustomField(306047, $utm_data['utm_source']);
	    if(isset($utm_data['utm_campaign'])) $lead->addCustomField(306049, $utm_data['utm_campaign']);
	    if(isset($utm_data['utm_term'])) $lead->addCustomField(306051, $utm_data['utm_term']);
	    if(isset($utm_data['utm_medium'])) $lead->addCustomField(306053, $utm_data['utm_medium']);
	    if(isset($utm_data['utm_content'])) $lead->addCustomField(306055, $utm_data['utm_content']);
*/
	    $lead_id = $lead->apiAdd();
	    return $lead_id;
	}
	function connect_lead_to_contact($lead_id, $contact_id)
	{
		global $amo;
		$link = $amo->links;
	    $link['from'] = 'leads';
	    $link['from_id'] = $lead_id;
	    $link['to'] = 'contacts';
	    $link['to_id'] = $contact_id;
	    $link->apiLink();
	}

	function is_contact_exists($phone, $email)
	{
		global $amo;
		$exists = false;
		//check if exists
		//от
			if(iconv_strlen($phone)<10) {
				$tfr = 0;
			} else {
				$tfr = iconv_strlen($phone)-10;
			}
		//Проверяем есть ли контакт
		$phone_exists = false;
		$phone = preg_replace("/[^0-9]/", "", $phone);
		$p = (strlen($phone)>10) ? substr($phone, (strlen($phone) - 10), strlen($phone)-1):$phone;
		if(strlen($phone)>3){
			$phone_users = $amo->contact->apiList([
			        'query' => substr($p,$tfr,iconv_strlen($phone)),
			 ]);
			if(count($phone_users)>0){
				$phone_exists = true;
			}
		}

		$email_exists = false;
		if(strlen($email)>3){
			$email_users = $amo->contact->apiList([
			        'query' => $email,
			]);
			if(count($email_users)>0){
				$email_exists = true;
			}
		}


		//Если нашли
		$responsible_id = false;
		$amo_id = false;
		$user = false;

		if($phone_exists){
		  $exists = true;
		  $amo_id = $phone_users[0]['id'];
		  $responsible_id = $phone_users[0]['responsible_user_id'];
		  $user =$phone_users[0];
		}


		if($email_exists){
		   $exists = true;
		   $amo_id = $email_users[0]['id'];
		   $responsible_id = $email_users[0]['responsible_user_id'];
		   $user =$email_users[0];
		}

		
		if($exists){
		//	lg('fill');
		//	lg($user);
			fill_contact($user, $phone, $email);
		}
		return [$exists, $amo_id, $responsible_id];
	}
	function fill_contact($user, $phone, $email)
	{
		//TODO
		$fields = $user['custom_fields'];
		$phone_exists = false;
		$email_exists = false;
		$phones = [$phone];
		$emails = [$email];
		foreach ($fields as $field) {
			if($field['code']=="PHONE"){
				$values = $field['values'];
				foreach ($values as $value) {
					if( preg_replace("/[^0-9]/", '', $value['value'])==$phone) $phone_exists = true;
					$phones[] = preg_replace("/[^0-9]/", '', $value['value']);
				}
			}
			if($field['code']=="EMAIL"){
				$values = $field['values'];
				foreach ($values as $value) {
					if( $value['value']==$email) $email_exists = true;
					$emails[] = $value['value'];
				}
			}
		}

		global $amo;
		$contact = $amo->contact;

	
		if(!$phone_exists){
			$phone_data  = [];
	    	foreach ($phones as $p) {
	    		$phone_data[] = [$p, 'WORK'];
	    	}
			$contact->addCustomField(183781, $phone_data);
		}
		if(!$email_exists){
			$email_data  = [];
	    	foreach ($emails as $p) {
	    		$email_data[] = [$p, 'WORK'];
	    	}
			$contact->addCustomField(183783, $email_data);
		}

		if(!$phone_exists or !$email_exists) $contact->apiUpdate((int)$user['id']);

		return true;	
	}


	function is_lead_exists($contact_id, $pipeline_id=false)
	{
		global $amo;
		$lead_id = false;
		$links = $amo->links->apiList([
	        'from' => 'contacts',
	        'from_id' => $contact_id,
	        'to' => 'leads',
    	]);

    	$lead_ids = [];
    	foreach ($links as $link) {
    		$lead_ids[] = $link['to_id'];
    	}
    	
    	if(count($lead_ids)==0) return [false, $lead_id];

    	$leads = $amo->lead->apiList([
        	'id' => $lead_ids,
    	]);

    	foreach ($leads as $lead) {
    		if($pipeline_id==false or $lead['pipeline_id']==$pipeline_id) 
    			if($lead['status_id']!=142 and $lead['status_id']!=143)
    			return [true, $lead['id']];
    	}
    	

		return [false, $lead_id];
	}

	function create_note($lead_id, $text)
	{
		global $amo;
		$note = $amo->note;
		$note['element_id'] = $lead_id;
		$note['element_type'] = \AmoCRM\Models\Note::TYPE_LEAD;
	    $note['note_type'] = \AmoCRM\Models\Note::COMMON; // @see https://developers.amocrm.ru/rest_api/notes_type.php
	    $note['text'] = $text;
	    $id = $note->apiAdd();
	}
	function create_task($lead_id, $responsible_id)
	{
			global $amo;
			$task = $amo->task;
	    	$task['element_id'] = $lead_id;
		    $task['element_type'] = 2;
		    $task['task_type'] = 1;
		    $task['responsible_user_id'] = $responsible_id;
		    $task['text'] = "Связаться с клиентом";
		    $task['complete_till'] = '+10 MINUTES';
		    $id = $task->apiAdd();
	}
	


	 ?>