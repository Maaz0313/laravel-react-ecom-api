<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp; // Property to hold the OTP value

    public function __construct($otp)
    {
        $this->otp = $otp; // Initialize the OTP property
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->view('emails.otp') // Specify the view to be used for the email
        ->subject('Your OTP Code for Email Verification') // Set the email subject
        ->with(['otp' => $this->otp]); // Pass the OTP value to the email view
    }
}
