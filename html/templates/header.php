<?php
/**
 * Header template file
 * @author jason@silvermast.io
 */

?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Security-Policy"
        content="
          default-src 'self' 'unsafe-inline' 'unsafe-eval';
          style-src 'self' 'unsafe-inline';
          media-src 'self' 'unsafe-inline';
          img-src 'self' 'unsafe-inline'">
<link rel="icon" href="/img/favicon.png" />

<title><?=models\Account::current()->name?><?=(isset($title) ? " - $title" : '')?></title>

<link href="/dist/css/build.css" rel="stylesheet">

<?php if (isset($css)) foreach ($css as $stylesheet) ?>
<link href="<?=$stylesheet?>" rel="stylesheet" />