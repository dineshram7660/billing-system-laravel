<?php

namespace App\Mail;

use App\Exports\EstimateItemsExport;
use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Rebuilds estimate_mail.php's PDF+Excel attachment email. Legacy also
 * offered an optional measurement-sheet PDF attachment
 * (create_estimate_measurement_pdf) — not ported, since the measurement
 * print view itself hasn't been built yet; see the README.
 */
class EstimateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Estimate $estimate,
        private readonly string $clientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bhavani Engineering Estimate - {$this->estimate->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.estimate',
            with: ['clientName' => $this->clientName],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $estimate = $this->estimate;

        return [
            Attachment::fromData(
                fn () => Pdf::loadView('estimates.pdf', $this->pdfData())->output(),
                'Estimate Bhavani Engineering.pdf',
            )->withMime('application/pdf'),

            Attachment::fromData(
                fn () => Excel::raw(new EstimateItemsExport($estimate), \Maatwebsite\Excel\Excel::XLSX),
                'Estimate Bhavani Engineering.xlsx',
            )->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }

    /**
     * @return array{estimate: Estimate, cgst: float, sgst: float, grandTotal: float}
     */
    private function pdfData(): array
    {
        $estimate = $this->estimate;
        $estimate->loadMissing('items');

        $cgst = round((float) $estimate->total * config('company.cgst_rate') / 100);
        $sgst = round((float) $estimate->total * config('company.sgst_rate') / 100);
        $grandTotal = round((float) $estimate->total + $cgst + $sgst);

        return [
            'estimate' => $estimate,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'grandTotal' => $grandTotal,
        ];
    }
}
