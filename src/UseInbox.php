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

    public function refreshToken()
    {
        $request = $this->client->get('token', [
            'EmailAddress' => $this->accountEmail,
            'Password' => $this->accountPassword,
        ]);
        $data = $this->getResponse($request->getBody());
        $this->token = [
            'access_token' => $data['access_token'],
            'expires_in' => $data['expires_in'],
            'token_type' => $data['token_type'],
        ];
        $this->header = [
            'headers' =>
                [
                    'Authorization' => "{$this->token['token_type']} {$this->token['access_token']}"
                ]
        ];
    }

    public function send($body)
    {
        $this->refreshToken();
        return $this->getResponse($this->client->post('notify/v1/send', [...$body, ...$this->header])->getBody());
    }
}
