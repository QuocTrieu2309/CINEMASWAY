<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $barcodeUrl;
    public $cinema;
    public $showDate;
    public $showTime;
    public $seatDetails;
    public $totalAmount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($booking, $barcodeUrl, $cinema, $showDate, $showTime, $seatDetails, $totalAmount)
    {
        $this->booking = $booking;
        $this->barcodeUrl = $barcodeUrl;
        $this->cinema = $cinema;
        $this->showDate = $showDate;
        $this->showTime = $showTime;
        $this->seatDetails = $seatDetails;
        $this->totalAmount = $totalAmount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.booking_mail')
            ->with([
                'barcodeUrl' => $this->barcodeUrl,
                'bookingCode' => $this->booking->code,
                'cinema' => $this->cinema,
                'showDate' => $this->showDate,
                'showTime' => $this->showTime,
                'seatDetails' => $this->seatDetails,
                'totalAmount' => $this->totalAmount,
            ]);
    }
}
