<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedEticketMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Booking $booking,
        private readonly string $pdfBinary
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject(__('e_ticket') . ' - ' . $this->booking->booking_code)
            ->view('emails.booking-confirmed')
            ->with([
                'booking' => $this->booking,
            ])
            ->attachData(
                $this->pdfBinary,
                'e-ticket-' . $this->booking->booking_code . '.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
