<?php

namespace core;

use Aws\Ses\SesClient;
use \Exception;

/**
 * This class houses all application emails methods
 * @author Jason Wright <jason.dee.wright@gmail.com>
 * @since 2/17/17
 * @package charon
 * @see https://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-ses.html
 */
class Email {

    /**
     * Sends an email through the SES API
     * @param $to
     * @param $body
     * @param $subject
     * @param array $extra
     * @throws Exception
     */
    private static function send($to, $subject, $body, $extra = []) {
        // aws ses send-email --from $email --to $email --text $body --subject $subject --region us-west-2
        try {
            if (!$to)
                throw new Exception(__METHOD__ . " - to address is invalid. '$to'");

            $client = new SesClient([
                'version' => '2010-12-01',
                'region'  => AWS_SES_REGION,
            ]);

            $result = $client->sendEmail(array_merge_recursive([
                'Source'      => 'SilverMast <no-reply@silvermast.io>',
                'Destination' => [
                    'ToAddresses' => is_array($to) ? $to : [$to],
                ],
                'Message'     => [
                    'Subject' => [
                        'Data'    => $subject,
                        'Charset' => 'UTF-8',
                    ],
                    'Body'    => [
                        'Text' => [
                            'Data'    => strip_tags($body),
                            'Charset' => 'UTF-8',
                        ],
                        'Html' => [
                            'Data'    => $body,
                            'Charset' => 'UTF-8',
                        ],
                    ],
                ],
            ], $extra));

            Debug::info($result);

        } catch (\Exception $e) {
            Debug::error($e);
            throw new \Exception('Failed to send email');
        }
    }

    /**
     * @param \models\User $user
     */
    public static function request_password_reset(\models\User $user) {
        $subject = "{$user->Account->name} password reset request";
        $message = Template::get('email/tmpl.request-password-reset.php', ['user' => $user]);
        Debug::info([__METHOD__, $user]);
        self::send($user->email, $subject, $message);
    }

}