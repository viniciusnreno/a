<?php
namespace App\Libraries;

use GuzzleHttp;
use Ramsey\Uuid\Uuid;

class PicPay {

    private $client = null;
    private $clientID = null;
    private $clientSecret = null;

    public function __construct(){
        $this->clientID = env('PICPAY_CLIENT_ID');
        $this->clientSecret = env('PICPAY_CLIENT_SECRET');

        $this->client = new GuzzleHttp\Client(['base_uri' => env('PICPAY_DOMAIN')]);
    }
    
    public function getToken(){
        $hasToken = false;
        if($hasToken === false){
            $token = $this->getNewToken();
        } else {
            $token = $this->getRefreshToken();
        }

        return $token;
    }

    private function getNewToken(){
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        
        try {
            $response = $this->client->request('POST', '/oauth2/token',[
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientID,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'openid b2p.transfer'
                ]
            ]);
        } catch(Exception $e){
            var_dump($e);
        }

        return json_decode($response->getBody()->getContents());
    }

    private function getRefreshToken(){
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        
        try {
            $response = $this->client->request('POST', '/oauth2/token', $headers, [
                'data' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->clientID,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $refreshToken
                ]
            ]);
        } catch(Exception $e){
            var_dump($e);
        }

        return json_decode($response->getBody()->getContents());
    }

    public function createProject(){

        $token = $this->getToken();
        // print json_encode($token);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Bearer {$token->access_token}"
        ];

        try {
            $response = $this->client->request('POST', '/b2p/v2/projects', [
                'headers' => $headers,
                'form_params' => [
                    'name' => 'P. Fome de Aprender',
                    'description' => 'Projeto Panco - Fome de Aprender',
                    'started_at' => null,
                    'ended_at' => null,
                    'withdrawable' => false,
                    'payee_transaction_limit' => 5,
                    'payee_transaction_value' => 500,
                    'identical_transaction_rule' => false
                ]
            ]);
        } catch(Exception $e){
            return $e;
        }
        return $response;


    }

    public function projects(){

        $token = $this->getToken();
        // print json_encode($token);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Bearer {$token->access_token}"
        ];

        try {
            $response = $this->client->request('GET', '/b2p/v2/projects', [
                'headers' => $headers
            ]);
        } catch(Exception $e){
            return $e;
        }
        return $response;


    }


    public function transfer($consumer, $value, $notWithDrawable = false){
        $token = $this->getToken();
        // print json_encode($token);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => "Bearer {$token->access_token}"
        ];

        try {
            $response = $this->client->request('POST', '/v1/b2p/transfer', [
                'headers' => $headers,
                'form_params' => [
                    'consumer' => $consumer,
                    'value' => $value,
                    'reference_id' => Uuid::uuid1()
                    // 'not_withdrawable' => $notWithDrawable
                ]
            ]);
        } catch(Exception $e){
            return $e;
        }
        return $response;
    }
}