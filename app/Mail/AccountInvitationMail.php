<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AccountInvitationMail extends Mailable
{
    public function __construct(
        public string $employeeName,
        public string $username,
        public string $activationUrl,
        public string $expiresAt,
    ) {
    }

    public function build(): static
    {
        return $this->subject('Kích hoạt tài khoản hệ thống quản lý nhân sự')
            ->view('emails.account-invitation');
    }
}
