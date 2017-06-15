<?php
/**
 * Header template file
 * @author jason@silvermast.io
 */

?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/img/favicon.png" />

<title><?=models\Account::current()->name?><?=(isset($title) ? " - $title" : '')?></title>

<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
<link href="/dist/css/build.css" rel="stylesheet">

<?php if (isset($css)) foreach ($css as $stylesheet) ?>
<link href="<?=$stylesheet?>" rel="stylesheet" />