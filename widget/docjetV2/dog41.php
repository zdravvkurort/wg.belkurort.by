<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

	$temp_name = explode(" ",$data['name_manager']);
	$otchestvo = (isset($temp_name[2])) ? substr($temp_name[2], 0, 2)."." : "";
	$manager_insert = $temp_name[0]." ".substr($temp_name[1], 0, 2).". ".$otchestvo;
	$data['numrequest'] = (isset($data['numrequest'])) ? $data['numrequest'] : "Б/Н";
	
if($data['sign'] == '3406348') {
	$data['sign'] = 'null';
}

require_once 'src/autoload.php';
$document = (date("m",strtotime($data['data_vyezda'])) < date("m",strtotime($data['data_zaezda']))) ? new PhpOffice\PhpWord\TemplateProcessor('templatesV2/null_template_with_banket.docx') : new PhpOffice\PhpWord\TemplateProcessor('templatesV2/null_template.docx'); //шаблон

$document->setValue('today', date("d.m.Y"));
$document->setValue('dog_naimenovanie_obekta_razmescheniya', $data['dog_naimenovanie_obekta_razmescheniya']);
$document->setValue('data_vyezda', date("d.m.Y",strtotime($data['data_vyezda'])));
$document->setValue('data_zaezda', date("d.m.Y",strtotime($data['data_zaezda'])));
$document->setValue('kolichestvo_dney', $data['kolichestvo_dney']);
$document->setValue('sutki_dni', $data['sutki_dni']);
$document->setValue('tip_putevki', $data['tip_putevki']);
$document->setValue('kolichestvo_nomerov', $data['kolichestvo_nomerov']);
$document->setValue('tip_nomera', $data['tip_nomera']);
$document->setValue('name_manager', $manager_insert);
$document->setValue('primechanie_v_annul', $data['primechanie_v_annul']);
//$document->setValue('prilozhenie', preg_replace('/, услуги,[^;]+;/', "", $data['prilozhenie']));
$document->setValue('numrequest', $data['numrequest']);

$document->setValue('dolzhnost', $data['dolzhnost']);
$document->setImageValue('sign', array('path' => 'sign/'.$data['sign'].'.png', 'width' => 150, 'height' => 150, 'ratio' => true));

$document->cloneRowAndSetValues('id', $data['prilozhenie']);

$fio = explode(",",$data['turist_dogovor_fio_pasport_propiska']);

$docname = "wievDoc/".strtotime("now").'nul.docx';
ob_clean();
$document->saveAs($docname);
header_remove();
//header('Location: http://docs.google.com/viewer?url=http://wg.belkurort.by/widget/docjetV2/'.$docname);
header('Location: http://docs.google.com/viewer?url=http://wg.belkurort.by/widget/docjetV2/'.urlencode($docname).'&embedded=true');