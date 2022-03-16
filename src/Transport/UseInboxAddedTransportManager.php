<?php

namespace Barisdemirhan\Useinbox\Transport;

use Illuminate\Mail\TransportManager;

class UseInboxAddedTransportManager extends TransportManager
{
    protected function createUseinboxDriver()
    {
        return new UseInboxTransport;
    }
}