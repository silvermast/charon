<?php
require_once(__DIR__ . '/../core.php');
require_once(__DIR__ . '/functions.php');

$_SERVER['HTTP_HOST'] = $argv[1] ?? mb_strtolower(trim(readline('Account Slug: '))) . '.cli';

if (!$account = models\Account::current())
    finish('Account does not exist.');

if (!CLIUser::me())
    finish('Unable to verify user credentials.');

echo "\nContentKey: " . CLIUser::me()->contentKey . "\n\n";

$lockerId = $argv[2] ?? mb_strtolower(trim(readline('Locker ID: ')));


$locker = models\Locker::findOne([
    'accountId' => \models\Account::current()->id,
    'id' => $lockerId,
]);

$locker->items = json_decode(core\crypto\AES::decrypt($locker->items, CLIUser::me()->contentKey));
echo json_encode($locker, JSON_PRETTY_PRINT);
echo "\n";
die(0);