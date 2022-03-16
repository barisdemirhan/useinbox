<?php

namespace Barisdemirhan\Useinbox\Transport;

use Barisdemirhan\Useinbox\UseInbox;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;

class UseInboxTransport extends Transport
{

    protected $accountEmail;
    protected $accountPassword;

    public function __construct()
    {
        $this->accountEmail = config('useinbox.account_email');
        $this->accountPassword = config('useinbox.account_password');
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $useinboxClient = new UseInbox();
        try {
            $response = $useinboxClient->send(['body' => $this->getBody($message)]);
        } catch (\Exception $e) {
            throw $e;
        }

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get body for the message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @return array
     */

    protected function getBody(Swift_Mime_SimpleMessage $message)
    {
        return [
            'from' => [
                'email' => config('mail.from.address'),
                'name' => config('mail.from.name')
            ],
            'to' => $this->getTo($message),
            'subject' => $message->getSubject(),
            'htmlContent' => $message->getBody(),
        ];
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @return array
     */
    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? [
                'email' => $address,
                'name' => $display
            ] : [
                'email' => $address,
            ];

        })->values()->toArray();
    }

    /**
     * Get all of the contacts for the message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @return array
     */
    protected function allContacts(Swift_Mime_SimpleMessage $message)
    {
        return array_merge(
            (array)$message->getTo(), (array)$message->getCc(), (array)$message->getBcc()
        );
    }
}
