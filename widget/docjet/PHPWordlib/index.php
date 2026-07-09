<?php
require_once 'PHPWord.php';
$PHPWord = new PHPWord();
$document = $PHPWord->loadTemplate('Template.docx');
$document->setValue('d_num', '777');
$document->setValue('d_date', '04.10.2014');
$document->setValue('last_name', 'Никоненко');
$document->setValue('name', 'Сергей');
$document->setValue('surname', 'Васильевич');
$document->save('Template_full.docx');
?>