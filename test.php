<?php
/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.22.
 * Time: 12:23
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Art4\JsonApiClient\Utils\Manager;

$headers = [
    'Content-Type'  => 'application/json',
    'Accept'        => 'application/prs.company-document.v1+json',
    'X-API-KEY'     => '5b81e6307ff218a6615d4ab78f2d3b51',
];

$client = new Client();
$res = "";
try {
    $res = $client->request('GET', 'http://localhost:8000/api/v1/66572/documentsf', ['headers' => $headers]);
    $manager = new Manager();
    //dd($res->getBody());
    $documents = $manager->parse($res->getBody());

} catch (Exception $e) {
    $z = $e->getMessage();
    //echo $z;
    echo $res;
    //dd($z);

}


$json_string = '{"big_int": 123456789012345678901234567890}';

$options = ( version_compare(PHP_VERSION, '5.4.0', '>=') and ! (defined('JSON_C_VERSION') and PHP_INT_SIZE > 4) ) ? JSON_BIGINT_AS_STRING : 0;
$data = json_decode($json_string, false, 512, $options);

var_dump($data, defined('JSON_C_VERSION'), PHP_INT_SIZE, $options);