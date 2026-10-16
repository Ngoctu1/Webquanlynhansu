<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Carbon;

class AccountInvitationMail extends Mailable
{
    public function __construct(
        public string $employeeName,
        public string $loginEmail,
        public string $activationUrl,
        public Carbon $expiresAt,
    ) {}

    public function build(): static
    {
        return $this->subject('Kích hoạt tài khoản HRM')->view('emails.account-invitation');
    }
}
