<?php
// $mailAuth = (object)[
//   "user" => 'mail@mail.zdravkurort.by',
// 	"password" => 'MQdzE68O5xX%',
// "host" => 'mailbe05.hoster.by'
// ];

$mailAuth = (object)[
  	"user" => 'info@zdravkurort.by',
	"password" => 'D1TYI@A?xo',
	"host" => 'imap.hoster.by'
];

$varDate = time();

function dateFormat($varDate){
   $mounth = array(
       'января',
       'февраля',
       'марта',
       'апреля',
       'мая',
       'июня',
       'июля',
       'августа',
       'сентября',
       'октября',
       'ноября',
       'декабря'
   );
    settype($varDate, "integer");

   $mounthNumber = date("n", $varDate) - 1;
   $date = date("d", $varDate) . " " . $mounth[$mounthNumber] . " " . date("Y", $varDate);
   return $date;
}

function dateFormatStandard($varDate){

    settype($varDate, "integer");
    $date = date("d.m.Y", $varDate);
    return $date;
}

function number2string($num) {
    $nul='';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('' ,'' ,'',  1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        $out[] = "(kkwer1";
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
        $out[] = "kkwer1)";
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    //$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return $str = str_replace(" kkwer1", "",str_replace("kkwer1 ", "",trim(preg_replace('/ {2,}/', ' ', join(' ',$out)))));
}

function number3string($num) {
    $nul='';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('' ,'' ,'',  1),
        array('доллар США'   ,'доллара США'   ,'долларов США'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        $out[] = "(kkwer1";
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
        $out[] = "kkwer1)";
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return $str = str_replace(" kkwer1", "",str_replace("kkwer1 ", "",trim(preg_replace('/ {2,}/', ' ', join(' ',$out)))));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}


require_once('petrovich-php-master/Petrovich.php');

function nameFormat($fio, $sex){
   if ($sex == 'муж')
      $petrovich = new Petrovich(Petrovich::GENDER_MALE);
   else if ($sex == 'жен')
      $petrovich = new Petrovich(Petrovich::GENDER_FEMALE);
   else
      $petrovich = new Petrovich();
   $FIO = explode(" ", $fio);
   $lastname = $FIO[0];
   $firstname = $FIO[1];
   $middlename = $FIO[2];


   $name = $petrovich->lastname($lastname, Petrovich::CASE_INSTRUMENTAL) . ' ' . $petrovich->firstname($firstname, Petrovich::CASE_INSTRUMENTAL) . ' ' . $petrovich->middlename($middlename, Petrovich::CASE_INSTRUMENTAL);
   return $name;
}

function sendEmail($FioGuest, $man, $typemail, $email, $tnf, $attach, $mail_note="") {

	require_once 'PHPMailer/PHPMailerAutoload.php';
	global $mailAuth;

		if(stripos($typemail,"Аннул") !== false){
			$typemailbody =	"аннуляцию"; 
		} else if (stripos($typemail,"Изменени") !== false) {
			$typemailbody =	"изменения по бронированию"; 
		} else if (stripos($typemail,"Уточнени") !== false) {
			$typemailbody =	"уточнения по бронированию"; 
		} else {
			$typemailbody =	"бронирование"; 
		};

	$mail = new PHPMailer;
	$mail->CharSet = 'UTF-8';

	// Настройки SMTP
	$mail->isSMTP();
	$mail->SMTPAuth = true;
	$mail->SMTPDebug = 0;

	$mail->Host = 'ssl://'.$mailAuth->host;
	$mail->Port = 465;
	$mail->Username = $mailAuth->user;
	$mail->Password = $mailAuth->password;

	// От кого
	$mail->setFrom('info@zdravkurort.by', 'ООО Здравкурорт');        
	
	// Кому
	$mailsArray = explode(",", $email);
	foreach ($mailsArray as $v) {
		$mail->addAddress(trim($v), $tnf);
		// $mail->addReplyTo('info@zdravkurort.by', 'Здравкурорт');
	}

	// Тема письма
	$mail->Subject = $typemail." ".$FioGuest." ".$tnf;

	$body = '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html style="width:100%;font-family: "Muller", "helvetica neue", helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;">
	 <head> 
	  <meta charset="UTF-8"> 
	  <meta content="width=device-width, initial-scale=1" name="viewport"> 
	  <meta name="x-apple-disable-message-reformatting"> 
	  <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
	  <meta content="telephone=no" name="format-detection"> 
	  <title>Новое письмо</title> 
	  <!--[if (mso 16)]>
		<style type="text/css">
		a {text-decoration: none;}
		</style>
		<![endif]--> 
	  <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--> 
	  <!--[if !mso]><!-- --> 
	  <!--<![endif]--> 
	  <style type="text/css">
	@media only screen and (max-width:600px) {p, ul li, ol li, a { font-size:16px!important; line-height:150%!important } h1 { font-size:32px!important; text-align:center; line-height:120%!important } h2 { font-size:26px!important; text-align:center; line-height:120%!important } h3 { font-size:20px!important; text-align:center; line-height:120%!important } h1 a { font-size:32px!important } h2 a { font-size:26px!important } h3 a { font-size:20px!important } .es-menu td a { font-size:16px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:16px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:inline-block!important } a.es-button { font-size:16px!important; display:inline-block!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } .es-desk-menu-hidden { display:table-cell!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } }
	@font-face {
		font-family:"Muller";
		src:url("https://new.belkurort.by/src/fonts/muller/MullerRegular.eot");
		src:url("https://new.belkurort.by/src/fonts/muller/MullerRegular.eot?#iefix") format("embedded-opentype"),
			url("https://new.belkurort.by/src/fonts/muller/MullerRegular.woff2") format("woff2"),
			url("https://new.belkurort.by/src/fonts/muller/MullerRegular.woff") format("woff"),
			url("https://new.belkurort.by/src/fonts/muller/MullerRegular.ttf") format("truetype");
		font-weight:normal;
		font-style:normal;
	}
	#outlook a {
		padding:0;
	}
	.ExternalClass {
		width:100%;
	}
	.ExternalClass,
	.ExternalClass p,
	.ExternalClass span,
	.ExternalClass font,
	.ExternalClass td,
	.ExternalClass div {
		line-height:100%;
	}
	.es-button {
		mso-style-priority:100!important;
		text-decoration:none!important;
	}
	a[x-apple-data-detectors] {
		color:inherit!important;
		text-decoration:none!important;
		font-size:inherit!important;
		font-family:inherit!important;
		font-weight:inherit!important;
		line-height:inherit!important;
	}
	.es-desk-hidden {
		display:none;
		float:left;
		overflow:hidden;
		width:0;
		max-height:0;
		line-height:0;
		mso-hide:all;
	}
	</style> 
	 </head> 
	 <body style="width:100%;font-family:Muller, "helvetica neue", helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;"> 
	  <div class="es-wrapper-color" style="background-color:#A0D9D9;"> 
	   <!--[if gte mso 9]>
				<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
					<v:fill type="tile" color="#a0d9d9" origin="0.5, 0" position="0.5,0"></v:fill>
				</v:background>
			<![endif]--> 
	   <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;"> 
		 <tr style="border-collapse:collapse;"> 
		  <td valign="top" style="padding:0;Margin:0;"> 
		   <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;"> 
			 <tr style="border-collapse:collapse;"></tr> 
			 <tr style="border-collapse:collapse;"> 
			  <td align="center" style="padding:0;Margin:0;"> 
			   <table class="es-header-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#044767;" width="600" cellspacing="0" cellpadding="0" bgcolor="#044767" align="center"> 
				 <tr style="border-collapse:collapse;"> 
				  <td align="left" bgcolor="#fff" style="Margin:0;padding-top:10px;padding-bottom:10px;padding-left:10px;padding-right:10px;background-color:#FFFFFF;background-position:left top;"> 
				   <table cellspacing="0" cellpadding="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;"> 
					 <tr style="border-collapse:collapse;"> 
					  <td class="es-m-p0r" width="580" valign="top" align="center" style="padding:0;Margin:0;"> 
					   <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-position:left top;"> 
						 <tr style="border-collapse:collapse;"> 
						  <td align="center" style="padding:0;Margin:0;"><a target="_blank" href="https://zdravkurort.by" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:Muller, "helvetica neue", helvetica, arial, sans-serif;font-size:14px;text-decoration:none;color:#FFFFFF;"><img class="adapt-img" src="https://vdpda.stripocdn.email/content/guids/CABINET_564d9778d355cfad7a73cd74b52951f0/images/90531573547684900.jpg" alt="Здравкурорт" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" title="Здравкурорт" height="161"></a></td> 
						 </tr> 
					   </table></td> 
					 </tr> 
				   </table></td> 
				 </tr> 
			   </table></td> 
			 </tr> 
		   </table> 
		   <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;"> 
			 <tr style="border-collapse:collapse;"> 
			  <td align="center" style="padding:0;Margin:0;"> 
			   <table class="es-content-body" width="600" cellspacing="0" cellpadding="0" bgcolor="#fff" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;"> 
				 <tr style="border-collapse:collapse;"> 
				  <td style="Margin:0;padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right:20px;background-color:#FFFFFF;" bgcolor="#fff" align="left"> 
				   <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;"> 
					 <tr style="border-collapse:collapse;"> 
					  <td width="560" valign="top" align="center" style="padding:0;Margin:0;"> 
					   <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;"> 
						 <tr style="border-collapse:collapse;"> 
						  <td align="center" style="padding:0;Margin:0;padding-bottom:15px;"><h2 style="Margin:0;line-height:34px;mso-line-height-rule:exactly;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;font-size:28px;font-style:normal;font-weight:bold;color:#333333;">'.
						  $typemail.'</h2><br><h2 style="Margin:0;line-height:34px;mso-line-height-rule:exactly;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;font-size:28px;font-style:normal;font-weight:bold;color:#333333;">'.
						  $FioGuest.'</h2></td> 
						 </tr> 
						 <tr style="border-collapse:collapse;"> 
						  <td align="center" style="padding:0;Margin:0;padding-bottom:10px;padding-top:15px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:24px;color:#777777;">Прошу подтвердить '.
						  $typemailbody.' гостя '.
						  $FioGuest.'</p>
						  <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:24px;color:#777777;">'.
						  $mail_note.'</p>
						  <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:24px;color:#777777;">'.
						  $typemailbody.' во вложении.</p></td> 
						 </tr> 
						 <tr style="border-collapse:collapse;"> 
						  <td align="center" style="padding:0;Margin:0;padding-bottom:10px;padding-top:15px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:24px;color:#777777;">С уважением, '.
						  $man.'</p><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:16px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:24px;color:#777777;">Компания Здравкурорт</p></td> 
						 </tr> 
					   </table></td> 
					 </tr> 
				   </table></td> 
				 </tr> 
			   </table></td> 
			 </tr> 
		   </table> 
		   <table class="es-footer" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top;"> 
			 <tr style="border-collapse:collapse;"> 
			  <td align="center" style="padding:0;Margin:0;"> 
			   <table class="es-footer-body" width="600" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;"> 
				 <tr style="border-collapse:collapse;"> 
				  <td align="left" style="Margin:0;padding-top:20px;padding-bottom:20px;padding-left:35px;padding-right:35px;background-position:left top;"> 
				   <!--[if mso]><table width="530" cellpadding="0" cellspacing="0"><tr><td width="172" valign="top"><![endif]--> 
				   <table cellspacing="0" cellpadding="0" align="left" class="es-left" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left;"> 
					 <tr style="border-collapse:collapse;"> 
					  <td class="es-m-p20b" width="172" valign="top" align="center" style="padding:0;Margin:0;"> 
					   <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;"> 
						 <tr style="border-collapse:collapse;"> 
						  <td align="center" style="padding:0;Margin:0;"><img class="adapt-img" src="https://vdpda.stripocdn.email/content/guids/CABINET_564d9778d355cfad7a73cd74b52951f0/images/67101573548101671.jpg" alt style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;" height="104"></td> 
						 </tr> 
					   </table></td> 
					 </tr> 
				   </table> 
				   <!--[if mso]></td><td width="20"></td><td width="338" valign="top"><![endif]--> 
				   <table cellpadding="0" cellspacing="0" class="es-right" align="right" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:right;"> 
					 <tr style="border-collapse:collapse;"> 
					  <td width="338" align="left" style="padding:0;Margin:0;"> 
					   <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;"> 
						 <tr style="border-collapse:collapse;"> 
						  <td align="left" style="padding:10px;Margin:0;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#333333;">ООО "ЗДРАВКУРОРТ"</p><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#333333;">УНП 193237911</p><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#333333;">Республика Беларусь, 220037,</p><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:"Muller", "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#333333;">г. Минск, ул. Скрыганова д. 2Б, пом. 3</p></td> 
						 </tr> 
					   </table></td> 
					 </tr> 
				   </table> 
				   <!--[if mso]></td></tr></table><![endif]--></td> 
				 </tr> 
			   </table></td> 
			 </tr> 
		   </table></td> 
		 </tr> 
	   </table> 
	  </div>  
	 </body>
	</html>
	';

	$mail->msgHTML($body);

		// Приложение
		$mail->addAttachment(__DIR__ .$attach);
		if ($mail->send()) {
			save_mail($mail);
			return true;
		} else {
			return false;
		}
}

function save_mail($mail)
{
	global $mailAuth;
    $path = "{".$mailAuth->host.":993/imap/ssl}Sent";
    $imapStream = imap_open($path, $mailAuth->user, $mailAuth->password);
    $result = imap_append($imapStream, $path, str_replace("
", "\r\n",$mail->getSentMIMEMessage())
	//$mail->getSentMIMEMessage()
	);
    imap_close($imapStream);
    return $result;
}