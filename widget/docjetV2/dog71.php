<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
	$temp_name = explode(" ",$data['name_manager']);
	$manager_insert = $temp_name[0]." ".substr($temp_name[1], 0, 2);
	if(isset($temp_name[2])) {
			$manager_insert = $manager_insert.". ".substr($temp_name[2], 0, 2).".";
	}

require_once 'src/autoload.php';

$data['stamp'] = 'oval_stamp';
$data['vh_stamp'] = 180;

if($data['sign'] == '3406348') {
	$data['stamp'] = 'round_stamp';
	$data['vh_stamp'] = 150;
}

$document = new PhpOffice\PhpWord\TemplateProcessor('templatesV2/bill_template_prepay.docx'); //шаблон

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

if($data['sum_novogod_banketa'] != 0) {
array_push($shet, array(
		'tid' => (count($shet)+1),
		'tusl' => 'Организация новогоднего банкета',
		'ted_izm'      => 'шт.',
        'tkol_vo'     => '1',
		'tcost'     => $data['sum_novogod_banketa'],
		'tsum'     => $data['sum_novogod_banketa']
));
};

if($data['sum_novogod_program'] != 0) {
	array_push($shet, array(
			'tid' => (count($shet)+1),
			'tusl' => 'Организация новогодней программы',
			'ted_izm'      => 'шт.',
					'tkol_vo'     => '1',
			'tcost'     => $data['sum_novogod_program'],
			'tsum'     => $data['sum_novogod_program']
	));
};

if($data['sum_novogod_utrennika'] != 0) {
array_push($shet, array(
		'tid' => (count($shet)+1),
		'tusl' => 'Организация детского новогоднего утренника',
		'ted_izm'      => 'шт.',
        'tkol_vo'     => '1',
		'tcost'     => $data['sum_novogod_utrennika'],
		'tsum'     => $data['sum_novogod_utrennika']
));
};

// $covid = "";
$rbcovid = "";
// if($data['san_country'] == "Россия") {
// 	$covid = " отрицательные результаты лабораторных обследований в отношении новой короновирусной инфекции COVID-19, полученные не позднее чем за 2 суток до даты отъезда в санаторно-курортное учреждение, справку выданную медицинским учреждением и заверенную печатью учреждения об отсутствии контакта с вероятными больными в срок не менее 14 дней до даты отъезда,";
// };
if($data['san_country'] == "РБ" and $data["not_rb"]) {
	$rbcovid = ", а так же доведены нормы Постановления Совета министров РБ от 25.03.2020 №171 «О мерах по предотвращению завоза и распространения инфекции, вызванной коронавирусом COVID-19»";
};
 
$document->cloneRowAndSetValues('tid', $shet);

$document->setValue('data_predopl_fact', date("d.m.Y",strtotime($data['data_predopl_fact']))); //!
$document->setValue('sum_predopl_fact', $data['sum_predopl_fact']); //!
$document->setValue('ostatok', $data['cena_uslug']-$data['sum_predopl_fact']); //!
$document->setValue('covid', $covid);
$document->setValue('rbcovid', $rbcovid);

$document->setValue('dog_schet', $data['dog_schet']); //!
$document->setValue('turist_dogovor_fio_pasport_propiska', $data['turist_dogovor_fio_pasport_propiska']);//!
$document->setValue('turist_dogovor_fio_pasport_propiska_short', $data['turist_dogovor_fio_pasport_propiska_short']);
$document->setValue('valyuta', $data['valyuta']); //!
$document->setValue('cena_uslug', $data['cena_uslug']); //!
$document->setValue('data_dogovora', date("d.m.Y",strtotime($data['data_dogovora']))); //!
$document->setValue('nomer_dogovora', $data['nomer_dogovora']); //!
$document->setValue('name_manager', $manager_insert); //!
$document->setValue('dolzhnost', $data['dolzhnost']);//!
$document->setValue('korschet_shet', $data['korschet_shet']);
$document->setValue('oplata_po_schetu', $data['oplata_po_schetu']);
$document->setImageValue('sign', array('path' => 'sign/'.$data['sign'].'.png', 'width' => 150, 'height' => 150, 'ratio' => true));//!
$document->setImageValue('stamp', array('path' => 'sign/'.$data['stamp'].'.png', 'width' => $data['vh_stamp'], 'height' => $data['vh_stamp'], 'ratio' => true));//!

$document->setValue('pay_days', $data['due_days']); 


//$document->cloneRowAndSetValues('prilozhenie', $data['prilozhenieUpd']);

$fio = explode(",",$data['turist_dogovor_fio_pasport_propiska']);

// Далее отправляем файл в браузер
if (!file_exists("docs/".$card_id)) {
    mkdir("docs/".$card_id, 0777, true);
	}
	
$date = date('d-m-Y');
$time = date('H:i:s');
ob_clean();
$docfilename = "wievDoc/".$date.' '.$time.' Счет '.explode(" ",$fio[0])[0].'.docx';
$document ->saveAs($docfilename);

//Генерим pdf

require_once 'pdf_function.php';
$pdfUrl = getPdfUrl($docfilename);
$pdfUrl = preg_replace("/ /","%20",$pdfUrl);

require_once 'yaDiskFunc.php';
saveFileByLinkOnYaDisk( $pdfUrl, 
                        'leads', 
                        $card_id, 
                        'Счёт к частичному договору '.explode(" ",$fio[0])[0].'.pdf', 
                        '/Генерация');

/*
require_once 'ilovepdf/init.php';
$ilovepdf = (rand(1,2) == 1) ? new Ilovepdf\Ilovepdf('project_public_49e6b7c8e53ef8884b9e72bef42f2179_u5iL104a5d099045bd2e9c624ba89a2674068','secret_key_c60779739c2de8799eaea8ad848e36ce_4M_LYfbd53a674f76b806e05f7219305a19dc') : new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
$myTaskConvertOffice = $ilovepdf->newTask('officepdf');
$file1 = $myTaskConvertOffice->addFile($docfilename);
$myTaskConvertOffice->execute();
$myTaskConvertOffice->download("docs/".$card_id);

$pdfUrl = "https://wg.belkurort.by/widget/docjetV2/docs/".$card_id.'/'.$date.'%20'.$time.'%20Счет%20'.explode(" ",$fio[0])[0].'.pdf';
*/
// примечание к сделке
// create_note($card_id, 'Сформирован счёт: '.$pdfUrl, $manager);

//Переходим к документу
flush();
header ('Location: '.$pdfUrl);