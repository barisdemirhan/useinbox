<?php

namespace Barisdemirhan\Useinbox;

use Exception;

class UseInbox
{
    protected $accountEmail;
    protected $accountPassword;
    protected $baseUrl;
    protected $client;
    protected $token;
    protected $header;

    public function __construct()
    {
        $this->accountEmail = config('useinbox.account_email');
        $this->accountPassword = config('useinbox.account_password');
        $this->baseUrl = "https://useapi.useinbox.com/";
        $this->client = new \GuzzleHttp\Client(['base_uri' => $this->baseUrl]);
        $this->header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * @throws Exception
     */
    public function getResponse($response)
    {
        try {
            $responseArray = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new Exception($e);
        }
        if ($responseArray['resultStatus']) {
            return [
                'code' => $responseArray['resultCode'],
                'data' => $responseArray['resultObject'],
                'message' => $responseArray['resultMessage'],
            ];
        }

        throw new Exception($responseArray['resultMessage']);
    }

    public function getToken()
    {
        try {
            $request = $this->client->post('token', [
                'headers' => $this->header,
                'body' => json_encode([
                    'EmailAddress' => $this->accountEmail,
                    'Password' => $this->accountPassword,
                ], JSON_THROW_ON_ERROR)
            ]);
        } catch (Exception $e) {
            throw $e;
        }
        $response = $this->getResponse($request->getBody());
        $this->token = [
            'access_token' => $response['data']['access_token'],
            'expires_in' => $response['data']['expires_in'],
            'token_type' => $response['data']['token_type'],
            'refresh_token' => $response['data']['refresh_token'],
        ];
    }

    public function send($body)
    {
        $this->getToken();
        return $this->getResponse($this->client->post('notify/v1/send', [...$body, [
            'headers' =>
                [
                    ...$this->header,
                    'Authorization' => "{$this->token['token_type']} {$this->token['access_token']}"
                ]
        ]])->getBody());
    }
}