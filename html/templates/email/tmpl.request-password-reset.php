<?php
/**
 * @var \models\User $user
 */
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "{$user->Account->slug}.silvermast.io";
?>
<div style="font-family: sans-serif;">
    <p>Dear <?=$user->name?>,</p><br />

    <p>Someone has requested a password reset for your email and account.</p>

    <table>
        <tr>
            <td>Account</td>
            <td><b><?=$user->Account->slug?></b></td>
        </tr>
        <tr>
            <td>Email</td>
            <td><b><?=$user->email?></b></td>
        </tr>
    </table>

    <p>If this was a mistake or you're not the one who made the request, simply ignore this email.</p>

    <p>To reset your password, visit the following address. The authentication token will expire in 24 hours.</p>
    <br>
    <a href="https://<?=$http_host?>/reset-password?token=<?=$user->passwordResetToken?>" target="_blank">https://<?=$http_host?>/reset-password?token=<?=$user->passwordResetToken?></a>

</div>