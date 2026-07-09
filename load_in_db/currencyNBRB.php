<?
    require "../db_login.php";
    require "../functions.php";

    list($json, $content) = getCurrencyNBRB();
    $currentDataDate = date('d.m.Y', strtotime($json[0]["Date"]));

    $stmt = $db->query("SELECT * FROM `currency_quotes` WHERE DATE_FORMAT(`created_at`, '%d.%m.%Y') = '$currentDataDate'");
    $existCurrency = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!count($existCurrency) and isset($json[0]["Cur_ID"])) {
        $db->query("INSERT INTO currency_quotes SET data='".$content."'");
    }

    function getCurrencyNBRB() {
        $myURL = 'https://www.nbrb.by/API/ExRates/Rates?Periodicity=0';
        // $myURL = 'https://www.nbrb.by/API/ExRates/Rates?ondate=2025-02-03&periodicity=0';

        $ch = curl_init($myURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $content = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($content,true);
        return [$json, $content];
    }
?>