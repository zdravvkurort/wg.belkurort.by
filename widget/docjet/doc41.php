<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
	
	$card_id =  (isset($_GET['card_id']))?$_GET['card_id']:"";
	$turist_5 =  (isset($_GET['turist_5']))?$_GET['turist_5']:"";
	$turist_4 =  (isset($_GET['turist_4']))?$_GET['turist_4']:"";
	$turist_3 =  (isset($_GET['turist_3']))?$_GET['turist_3']:"";
	$turist_2 =  (isset($_GET['turist_2']))?$_GET['turist_2']:"";
	$dog_edet_li_turist_dogovor =  (isset($_GET['dog_edet_li_turist_dogovor']))?$_GET['dog_edet_li_turist_dogovor']:"";
	$ekvayring =  (isset($_GET['ekvayring']))?$_GET['ekvayring']:"";
    $pitanie =(isset($_GET['pitanie']))?$_GET['pitanie']:"";
    $dog_chasy_zaezda_vyezda = (isset($_GET['dog_chasy_zaezda_vyezda']))?$_GET['dog_chasy_zaezda_vyezda']:"";
    $kolichestvo_nomerov = (isset($_GET['kolichestvo_nomerov']))?$_GET['kolichestvo_nomerov']:"";
    $tip_nomera =  (isset($_GET['tip_nomera']))?$_GET['tip_nomera']:"";
    $dog_transfer =  (isset($_GET['dog_transfer']))?$_GET['dog_transfer']:"";
    $data_vyezda =  (isset($_GET['data_vyezda']))?$_GET['data_vyezda']:"";
	$data_zaezda =  (isset($_GET['data_zaezda']))?$_GET['data_zaezda']:"";
    $dog_adres_obekta_razmescheniya =  (isset($_GET['dog_adres_obekta_razmescheniya']))?$_GET['dog_adres_obekta_razmescheniya']:"";
	$dog_naimenovanie_obekta_razmescheniya = (isset($_GET['dog_naimenovanie_obekta_razmescheniya']))?$_GET['dog_naimenovanie_obekta_razmescheniya']:"";
    $dog_transfer_fraza_2 = (isset($_GET['dog_transfer_fraza_2']))?$_GET['dog_transfer_fraza_2']:"";
	$dog_schet = (isset($_GET['dog_schet']))?$_GET['dog_schet']:"";
    $turist_dogovor_fio_pasport_propiska =  (isset($_GET['turist_dogovor_fio_pasport_propiska']))?$_GET['turist_dogovor_fio_pasport_propiska']:"";
	$dog_transfer_fraza = (isset($_GET['dog_transfer_fraza']))?$_GET['dog_transfer_fraza']:"";
	$stoimost_sanatoriya =  (isset($_GET['stoimost_sanatoriya']))?$_GET['stoimost_sanatoriya']:"";
	$dog_s_lecheniem =  (isset($_GET['dog_s_lecheniem']))?$_GET['dog_s_lecheniem']:"";
	$infouslugi =  (isset($_GET['infouslugi']))?$_GET['infouslugi']:"";
	$turobsluzhivanie =  (isset($_GET['turobsluzhivanie']))?$_GET['turobsluzhivanie']:"";
	$valyuta =  (isset($_GET['valyuta']))?$_GET['valyuta']:"";
	$cena_uslug =  (isset($_GET['cena_uslug']))?$_GET['cena_uslug']:"";
	$kolichestvo_turistov =  (isset($_GET['kolichestvo_turistov']))?$_GET['kolichestvo_turistov']:"";
	$data_dogovora =  (isset($_GET['data_dogovora']))?$_GET['data_dogovora']:"";
	$nomer_dogovora =  (isset($_GET['nomer_dogovora']))?$_GET['nomer_dogovora']:"";
	$tip_putevki =  (isset($_GET['tip_putevki']))?$_GET['tip_putevki']:"";
	$sutki_dni =  (isset($_GET['sutki_dni']))?$_GET['sutki_dni']:"";
	$kolichestvo_dney =  (isset($_GET['kolichestvo_dney']))?$_GET['kolichestvo_dney']:"";
	$name_manager =  (isset($_GET['name_manager']))?$_GET['name_manager']:"";
	
	$temp_name = explode(" ",$name_manager);
	$manager_insert = $temp_name[0]." ".substr($temp_name[1], 0, 2);

//require_once 'functions.php';	
require_once 'PHPWordlib/PHPWord.php';
$PHPWord = new PHPWord();
$document = $PHPWord->loadTemplate('templates/null_template.docx'); //шаблон

$document->setValue('today', date("d.m.Y"));
$document->setValue('dog_naimenovanie_obekta_razmescheniya', $dog_naimenovanie_obekta_razmescheniya);
$document->setValue('data_vyezda', date("d.m.Y",strtotime($data_vyezda)));
$document->setValue('data_zaezda', date("d.m.Y",strtotime($data_zaezda)));
$document->setValue('kolichestvo_dney', $kolichestvo_dney);
$document->setValue('sutki_dni', $sutki_dni);
$document->setValue('tip_putevki', $tip_putevki);
$document->setValue('kolichestvo_nomerov', $kolichestvo_nomerov);
$document->setValue('tip_nomera', $tip_nomera);
$document->setValue('turist_5', $turist_5);
$document->setValue('turist_4', $turist_4);
$document->setValue('turist_3', $turist_3);
$document->setValue('turist_2', $turist_2);
$document->setValue('dog_edet_li_turist_dogovor', $dog_edet_li_turist_dogovor);
$document->setValue('name_manager', $manager_insert);

$fio = explode(",",$turist_dogovor_fio_pasport_propiska);

// Далее отправляем файл в браузер
if (!file_exists($card_id)) {
    mkdir($card_id, 0777, true);
	}
$date = date('d-m-Y');
$time = date('H:i:s');
$document ->save($card_id.'/'.$date.' '.$time.' Заявка на аннулирование '.$fio[0].'.docx');

require_once 'ilovepdf/init.php';
$ilovepdf = new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
// Create a new task
$myTaskConvertOffice = $ilovepdf->newTask('officepdf');
// Add files to task for upload
$file1 = $myTaskConvertOffice->addFile($card_id.'/'.$date.' '.$time.' Заявка на аннулирование '.$fio[0].'.docx');
// Execute the task
$myTaskConvertOffice->execute();
// Download the package files
$myTaskConvertOffice->download($card_id);

header("Content-Type: text/html; charset=utf-8");
		header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$card_id.'/'.$date.' '.$time.' Заявка на аннулирование '.$fio[0].'.pdf');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
		header('Content-Length: ' . filesize($card_id.'/'.$date.' '.$time.' Заявка на аннулирование '.$fio[0].'.pdf'));
        flush();
		readfile($card_id.'/'.$date.' '.$time.' Заявка на аннулирование '.$fio[0].'.pdf');

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
// примечание к сделке
$fio_insert = str_replace(" ", "%20", $fio[0]);
$notes['request']['notes']['add']=array(
 array(
    'element_id'=>$card_id,
    'element_type'=>2,
    'note_type'=>4,
    'text'=>'Сформирована Заявка на аннулирование на скачивание: http://wg.belkurort.by/widget/docjet/'.$card_id.'/'.$date.'%20'.$time.'%20Заявка%20на%20аннулирование%20'.$fio_insert.'.pdf',
  ),
);

$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/notes/set';

$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
#Устанавливаем необходимые опции для сеанса cURL
curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
curl_setopt($curl,CURLOPT_URL,$link);
curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($notes));
curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
curl_setopt($curl,CURLOPT_HEADER,false);
curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
 
$out = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
CheckCurlResponse($code);
//$Response_notes = json_decode($out, true);
unlink($card_id.'/'.$date.' '.$time.' Заявка на аннулирование '.$fio[0].'.docx');