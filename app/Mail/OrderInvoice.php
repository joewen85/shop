<?php

namespace app\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderInvoice extends Mailable
{
    use Queueable, SerializesModels;



    protected $data = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($imagePath)
    {
        $this->data['imagePath'] = $imagePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('emails.orderInvoice', ['data' =>$this->data]);
    }
}
