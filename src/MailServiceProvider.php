<?php

namespace Barisdemirhan\Useinbox;

use Barisdemirhan\Useinbox\Transport\UseInboxAddedTransportManager;
use Illuminate\Mail\MailServiceProvider as BaseMailServiceProvider;

class MailServiceProvider extends BaseMailServiceProvider
{
    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    protected function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new UseInboxAddedTransportManager($app);
        });
    }
}