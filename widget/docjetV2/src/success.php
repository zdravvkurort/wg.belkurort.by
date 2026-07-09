<?php
function printSuccess($text) {
  echo '
  <!DOCTYPE html>
  <html lang="en">
  
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Успех!</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
  </head>
  
  <body>
    <div class="h-screen w-screen flex bg-gray-100 space-y-4">
      <div class="m-auto">
        <img class="mx-auto h-24 m-8 animate-pulse" src="http://wg.belkurort.by/widget/docjetV2/src/img/logo_des.png" alt="" />
        <div id="text" class="block mx-auto w-4/5 text-center">
          <p class="text-xl text-green-600">'.$text.'</p>
        </div>
      </div>
    </div>
  </body>
  
  </html>  
  ';
}
?>