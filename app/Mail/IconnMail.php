<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IconnMail extends Mailable
{
    use Queueable, SerializesModels;


    private $_data;
    private $_subject;
    private $_view;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $subject, $view)
    {
        $this->_data = $data;
        $this->_subject = $subject;
        $this->_view = $view;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */

    public function build()
    {
        return $this->from('no-reply@hrd-ihs.com')->subject($this->_subject)->view($this->_view, $this->_data);
        // return $this->from(env('MAIL_FROM_ADDRESS'))->subject($this->_subject)->view($this->_view, $this->_data);
    }

    // public function envelope()
    // {
    //     return new Envelope(
    //         subject: 'Iconn Mail',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  *
    //  * @return \Illuminate\Mail\Mailables\Content
    //  */
    // public function content()
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array
    //  */
    // public function attachments()
    // {
    //     return [];
    // }
}
