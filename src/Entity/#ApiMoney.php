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

    public function getAccount(): array
    {
        $response = $this->client->request(
            "GET",
            self::API_BASE_URI . "/accounts",
            array("headers"  => $this->getHeaders())
        );

        return $response->toArray();
    }

    /**
     * TODO:: -error dans creation d'account: si l'utilisateur ne choisis pas type -> error
     * TODO:-pour adress country que la France est acceptée (peut etre UK aussi)
     *
     * @param User $user
     * @return void
     */
    public function setStandardAccount(User $user)
    {
        $body = array(
            "subscriber" => array(
                "lastname" => $user->getLastName(),
                "firstname" => $user->getFirstName(),
                "birthdate" => $user->getBirthDateString(),
                "birth_country" => $user->getBirthCountryISO(),    // FRA
                "birth_city" => $user->getBirthCity(),
                "nationality" => $user->getNationalityISO(),       //FRA 
                "citizen_us" => $user->getCitiZenUs(),
                "fiscal_us" => $user->getFiscalUs(),
                "fiscal_out_france" => $user->getFiscalOutFrance()
            ),
            "address" => array(
                "label1" => $user->getAddressLabel1(),
                "zip_code" => $user->getAddressZipCode(),
                "city" => $user->getAddressCity(),
                "country" => $user->getAddressCountryISO()     //FRA
            ),
            "email" => $user->getEmail(),
            "tag" => $user->getTypeAccountString()       //account_type1
            //"tag" => $user->getTag()        //account_type1
            //"tag" => $user->getTag()        //account_type1
        );


        $response = $this->client->request("POST", self::API_BASE_URI . "/accounts/standard", [
            "headers" => $this->getHeaders($body),
            "json" => $body
        ]);

        return $response;
    }

    /**
     * TODO: Voir ci-dessus les problèmes à régler
     *
     * @param User $user
     * @return void
     */
    public function setBusinessAccount(User $user)
    {

        $body = array(
            "name" => $user->getCompanyName(),
            "business_type" => "COMPANY",
            "email" => $user->getEmail(),
            "registration_number" => $user->getCompanyCRN(), //unique!
            //"registration_number" =>"1208fd597933941561", //unique!
            "phone_number" => $user->getPhoneFixed(),
            "representative" => array(
                "lastname" => $user->getLastName(),
                "firstname" => $user->getFirstName(),
                "birthdate" => $user->getBirthDateString(),
                "nationality" => $user->getNationalityISO()
            ),
            "address" => array(
                "label1" => $user->getAddressLabel1(),
                "zip_code" => $user->getAddressZipCode(),
                "city" => $user->getAddressCity(),
                "country" => $user->getAddressCountryISO()
            ),
            "tag" => $user->getTypeAccountString()       //account_type1

        );

        //dd($body);
        $response = $this->client->request("POST", self::API_BASE_URI . "/accounts/business", [
            "headers" => $this->getHeaders($body),
            "json" => $body
        ]);

        return $response;
    }
    // methode for getting an account response
    public function getAnAccount($refAccount): array
    {
        $response = $this->client->request(
            "GET",
            self::API_BASE_URI . "/accounts/" . $refAccount,
            array("headers" => $this->getHeaders())
        );
        return $response->toArray();
    }

    public function setWallet(string $account)
    {
        $body = array(
            "account_id" => $account,
            "type" => "EMONEY"
        );

        $response = $this->client->request("POST", self::API_BASE_URI . "/wallets", [
            "headers" => $this->getHeaders($body),
            "json" => $body
        ]);

        return $response;
    }

    //function for creating a single-step transfer 
    public function createSingleTransfer(Transaction $transaction)
    {
        $timestamp = (string) time() * 1000;


        $body = array(
            "partner_ref" => "part" . $timestamp,
            "tag" => $transaction->getMessage(),
            //"sender_wallet_id" => $transaction->getSenderWallet()->getAccount()->getWalletApiMoney(),
            "sender_wallet_id" => $transaction->getSenderWallet()->getAccount()->getWalletApiMoney(),
            "receiver_wallet_id" => $transaction->getReceiverWallet()->getAccount()->getWalletApiMoney(),
            "fees_wallet_id" => $transaction->getfeeWallet()->getWallet(),
            "amount" => (string)$transaction->getAmount(),
            "fees" => (string)$transaction->getfeeWallet()->getPrice()
        );


        $response = $this->client->request("POST", self::API_BASE_URI . "/transfers", [
            "headers" => $this->getHeaders($body),
            "json" => $body
        ]);

        return $response;
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
            "partner_ref" => "partner_ref" . $timestamp,
            "tag" => $transaction->getMessage(),
            "receiver_wallet_id" => $transaction->getReceiverWallet()->getAccount()->getWalletApiMoney(),
            "fees_wallet_id" =>  $transaction->getfeeWallet()->getWallet(),
            "amount" => $amount,
            "fees" => (string)$transaction->getfeeWallet()->getPrice(),
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

    /**
     * TODO: verifier avec CB
     */
    public function setSingleCashInCreditCards(Transaction $transaction)
    {

        $timestamp = (string) time() * 1000;
        $amount = $transaction->getAmount();
        if ($transaction->getAmount() == \floor($transaction->getAmount())) {
            $amount = (int)$transaction->getAmount();
        }

        $ccId = "CC-0126075438926055";
        $body = array(
            "partner_ref" => "partner_ref" . $timestamp,
            "payment_method" => "CREDIT_CARD",
            "receiver_wallet_id" => $transaction->getReceiverWallet()->getAccount()->getWalletApiMoney(),
            "fees_wallet_id" =>  $transaction->getfeeWallet()->getWallet(),
            "amount" => $amount,
            "fees" => (string)$transaction->getfeeWallet()->getPrice(),
        );

        $response = $this->client->request("POST", self::API_BASE_URI . "/cash-in/creditcards/" . $ccId, [
            "headers" => $this->getHeaders($body),
            "json" => $body
        ]);

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
