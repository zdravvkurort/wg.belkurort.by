<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
require_once 'src/autoload.php';

$document = new PhpOffice\PhpWord\TemplateProcessor('templatesV2/dop_covid.docx'); //шаблон

$fio = explode(",",$data['turist_dogovor_fio_pasport_propiska']);
$f = explode(" ",$fio[0]);
$allpay = (int)$data['sum_predopl_fact'] + (int)$data['sum_all_pay_fact'];
$finpay = (int)$data['cena_uslug'] - (int)$allpay;

$format = new NumberFormatter("ru", NumberFormatter::SPELLOUT);

$document->setValue('dog_schet', $data['dog_schet']);
$document->setValue('turist_dogovor_fio_pasport_propiska', $data['turist_dogovor_fio_pasport_propiska']);
$document->setValue('data_dogovora', date("d.m.Y",strtotime($data['data_dogovora'])));
$document->setValue('nomer_dogovora', $data['nomer_dogovora']);
$document->setValue('today', date("d.m.Y"));
$document->setValue('fio_tourist', $fio[0]);
$document->setValue('korschet', $data['korschet']);
$document->setValue('FIO_client', mb_convert_encoding($f[0]." ".mb_substr($f[1],0,1,'utf-8').". ".mb_substr($f[2],0,1,'utf-8').".","UTF-8"));

// $document->setValue('boss_name', $data['boss_name']);
// $document->setValue('boss_podpis', $data['boss_podpis']);
// $document->setValue('boss_dolzhnost', $data['boss_dolzhnost']);

$document->setValue('podpis', $data['podpis']);
$document->setValue('dolzhnost', $data['dolzhnost']);
$document->setValue('name_manager', $data['short_name_manager']);

$data['stamp'] = 'oval_stamp';
if($data['sign'] == '3406348') {
	$data['sign'] = 'null';
	$data['stamp'] = 'null';
}
$document->setImageValue('sign', array('path' => 'sign/'.$data['sign'].'.png', 'width' => 150, 'height' => 150, 'ratio' => true));
$document->setImageValue('stamp', array('path' => 'sign/'.$data['stamp'].'.png', 'width' => 180, 'height' => 180, 'ratio' => true));


$fio = explode(",",$data['turist_dogovor_fio_pasport_propiska']);

// Далее отправляем файл в браузер
$date = date('d-m-Y');
$time = date('H:i:s');
ob_clean();
$path_of_doc = "wievDoc/".$date.' '.$time.' Доп. соглашение '.explode(" ",$fio[0])[0].'.docx';
$document ->saveAs($path_of_doc);

require_once 'yaDiskFunc.php';
saveFileToYaDisk('leads', $card_id, '/Генерация', $path_of_doc);

		header("Content-Type: text/html; charset=utf-8");
		header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$path_of_doc);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
		header('Content-Length: ' . filesize($path_of_doc));
        flush();
		readfile($path_of_doc);

