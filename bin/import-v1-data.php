<?php
/**
 * Migrates old User data structure to the new data structure
 * @author Jason Wright <jason@silvermast.io>
 * @since 1/9/17
 * @package charon
 */

require_once(__DIR__ . '/../core.php');
require_once(__DIR__ . '/functions.php');

$_SERVER['HTTP_HOST'] = mb_strtolower(trim(readline('Account Slug: '))) . '.cli';

if (!$account = models\Account::current())
    finish('Account does not exist.');

if (!CLIUser::me())
    finish('Unable to verify user credentials.');

$dataPath = trim(readline('Path to old data: '));

if (!file_exists($dataPath . '/crypt.key'))
    $cryptKey = readline('Enter CRYPT_KEY: ');
else
    $cryptKey = file_get_contents($dataPath . '/crypt.key');

$cryptKey = md5($cryptKey);

/**
 * @param $obj
 * @param $cryptKey
 * @return string
 */
function decrypt($obj, $cryptKey) {

    $obj->crypt = base64_decode($obj->crypt);
    $obj->iv    = base64_decode($obj->iv);

    // decrypt
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $cryptKey, $obj->crypt, MCRYPT_MODE_CBC, $obj->iv));

}

/**
 * @param $path
 * @param $cryptKey
 * @return mixed|string
 * @throws Exception
 */
function read_encrypted_datafile($path, $cryptKey) {
    if (!file_exists($path))
        throw new Exception("$path: file does not exist", 501);

    if (!$raw_data = file_get_contents($path))
        throw new Exception("$path: Failed to read data", 502);

    // If the string is empty, just return it
    if (mb_strlen($raw_data) === 0)
        throw new Exception("$path: empty file", 508);

    // First uncompress
    if (!$output = gzuncompress($raw_data))
        throw new Exception("$path: Failed to uncompress data", 503);

    if (!$outputJson = json_decode($output))
        throw new Exception("$path: Failed to uncompress data", 504);

    if (!isset($outputJson->crypt, $outputJson->iv))
        throw new Exception("$path: Invalid Encrypted object. Missing 'crypt' or 'iv' property\ndata: $output", 505);

    if (!$result = decrypt($outputJson, $cryptKey))
        throw new Exception("$path: Failed to decrypt data: " . var_export($result, true), 506);

    while (is_string($result))
        $result = json_decode($result);

    return $result;
}

$oldLockers = scandir($dataPath);

// migrate locker data
foreach ($oldLockers as $filename) {
    try {
        if (!preg_match('/^[0-9a-fA-F]{64}$/', $filename))
            continue;

        $oldItem            = read_encrypted_datafile("$dataPath/$filename", $cryptKey);
        $newItem            = models\Locker::new($oldItem);
        $newItem->id        = null;
        $newItem->accountId = models\Account::current()->id;
        $newItem->items     = core\crypto\AES::encrypt(json_encode($newItem->items), CLIUser::me()->contentKey);

        $newItem->validate()->save();
        echo '.';

    } catch (Exception $e) {
        echo $e->getCode() . ": " . $e->getMessage() . PHP_EOL;
        continue;
    }
}

echo "\nDone!\n";
echo "Memory: " . (memory_get_usage() / 1024 / 1024) . "MB\n";
die(0);
