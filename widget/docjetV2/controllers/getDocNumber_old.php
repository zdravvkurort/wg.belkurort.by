<?php
require_once 'ContractModel.php';
require_once 'ModelHelperFunction.php';
require_once './src/error.php';
require_once 'src/success.php';

function getContractNumber($docType, $data, $buttonType) {
  $datePostfix = "/".date('my',strtotime($data["data_dogovora"]));
  $contractModel = [
    "lead_id" => $data['card_id'],
    "san_id" => $data["sanid"],
    "Туроператор" => $data['idtouroperator'],
    "Питание" => $data["pitanie"],
    "Тип путёвки" => $data["tip_putevki"],
    "Категория номера" => $data["tip_nomera"],
    "Количество номеров" => $data["kolichestvo_nomerov"],
    "Примечание в заявке" => $data["primechanie_v_zayavke"],
    "На кого составляется договор" => $data["turist_dogovor_fio_pasport_propiska"],
    "Дата заезда" => $data["data_zaezda"],
    "Дата выезда" => $data["data_vyezda"],
    "Количество дней" => $data["kolichestvo_dney"],
    "Планируемая дата оплаты" => $data["hypot_date_pay"],
    "Валюта" => $data["valyuta"],
    "Тип платежа" => $data["type_oplaty"],
    "Стоимость санатория" => $data["stoimost_sanatoriya"],
    "Величина предоплаты" => $data["sum_predopl_dlya_dogovora"],
    "Туробслуживание" => $data["turobsluzhivanie"],
    "Инфоуслуги" => $data["infouslugi"],
    "Трансфер" => $data["dog_transfer"],
    "Эквайринг" => $data["ekvayring"],
    "Цена услуг" => $data['cena_uslug'],
    "Банкет включен" => $data["newyear"],
    "Стоимость банкета" => $data["sum_new_year"],
    "Стоимость детского утренника" => $data["sum_new_year_utrennik"],
    "Дата договора" => $data["data_dogovora"],
    "Гости" => $data["prilozhenie"]
  ];

  $dbContract = getCurrentContract($data['card_id']);
  $isSameContract = matchCurrentAndExist($dbContract["payload"], json_encode($contractModel));

  // Кнопка актуализации данных в договоре
  if($dbContract and ($docType == 1 or $docType == 6) and $buttonType == 'actualize') {
    updateContract($dbContract['num'], $contractModel);
    printSuccess("Данные по договору обновлены, номер договора не меняли.");
    exit;
  }

  // Есть номер договора и нет изменений? !!!
  if($dbContract and $isSameContract) {
    // Возвращаем текущий номер договора
    return $dbContract['num'].$datePostfix;
  }

  // Тип документа заявка корректировка уточнение аннуляция? !!!
  if($docType == 2 or $docType == 9 or $docType == 0 or $docType == 4) {
    return 'Б/Н';
  }
  
  // Есть номер договора и payload = ''? !!!
  if($dbContract and ($dbContract["payload"] == '' or $dbContract["payload"] == '""')) {
    // Обновляем payload и возвращаем его номер
    updateContract($dbContract['num'], $contractModel);
    return $dbContract['num'].$datePostfix;
  }

  // Тип документа договор?  !!!
  if($docType == 1 or $docType == 6) {
    // Создаём договор и возвращаем его номер
    $num = createContract($data['card_id'], $contractModel);
    changeLeadCustomField($data['card_id'], 305285, $num.$datePostfix);
    return $num.$datePostfix;
  }

  // Есть номер договора и Тип документа доп? !!!
  if($dbContract and $docType == 8) {
    // Обновляем payload
    updateContract($dbContract['num'], $contractModel);
    return $dbContract['num'].$datePostfix;
  }

  // Есть номер договора? !!!
  if($dbContract) {
    // Возвращаем его номер
    return $dbContract['num'].$datePostfix;
  }

  // Нет номера договора и документ счёт?
  if(!$dbContract and $docType == 7) {
    printError('По данной сделке не сгенерирован договор, а поэтому и акта быть не может.');
    exit;
  }

  // Возвращаем Б/Н !!!
  return 'Б/Н';
}

?>