<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
//phpinfo();
header('Content-Type: text/html; charset=utf-8');
if (isset($_GET['card_id']) && isset($_GET['card_type']) && isset($_GET['doc'])) {
    $card_id = $_GET['card_id'];
	$data['card_id'] = $_GET['card_id'];
    $card_type = $_GET['card_type'];
    $doc = $_GET['doc'];
    function CheckCurlResponse($code)
    {
        $code = (int)$code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            if ($code != 200 && $code != 204)
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }
    }

    $user = array(
        'USER_LOGIN' => 'zdravkyrort@yandex.ru',
        'USER_HASH' => 'a4ced5fd3143976bb5f758d85309de767cf7c218'
    );

    $subdomain = 'zdravkyrort';

    $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($user));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    CheckCurlResponse($code);

    $Response = json_decode($out, true);
    $Response = $Response['response'];
    if (isset($Response['auth'])) {
//        echo 'auth ok <br/>';
    } else {
//        echo 'auth fail  <br/>';
    }
if ($card_type == 'lead') {
		// Достаем данные сделки
		$link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/leads/list?id=' . $card_id;
			$curl = curl_init();

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

            $out = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            CheckCurlResponse($code);

            $Response = json_decode($out, true);
	//file_put_contents( "debug.txt", print_r($Response, true) . PHP_EOL , FILE_APPEND);
	$data['leads_number'] = $Response['response']['leads'][0]['id'];
	$manager = $Response['response']['leads'][0]['responsible_user_id'];
	foreach ($Response['response']['leads'][0]['custom_fields'] as $value) {
            if ($value['id'] == '305285') {
                $data['nomer_dogovora'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305287') {
                $data['data_dogovora'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305195') {
                $data['kolichestvo_turistov'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305337') {
                $data['cena_uslug'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305333') {
                $data['valyuta'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305091') {
                $data['turobsluzhivanie'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305093') {
                $data['infouslugi'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313621') {
                $data['dog_s_lecheniem'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305095') {
                $data['stoimost_sanatoriya'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '312591') {
                $data['dog_transfer_fraza'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305299') {
                $data['turist_dogovor_fio_pasport_propiska'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313751') {
                $data['dog_schet'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313801') {
                $data['dog_transfer_fraza_2'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '312595') {
                $data['dog_naimenovanie_obekta_razmescheniya'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '312597') {
                $data['dog_adres_obekta_razmescheniya'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305203') {
                $data['data_zaezda'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305205') {
                $data['data_vyezda'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '312599') {
                $data['dog_transfer'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313921') {
                $data['tip_nomera'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305323') {
                $data['kolichestvo_nomerov'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '312617') {
                $data['dog_chasy_zaezda_vyezda'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313885') {
                $data['pitanie'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305139') {
                $data['ekvayring'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '314777') {
                $data['dog_edet_li_turist_dogovor'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305301') {
                $data['turist_2'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305303') {
                $data['turist_3'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305305') {
                $data['turist_4'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305307') {
                $data['turist_5'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '305179') {
                $data['tip_putevki'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313433') {
                $data['sutki_dni'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '313133') {
                $data['kolichestvo_dney'] = $value['values'][0]['value'];
            }
			else if ($value['id'] == '314905') {
                $data['dog_lechenie'] = $value['values'][0]['value'];
            }
            }
// данные аккаунта
		$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/accounts/current';
		$curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        CheckCurlResponse($code);

        $Response_account = json_decode($out, true);
		//file_put_contents( "debug_account.txt", print_r($Response_account, true) . PHP_EOL , FILE_APPEND);
foreach ($Response_account['response']['account']['users'] as $val)
	{
	if ($val['id'] == $manager) {
                $data['name_manager'] = $val['name'];
            }
	}
//посылаем данные на печать
            if (!empty($data)) {
                if ($doc == 'dog1') {
					$link = "doc1.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog11') {
					$link = "doc11.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog111') {
					$link = "doc111.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog2') {
					$link = "doc2.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog21') {
					$link = "doc21.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog211') {
					$link = "doc211.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog3') {
					$link = "doc3.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog31') {
					$link = "doc31.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog311') {
					$link = "doc311.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog4') {
					$link = "doc4.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog41') {
					$link = "doc41.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }
				if ($doc == 'dog411') {
					$link = "doc411.php?";
                    foreach ($data as $key=>$value) {
                        $link.=$key .'=' . $value . "&";
                    }
                }

                $link = substr($link, 0, -1);
				$link = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($link));// удаление символа перевода строки/возврата каретки
                header("location: ". $link);
            }
}		
			else {
            echo "<h2>Для формирования документа выберите сделку</h2>";
            exit();
        }
} 