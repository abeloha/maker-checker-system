<?php

namespace App\Mail;

use App\Models\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingRequestEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $changeRequest;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ChangeRequest $changeRequest)
    {
        $this->changeRequest = $changeRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.new-request');
    }
}
