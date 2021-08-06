<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $order;
    protected $details;
    protected $customer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $order)
    {
        $this->user = $user;
        $this->order = $order;

        $this->details = $order->order_details()->with('item')->get();
        $this->customer = $order->customer()->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order = $this->order;
        $details = $this->details;
        $customer = $this->customer;
        $totalPrice = collect($details)->sum(function($detail){
           return $detail->item->price * $detail->item_qty;
        });
        return $this->text('mail.purchaseOrder', compact('order', 'details','customer', 'totalPrice'))
            ->subject('ご注文内容の送付（テスト）');
    }
}
