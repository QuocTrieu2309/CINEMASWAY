<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class VoucherMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $voucher = Voucher::where('type',Voucher::TYPE_HOLIDAY )->where('status',Voucher::STATUS_ACTIVE)->first();
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
            return $this->view('emails.holiday_voucher')
                ->with([
                    'name' => $this->user->full_name,
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
            subject: 'Lời chúc ngày lễ tới từ Cinema Go',
        );
    }
}
