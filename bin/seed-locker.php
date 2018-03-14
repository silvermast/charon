<?php
require_once(__DIR__ . '/../core.php');
require_once(__DIR__ . '/functions.php');

$_SERVER['HTTP_HOST'] = mb_strtolower(trim(readline('Account Slug: '))) . '.cli';

if (!$account = models\Account::current())
    finish('Account does not exist.');

if (!CLIUser::me())
    finish('Unable to verify user credentials.');


function randomString($len) {
    $chars   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ?><.,][}{!@#$%^&*()-=_+|`~';
    $charLen = strlen($chars) - 1;
    $result  = '';
    $len     = $len ?? 16;
    for ($i = 0; $i < $len; $i++)
        $result .= $chars[mt_rand(0, $charLen)];

    return $result;
}

$semStr = new core\SemanticString();

echo "Seeding\n";
// generate 100 Locker objects
for ($i = 0; $i < 100; $i++) {

    $locker            = new models\Locker();
    $locker->accountId = $account->id;
    $locker->name      = $semStr->getSemanticString();
    $locker->note      = "--SEED--\n" . $semStr->getSemanticString(50);
    $locker->items     = [];

    for ($j = 0; $j < mt_rand(2, 50); $j++)
        $locker->items[] = [
            'title'     => $semStr->getRandomWord(),
            'url'       => "https://" . $semStr->getRandomWord() . "." . $semStr->getRandomWord(),
            'user'      => $semStr->getRandomWord(),
            'pass'      => randomString(mt_rand(8, 64)),
        ];

    $locker->items = core\crypto\AES::encrypt(json_encode($locker->items), CLIUser::me()->contentKey);

    $locker->save();
    echo '.';
}


echo "\nDone!\n";
echo "Memory: " . (memory_get_usage() / 1024 / 1024) . "MB\n";
die(0);