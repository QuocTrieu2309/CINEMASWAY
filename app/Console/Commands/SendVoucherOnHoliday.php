<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VoucherMail;
use Carbon\Carbon;

class SendVoucherOnHoliday extends Command
{
    protected $signature = 'voucher:send-on-holiday';
    protected $description = 'Send vouchers to all users on holidays';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $holidays = [
            '06-14',
            '12-25', // Giáng sinh
            '01-01', // Năm mới
            '04-30', // Ngày giải phóng miền Nam
            '02-09', // Quốc khánh
        ];
        $today = Carbon::today()->format('m-d');
        if (in_array($today, $holidays)) {
            User::where('status', User::STATUS_ACTIVE)->chunk(100, function ($users) {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)->send(new VoucherMail($user));
                        $this->info('Voucher sent to ' . $user->email);
                    } catch (\Exception $e) {
                        $this->error('Failed to send voucher to ' . $user->email . '. Error: ' . $e->getMessage());
                    }
                }
            });
        } else {
            return false;
        }
    }
}
