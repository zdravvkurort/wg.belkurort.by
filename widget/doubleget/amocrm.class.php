<?php

class amocrm {	
	
	var $login = '';
	var $password = '';
	var $curl = null;
	var $domain = '';	
	
	function __construct($login, $password, $domain) {
		$this->login = $login;
		$this->password = $password;
		$this->domain = $domain;
		
		$this->curl = new curl ();
		$this->curl->set_cookiefile(ROOT_DIR .'');
	}
	
	function Auth() {
		$response = $this->curl->get('https://www.amocrm.ru');
		
		if (!$this->IsLogged($response, $this->login)) {
			$params = array ('username' => $this->login, 'password' => $this->password, 'csrf_token' => '');

			if ($response) {
				if (preg_match('/name="csrf_token"\svalue="(.*?)"/im', $response, $matches)) {
					$params['csrf_token'] = end($matches);
					
					$response = $this->curl->post('https://www.amocrm.ru/oauth2/authorize', http_build_query($params));
				}
			}
		}
		
		return ($this->IsLogged($response, $this->login))? true : false ;
	}
	
	function DeleteContacts($ids) {			
		if ($this->Auth()) {
			if ($ids) {
				$response = json_decode($this->curl->post('https://' . $this->domain . '.amocrm.ru/ajax/contacts/multiple/delete/', http_build_query(array('ID' => $ids))));
				
				if (strpos($response->status, "success") !== false) {
					$this->ShowMessage('Contacts with identifiers: [' . join(',', $ids) . '] were removed');
				}
			} else {
				$this->ShowMessage('Array of contacts not be empty');
			}
		} else {
			$this->ShowMessage('Authorisation error');
		}
	}
	
	function DeleteLeads($ids) {			
		if ($this->Auth()) {
			if ($ids) {
				$response = json_decode($this->curl->post('https://' . $this->domain . '.amocrm.ru/ajax/leads/multiple/delete/', http_build_query(array('ID' => $ids))));
				
				if (strpos($response->status, "success") !== false) {
					$this->ShowMessage('Leads with identifiers: [' . join(',', $ids) . '] were removed');
				}
			} else {
				$this->ShowMessage('Array of Leads not be empty');
			}
		} else {
			$this->ShowMessage('Authorisation error');
		}
	}
	
		function MergeDublicate($query) {			
		if ($this->Auth()) {
			if ($query) {
					//print_r($query);
					$response = json_decode($this->curl->post('https://' . $this->domain . '.amocrm.ru/ajax/merge/contacts/save', $query));
				//vardump($response);
				}
			} else {
				$this->ShowMessage('Authorisation error');
			}
			
		}
	
	function IsLogged($data, $login) {
		if (strpos($data, "{\"token_type\":\"Bearer\"") !== false || strpos($data, "login: '$login'") !== false ) {
			return true;
		}
		return false;
	}
	
	function ShowMessage($error) {
		echo "$error\n";
	}
}
?>