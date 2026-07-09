<?php
function printError($text, $errorDetails = []) {
  $rows = '';
  foreach ($errorDetails as $detail) {
    $rows .= '<tr>
                <td class="">'.$detail['type'].'</td>
                <td class="">'.$detail["name"].'</td>
                <td class="">'.$detail["exist"].'</td>
                <td class="">'.$detail["current"].'</td>
              </tr>';
  }
  if(count($errorDetails)){
    $table = '<div class="table-wrapper">
                <table class="table">
                  <thead class="">
                    <tr>
                      <th class="">Тип</th>
                      <th class="">Поле</th>
                      <th class="">В отправленной заявке</th>
                      <th class="">В текущей сделке</th>
                    </tr>
                  </thead>
                  <tbody>
                    '.$rows.'
                  </tbody>
                </table>
              </div>';
  } else {
    $table = '';
  }

  echo '
  <!DOCTYPE html>
  <html lang="en">
  
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибочка!</title>
  </head>
  
  <body class="main">
    <main class="main">
      <div class="main-wrap">
        <div class="logo-wrapper">
          <img class="logo" src="http://wg.belkurort.by/widget/docjetV2/src/img/logo_des.png" alt="" />
        </div>
        <div class="text-wrapper">
          <span class="error">Ошибка!</span>
          <p class="error-text">'.$text.'</p>
        </div>
        '.$table.'
      </div>
    </main>
  </body>
  
  <style>
    body {
      margin: 0;
      background-color: #efefef;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
    }
  
    .main {
      height: 100%;
      width: 100%;
    }
  
    .main-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      height: 100%;
    }
  
    .logo {
      width: 100px;
      height: 100px;
      margin: 24px;
    }
  
    .text-wrapper {
      text-align: center;
    }
  
    .error {
      color: rgb(239, 68, 68);
      font-size: 1.5em;
      font-weight: 400;
    }
  
    .error-text {
      max-width: 700px;
      color: rgb(0, 0, 0);
      font-size: 20px;
      font-weight: 400;
    }
  
    .table-wrapper {
      width: 50%;
      max-width: 700px;
      min-width: 300px;
    }
  
    .table {
      width: 100%;
      margin-bottom: 20px;
      border: 1px solid #dddddd;
      border-collapse: collapse;
      font-size: 14px;
    }
  
    .table th {
      font-weight: bold;
      padding: 5px;
      background: #efefef;
      border: 1px solid #dddddd;
    }
  
    .table td {
      border: 1px solid #dddddd;
      padding: 5px;
    }
  </style>
  
  </html>
  ';
}

?>