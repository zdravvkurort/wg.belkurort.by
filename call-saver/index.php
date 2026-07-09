<?php
?>
<head>
    <script src="https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js"></script>
</head>
<script>
window.YaAuthSuggest.init(
    {
        client_id: '968add541a684e2bbcc60c0ad137b23d',
        response_type: 'token',
        redirect_uri: 'https://wg.belkurort.by/call-saver/verification-code/'
    },
    'https://wg.belkurort.by/call-saver/verification-code/',
    {
      view: "button",
      parentId: "buttonContainerId",
      buttonSize: 'm',
      buttonView: 'main',
      buttonTheme: 'light',
      buttonBorderRadius: "0",
      buttonIcon: 'ya',
    }
  )
  .then(({handler}) => handler())
  .then(data => console.log('Сообщение с токеном', data))
  .catch(error => console.log('Обработка ошибки', error))
</script>