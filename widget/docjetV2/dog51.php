<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

require_once 'src/autoload.php';

$document = new PhpOffice\PhpWord\TemplateProcessor('templatesV2/act_template.docx'); //шаблон
$shet = array(
    array(
        'tid'        => 1,
        'tusl' => ucfirst($data['dog_s_lecheniem']).' в соответствии с программой туристического путешествия в '.$data['dog_naimenovanie_obekta_razmescheniya'].' с '.date("d.m.Y",strtotime($data['data_zaezda'])).' по '.date("d.m.Y",strtotime($data['data_vyezda'])).' для '.$data['kolichestvo_turistov'].' человек(-а)',
        'ted_izm'      => 'шт.',
        'tkol_vo'     => '1',
		'tcost'     => $data['stoimost_sanatoriya'],
		'tsum'     => $data['stoimost_sanatoriya'],
    )
);

if($data['turobsluzhivanie'] != 0) {
array_push($shet, array(
		'tid' => (count($shet)+1),
		'tusl' => 'Организация комплексного туристического обслуживания',
		'ted_izm'      => 'шт.',
        'tkol_vo'     => '1',
		'tcost'     => $data['turobsluzhivanie'],
		'tsum'     => $data['turobsluzhivanie']
));
};

if($data['infouslugi'] != 0) {
array_push($shet, array(
		'tid' => (count($shet)+1),
		'tusl' => 'Туристические информационные услуги',
		'ted_izm'      => 'шт.',
        'tkol_vo'     => '1',
		'tcost'     => $data['infouslugi'],
		'tsum'     => $data['infouslugi']
));
};

if($sumtransfer != 0) {
array_push($shet, array(
		'tid' => (count($shet)+1),
		'tusl' => 'Организация перевозки туристов автомобильным транспортом (трансфер)',
		'ted_izm'      => 'шт.',
        'tkol_vo'     => '1',
		'tcost'     => $sumtransfer,
		'tsum'     => $sumtransfer
));
};

$document->cloneRowAndSetValues('tid', $shet);

$format = new NumberFormatter("ru", NumberFormatter::SPELLOUT);


$fio = explode(",",$data['turist_dogovor_fio_pasport_propiska']);
$fioDOG = explode(" ", $fio[0]);

$document->setValue('turist_dogovor_fio', $fio[0]);
$document->setValue('FIO_dogovora', $fioDOG[0]." ".mb_substr($fioDOG[1], 0, 1).".".mb_substr($fioDOG[2], 0, 1).".");
$document->setValue('data_vyezda', date("d.m.Y",strtotime($data['data_vyezda'])));
$document->setValue('turist_dogovor_fio_pasport_propiska', $data['turist_dogovor_fio_pasport_propiska']);
$document->setValue('data_dogovora', date("d.m.Y",strtotime($data['data_dogovora'])));
$document->setValue('nomer_dogovora', $data['nomer_dogovora']);
$document->setValue('valyuta', $data['valyuta']);
$document->setValue('cena_uslug', $data['cena_uslug']);
$document->setValue('cena_uslug_propis', $format->format($data['cena_uslug']));

$document->setValue('boss_name', $data['boss_name']);
$document->setValue('boss_podpis', $data['boss_podpis']);
$document->setValue('boss_dolzhnost', $data['boss_dolzhnost']);

$document->setValue('turist_dogovor_fio_header', $data['turist_dogovor_fio_header']);

// Далее отправляем файл в браузер
if (!file_exists("docs/".$card_id)) {
    mkdir("docs/".$card_id, 0777, true);
	}
	
$date = date('d-m-Y');
$time = date('H:i:s');
ob_clean();
// $docfilename = "docs/".$card_id.'/'.$date.' '.$time.' Акт '.explode(" ",$fio[0])[0].'.docx';
$docfilename = "wievDoc/".$date.' '.$time.' Акт '.explode(" ",$fio[0])[0].'.docx';
$document ->saveAs($docfilename);

//Генерим pdf
require_once 'pdf_function.php';
$pdfUrl = getPdfUrl($docfilename);
$pdfUrl = preg_replace("/ /","%20",$pdfUrl);

require_once 'yaDiskFunc.php';
saveFileByLinkOnYaDisk( $pdfUrl, 
                        'leads', 
                        $card_id, 
                        'Акт '.explode(" ",$fio[0])[0].'.pdf', 
                        '/Генерация');
/*
require_once 'ilovepdf/init.php';
$ilovepdf = (rand(1,2) == 1) ? new Ilovepdf\Ilovepdf('project_public_49e6b7c8e53ef8884b9e72bef42f2179_u5iL104a5d099045bd2e9c624ba89a2674068','secret_key_c60779739c2de8799eaea8ad848e36ce_4M_LYfbd53a674f76b806e05f7219305a19dc') : new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
$myTaskConvertOffice = $ilovepdf->newTask('officepdf');
$file1 = $myTaskConvertOffice->addFile($docfilename);
$myTaskConvertOffice->execute();
$myTaskConvertOffice->download("docs/".$card_id);

$pdfUrl = "https://wg.belkurort.by/widget/docjetV2/docs/".$card_id.'/'.$date.'%20'.$time.'%20Акт%20'.explode(" ",$fio[0])[0].'.pdf';
*/
// примечание к сделке
// create_note($card_id, 'Сформирован Акт: '.$pdfUrl, $manager);

//Переходим к документу
flush();
header ('Location: '.$pdfUrl);