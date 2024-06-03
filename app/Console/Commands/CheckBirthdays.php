<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\BirthdayVoucher;

class CheckBirthdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:birthdays';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for users with birthdays today and send voucher emails';
    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $today = now()->format('m-d');
        $users = User::whereRaw('DATE_FORMAT(birthday, "%m-%d") = ?', [$today])->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new BirthdayVoucher($user));
            $this->info('Voucher sent to ' . $user->email);
        }
    }
}
