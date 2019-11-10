<?php

require ROOT_PATH . '/functions/vendor/autoload.php';

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

function send_email($account_name, $to_address, $subject, $html_body)
{
    AlibabaCloud::accessKeyClient('****', '****')
        ->regionId('cn-hangzhou')
        ->asDefaultClient();

    try {
        $result = AlibabaCloud::rpc()
            ->product('Dm')
            ->version('2015-11-23')
            ->action('SingleSendMail')
            ->method('POST')
            ->host('dm.aliyuncs.com')
            ->options([
                'query' => [
                    'RegionId' => 'cn-hangzhou',
                    'AccountName' => $account_name,
                    'AddressType' => '1',
                    'ReplyToAddress' => 'false',
                    'ToAddress' => $to_address,
                    'Subject' => $subject,
                    'HtmlBody' => $html_body,
                    'FromAlias' => 'Bandori车站'
                ],
            ])
            ->request();
        //print_r($result->toArray());
        return true;
    } catch (ClientException $e) {
        //echo $e->getErrorMessage() . PHP_EOL;
        return false;
    } catch (ServerException $e) {
       // echo $e->getErrorMessage() . PHP_EOL;
        return false;
    }
}