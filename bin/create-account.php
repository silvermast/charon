<?php
/**
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/4/17
 * @package charon
 */
require_once(__DIR__ . '/../core.php');
require_once(__DIR__ . '/functions.php');

// first, create the Account

$account = new models\Account([
    'slug' => mb_strtolower(trim(readline('Account Slug (e.g. mycompany): '))),
    'name' => trim(readline('Full Account Name: ')),
]);

if (models\Account::findOne(['slug' => $account->slug]))
    finish('An account with that slug already exists.');

$account->validate()->save();


// Next, create the user
$name  = trim(readline('User Name: '));
$email = trim(readline('User Email: '));
$user  = models\User::new(['name' => $name, 'email' => $email]);

$memberKey       = readline('Permission Level: (' . implode('|', array_keys(models\User::PERMLEVELS)) . '): ');
$user->permLevel = models\User::PERMLEVELS[$memberKey];
$user->accountId = $account->id;

// use hidden password functionality in read library
$pass1 = readline_masked('Enter Password: ');
$pass2 = readline_masked('Re-enter Password: ');

// check passwords
if ($pass1 != $pass2)
    finish('Passwords do not match.');

$user->setPassword($pass1);
$contentKey = core\crypto\AES::getRandomKey(32);
$user->setContentKey($pass1, $contentKey);
$user->save();

echo "Successfully created the user.\n";



$user->permLevel = models\User::PERMLEVELS['Owner'];
$user->save();

echo "Success!\n";