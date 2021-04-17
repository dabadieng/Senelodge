<?php

/*
 * lucie1.lemagnen.ext@orange.com
 * 0661166995
 *
 * A secret shared key (provided by W-HA) called “api_secret_key” is used to compute the hash.
 * StringToSign = api_access_key:timestamp:version:request_body
 * Sign = hash_hmac(StringToSign, api_secret_key)
 */

//j'ai pas de conflits



namespace App\Entity;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ApiMoney
{
    private $client;
    private const API_SECRET_KEY = "u3AoGEpfr7PNbZA0LXzkp2NABAXeuElA";
    private const API_ACCESS_KEY = "O6JQciqZda6VFS0Sq4maCWyU5HgR3WuI";
    private const API_BASE_URI = "https://test-emoney-services.w-ha.com/api";
    private const VERSION = 1;
    private const REDIRECT = "https://18a5dd375d16.eu.ngrok.io/home_after_cashin";


    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    private function getHeaders(array $body = []): array
    {
        return [
            "Content-Type" => "application/json",
            "Authorization" => "AUTH " . $this->getSignature($body)
        ];
    }

    private function getSignature(array $body): string
    {
        $timestamp = (string)(time() * 1000);

        $StringToSign = self::API_ACCESS_KEY . ":" . $timestamp . ":" . self::VERSION . ":";
        if (!empty($body)) {  // When the query contains a body, we should add it as json encoded string
            $StringToSign .= json_encode($body);
        }
        $sign = hash_hmac("sha256",  $StringToSign, self::API_SECRET_KEY);

        $auth = self::API_ACCESS_KEY . ":" . $timestamp . ":" . self::VERSION . ":" . $sign;

        return $auth;
    }



    // recover all information about a transaction 
    public function getTransaction($idTransaction)
    {
        $response = $this->client->request(
            "GET",
            self::API_BASE_URI . "/transactions/$idTransaction",
            array("headers" => $this->getHeaders())
        );
        return $response->toArray();
    }

    /**
     * TODO faire la redirection_url + lang
     */
    public function setCashInCreditCards(Transaction $transaction)
    {

        $token = md5(uniqid());
        $transaction->getSenderWallet()->setToken($token);
        $return_url = self::REDIRECT . "/" . $token;

        $timestamp = (string) time() * 1000;
        //Convertir un nombre float sans décimaie en un integer exigeance d'api money; 
        $amount = $transaction->getAmount();
        if ($transaction->getAmount() == \floor($transaction->getAmount())) {
            $amount = (int)$transaction->getAmount();
        }

        $body = array(
            "partner_ref" => "senelodge" . $timestamp,
            "tag" => $transaction->getMessage(),
            "receiver_wallet_id" => "WE-5245865150655766",
            "fees_wallet_id" =>  "",
            "amount" => $amount,
            "fees" => "0",
            "return_url" => $return_url,
            //TODO: 
            "lang" => "fr",
            "auth_timeout_delay" => 86400
        );

        $response = $this->client->request("POST", self::API_BASE_URI . "/cash-in/creditcards/init", [
            "headers" => $this->getHeaders($body),
            "json" => $body
        ]);

        return $response;
    }

    public function GetWalletStatistics()
    {
        $response = $this->client->request(
            "GET",
            self::API_BASE_URI . "/wallets/WE-2666409583996192/statistics??start_date=1577840400000&end_date=1609462800000",
            array("headers" => $this->getHeaders())
        );
        return $response;
    }

    public function getListTransaction($wallet)
    {
        $response = $this->client->request(
            "GET",
            self::API_BASE_URI . "/transactions?wallet_id=$wallet",
            array("headers" => $this->getHeaders())
        );
        return $response->toArray();
    }
    public function getSolde($wallet)
    {
        $response = $this->client->request(
            "GET",
            self::API_BASE_URI . "/wallets/$wallet/balance",
            array("headers" => $this->getHeaders())
        );
        return $response->toArray();
    }

    //TODO
    // function wich allow customer to recover her/his invoice
    public function getaPDFReceipt()
    {
        return $this->client->request(
            "GET",
            self::API_BASE_URI . "/transactions/TX-8635540932926963/receipt/fr",
            array("headers" => $this->getHeaders())
        );
    }

    public function getConfirmCashIn($idTranssction)
    {
        $response = $this->client->request(
            "PUT",
            self::API_BASE_URI . "/cash-in/" . $idTranssction,
            array("headers" => $this->getHeaders())
        );
        return $response->getStatusCode();
    }
}
