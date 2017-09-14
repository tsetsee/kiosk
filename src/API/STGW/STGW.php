<?php
/**
 * Created by PhpStorm.
 * User: tsetsee
 * Date: 9/14/17
 * Time: 10:29 AM
 */

namespace API\STGW;


use Monolog\Logger;

class STGW
{
    const URL = 'https://27.123.214.138:8060/stgw';

    const USERNAME = 'ict_expo_2017';
    const PASSWORD = 'ict_expo_!#2017';

    private $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    private function sendRequest($endPoint, $method, $params = array()) {

        $request_file = __DIR__ . "/../../../log/stgw_curl_err.xml";
        $fp = fopen($request_file, 'w');


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL . $endPoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/xml",
            "Username: " . self::USERNAME,
            "Password: " . self::PASSWORD,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if(strtoupper($method) !== 'GET') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, 'Bkyukliin!1dgaueellGug');
        curl_setopt($ch, CURLOPT_SSLCERT, __DIR__."/pem/mobicommn_private.pem");

//        curl_setopt($ch, CURLOPT_SSLCERT, __DIR__ . "/pem/comforthotel.pem");
//        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
//        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, 'Bibelot!*09201217');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "kiosk-backend/1.0/2017");

        curl_setopt($ch, CURLOPT_STDERR, $fp);


        $result = curl_exec($ch);

        $err = curl_error($ch);

        curl_close($ch);

        if($this->logger) {
            $this->logger->addInfo('STGW', array(
                'URL' => self::URL,
                'endPoint' => $endPoint,
                'method' => $method,
                'params' => $params,
                'response' => $result,
                'error' => $err,
            ));
        }

        return $result;
    }

    public function giveICTDataPackage($isdn, $packageId) {
        $customSms = rawurlencode("Ta #name data bagts avah erxtei bolloo. 592 dugaart #smsname gej ilgeen bagtsaa idevxjuulne uu.");
       var_dump($customSms);
        return $this->sendRequest('/datapackage_ict/sapcextendlistener', 'POST', <<<EOF
            <request>
                <isdn>$isdn</isdn>
                <cmd>free</cmd>
                <package>$packageId</package>
                <customsms>$customSms</customsms>
            </request>
EOF
            );
    }
}