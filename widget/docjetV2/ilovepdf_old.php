<?php
require_once 'ilovepdf/init.php';
$ilovepdf = (rand(1,2) == 1) ? new Ilovepdf\Ilovepdf('project_public_49e6b7c8e53ef8884b9e72bef42f2179_u5iL104a5d099045bd2e9c624ba89a2674068','secret_key_c60779739c2de8799eaea8ad848e36ce_4M_LYfbd53a674f76b806e05f7219305a19dc') : new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
$myTaskConvertOffice = $ilovepdf->newTask('officepdf');
$file1 = $myTaskConvertOffice->addFile("docs/".$card_id.'/'.$date.' '.$time.' Договор '.explode(" ",$fio[0])[0].'.docx');
$myTaskConvertOffice->execute();
$myTaskConvertOffice->download("docs/".$card_id);

header("Content-Type: text/html; charset=utf-8");
		header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='."docs/".$card_id.'/'.$date.' '.$time.' Договор '.explode(" ",$fio[0])[0].'.pdf');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
		header('Content-Length: ' . filesize("docs/".$card_id.'/'.$date.' '.$time.' Договор '.explode(" ",$fio[0])[0].'.pdf'));
        flush();
		readfile("docs/".$card_id.'/'.$date.' '.$time.' Договор '.explode(" ",$fio[0])[0].'.pdf');