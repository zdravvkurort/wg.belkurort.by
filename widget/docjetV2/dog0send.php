<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
	$temp_name = explode(" ",$data['name_manager']);
	$otchestvo = (isset($temp_name[2])) ? substr($temp_name[2], 0, 2)."." : "";
	$manager_insert = $temp_name[0]." ".substr($temp_name[1], 0, 2).". ".$otchestvo;
 
require_once 'src/autoload.php';

do {
	$document = (date("m",strtotime($data['data_vyezda'])) < date("m",strtotime($data['data_zaezda']))) ? new PhpOffice\PhpWord\TemplateProcessor('templatesV2/booking_adding_info_with_banket.docx') : new PhpOffice\PhpWord\TemplateProcessor('templatesV2/booking_adding_info.docx'); //шаблон

	$document->setValue('today', date("d.m.Y")); //
	$document->setValue('dog_naimenovanie_obekta_razmescheniya', $data['dog_naimenovanie_obekta_razmescheniya']);//
	$document->setValue('data_vyezda', date("d.m.Y",strtotime($data['data_vyezda'])));//
	$document->setValue('data_zaezda', date("d.m.Y",strtotime($data['data_zaezda'])));//
	$document->setValue('kolichestvo_dney', $data['kolichestvo_dney']);//
	$document->setValue('sutki_dni', $data['sutki_dni']);//
	$document->setValue('kolichestvo_nomerov', $data['kolichestvo_nomerov']);//
	$document->setValue('tip_nomera', $data['tip_nomera']);//
	$document->setValue('name_manager', $manager_insert);//
	$document->setValue('numrequest', $data['numrequest']);//
	$document->setValue('primechanie_v_zayavke', $data['primechanie_v_zayavke']);
	$document->setValue('book_type', $data['book_type']);
	
	$document->setValue('dolzhnost', $data['dolzhnost']);//
	$document->setImageValue('sign', array('path' => 'sign/'.$data['sign'].'.png', 'width' => 150, 'height' => 150, 'ratio' => true));//
	
	$document->cloneRowAndSetValues('id', $data['prilozhenie']);

$fio = explode(",",$data['dog_edet_li_turist_dogovor']);

// Далее отправляем файл в браузер
if (!file_exists("docs/".$card_id)) {
    mkdir("docs/".$card_id, 0777, true);
	}

$date = date('d-m-Y');
$time = date('H:i:s');
$doc = 'wievDoc/'.$date.' '.$time.' Уточнения '.explode(" ",$fio[0])[0].'.docx';
$document->saveAs($doc);

$doc2 = "wievDoc/".$date.' '.$time.' Уточнения '.explode(" ",$fio[0])[0].'.pdf';

//Генерим pdf

require_once 'pdf_function.php';
$pdfUrl = getPdfUrl($doc);
$pdfUrl = preg_replace("/ /","%20",$pdfUrl);

file_put_contents('./'.$doc2, file_get_contents($pdfUrl));


/*
require_once 'ilovepdf/init.php';
$ilovepdf = (rand(1,2) == 1) ? new Ilovepdf\Ilovepdf('project_public_49e6b7c8e53ef8884b9e72bef42f2179_u5iL104a5d099045bd2e9c624ba89a2674068','secret_key_c60779739c2de8799eaea8ad848e36ce_4M_LYfbd53a674f76b806e05f7219305a19dc') : new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
$myTaskConvertOffice = $ilovepdf->newTask('officepdf');
$file1 = $myTaskConvertOffice->addFile($doc);
$myTaskConvertOffice->execute();
$myTaskConvertOffice->download("wievDoc/");

$pdfUrl = "https://wg.belkurort.by/widget/docjetV2/".$doc2;
*/
} while (!file_exists($doc));

//Отправляем письмо $data['email']
require_once 'functions.php';

if($notSend or ($isOpenBookAfterSeptember and !$isOpenBookAfterSeptemberSending)) {
	$sendedEmail = true;
} else {
	$sendedEmail = sendEmail(explode(" ",$fio[0])[0]." ".explode(" ",$fio[0])[1], $manager_insert, "Уточнения по бронированию №".$data['numrequest'], $data['email'], $data['dog_naimenovanie_obekta_razmescheniya'], "/".$doc2, $data['mail_note']);
}
// $sendedEmail = ($notSend) ? true : sendEmail(explode(" ",$fio[0])[0]." ".explode(" ",$fio[0])[1], $manager_insert, "Уточнения по бронированию №".$data['numrequest'], $data['email'], $data['dog_naimenovanie_obekta_razmescheniya'], "/".$doc2, $data['mail_note']);

if($sendedEmail) {
	if(!$notSend and !$isOpenBookAfterSeptember) {
		//Пишем примечание к сделке о том что всё прошло успешно
		create_note($card_id, 'Уточнения по бронированию отправлены в '.$data['dog_naimenovanie_obekta_razmescheniya'], $manager);
	}
	//echo json_encode(['error' => false]);
	require_once 'src/success.php';
	printSuccess("Уточнения по бронированию отправлены в ".$data['dog_naimenovanie_obekta_razmescheniya']);
} else {
	create_note($card_id, "Произошла ошибка! Письмо не отправлено! Сгенерируйте уточнение и отправьте вручную.", $manager);	
	//echo json_encode(['error' => true]);
	require_once 'src/error.php';
	printError("Произошла ошибка! Письмо не отправлено! Попробуйте ещё раз или отправьте уточнения вручную.");
};

exit;