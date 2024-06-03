<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Voucher;
use App\Models\UserVoucher;

class BirthdayVoucher extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $voucher = Voucher::where('type',Voucher::TYPE_BIRTHDAY )->where('status',Voucher::STATUS_ACTIVE)->first();
        if (!$voucher) {
            return false;
        }
        $voucherID = $voucher->id;
        $voucherCode = $voucher->code;
        do {
            $pin = rand(100001, 999999);
            $checkPin = UserVoucher::where('pin', $pin)->where('voucher_id', $voucherID)->first();
        } while ($checkPin);
        $userVoucher = UserVoucher::query()->create([
            'user_id' => $this->user->id,
            'voucher_id' => $voucherID,
            'pin' => $pin,
            'status' => UserVoucher::STATUS_INACTIVE
        ]);
        if ($userVoucher) {
            return $this->view('emails.birthday_voucher')
                ->with([
                    'name' => $this->user->name,
                    'voucherCode' =>  $voucherCode,
                    'pinCode' => $userVoucher->pin
                ]);
        }
        return false;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lời chúc sinh nhật tới từ Cinema Go',
        );
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
