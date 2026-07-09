<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
/**
 * default timezone for TCPDF date functions
 * see [url]http://www.php.net/manual/en/timezones.php[/url]
 * for a list of the supported timezones
 */
date_default_timezone_set('UTC');
function htmlout($text)
{
  return htmlspecialchars($text, ENT_QUOTES);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

  try {

    require_once('tcpdf/tcpdf.php');
	require_once 'functions.php';

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
	
	$pdf = new TCPDF("P", "mm", "A4", true, 'UTF-8', false);

	$pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins(10, 0, 10);
    $pdf->SetAutoPageBreak(true, 5);

    $pdf->setFontSubsetting(true);
    $pdf->SetFont('freeserif');
    $pdf->AddPage();

    $html = '<html>
<head>
<meta charset="utf-8">
<title>Form</title>
<style>
        * {
          font-size: 14pt;
          text-align: justify;
		}
        td.rightAlign {
          text-align: right;
        }
		td.centerAlign {
          text-align: center;
        }
        table{
            border: none;
        }
        h4 {
          font-size: 14pt;
          text-align: center;
        }
		p.indent {
           text-indent: 14px;
        }
		 p.justifyP {
          text-align: justify;
          text-indent: 14pt;
        }
				 p.center {
          text-align: center;
          text-indent: 14pt;
        }
						 p.justify {
          text-align: right;
          text-indent: 14pt;
        }
        div#requisites {
          font-size: 14pt;
          text-align: center;
        }
        div#underline {
            border-bottom: 0.5pt solid black;
        }
        #phone {
            font-size: 14pt;
            text-align: right;
        }
</style>
</head>
<body>
<p></p>
<table>
<tr>
<td width="100%" class="leftAlign">' . date("d.m.Y") . '</td>
</tr>
</table>
<p  class="center">Общество с ограниченной ответственностью <br/>
Республика Беларусь, г. Минск, ул. К.Либкнехта, д. 66, пом. 73а<br/>
УНП 193237911<br/>
====================================================================</p>
<p class = "center">Заявка на бронирование</p>
<p>ООО «Здравкурорт» в соответствии с заключенным договором просит забронировать следующие 
путевки в '.$dog_naimenovanie_obekta_razmescheniya.':</p>
<p>Заезд c '.date("d.m.Y",strtotime($data_zaezda)).' по '.date("d.m.Y",strtotime($data_vyezda)).' на '.$kolichestvo_dney.' 
'.$sutki_dni.' ('.$tip_putevki.').<br/>
Категория номера: '.$tip_nomera.'<br/>
Количество номеров: '.$kolichestvo_nomerov.'<br/>
'.$dog_edet_li_turist_dogovor.'<br/>
'.$turist_2.'<br/>
'.$turist_3.'<br/>
'.$turist_4.'<br/>
'.$turist_5.'</p>
<p>Ожидаем письменное подтверждение бронирования на 
e-mail: info@zdravkurort.by</p>
<p>В случае подтверждения счёт-фактуру просим высылать в установленные договором сроки на указанные e-mail.</p>
<p></p>
<p></p>
<p></p>
<table>
<tr>
<td width="50%" class="leftAlign">С уважением,<br/>
Специалист по туризму<br/>
ООО «Здравкурорт»</td>
<td width="50%" class="leftAlign">'.$manager_insert.'</td>
</tr>
</table>
</body>
</html>';
 
    $pdf->writeHTML($html, "I");
$fio = explode(",",$turist_dogovor_fio_pasport_propiska);
$fio_insert = str_replace(" ", "%20", $fio[0]);
$date = date('d-m-Y');
$time = date('H:i:s');	
    //$pdf->Output('form.pdf');
	$pdf->Output($card_id.'/'.$date.' '.$time.' Заявка на бронирование + '.$fio[0].'.pdf');

  } catch (Exception $e) {

    echo 'В процессе генерации документа возникла ошибка: ' . $e->getMessage();
  }
  }