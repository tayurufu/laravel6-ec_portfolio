<?php

namespace App\Listeners;

use App\Events\PurchaseEvent;
use App\Mail\PurchaseOrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPurchaseOrderEmailListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PurchaseEvent  $event
     * @return void
     */
    public function handle(PurchaseEvent $event)
    {
        Mail::to($event->user->email)->send(new PurchaseOrderMail($event->user, $event->order));
    }
}
