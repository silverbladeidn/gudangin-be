<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ItemRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ItemRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public ItemRequest $itemRequest;

    public function __construct(ItemRequest $itemRequest)
    {
        $this->itemRequest = $itemRequest;
    }

    public function envelope(): Envelope
    {
        Log::info("Mailable: Building envelope");
        return new Envelope(
            subject: 'Item Request Created - ' . $this->itemRequest->request_number,
        );
    }

    public function content(): Content
    {
        Log::info("Mailable: Building content");
        $contentStart = microtime(true);

        $content = new Content(
            view: 'emails.item_request_created',
            with: [
                'itemRequest' => $this->itemRequest,
            ]
        );

        Log::info("Mailable: Content built", ['duration' => round(microtime(true) - $contentStart, 3) . 's']);
        return $content;
    }

    public function attachments(): array
    {
        Log::info("Mailable: Starting PDF generation");
        $pdfStart = microtime(true);

        try {
            $pdf = Pdf::loadView('pdf.item_request', [
                'itemRequest' => $this->itemRequest,
            ]);

            Log::info("Mailable: PDF generated successfully", ['duration' => round(microtime(true) - $pdfStart, 3) . 's']);

            return [
                Attachment::fromData(
                    fn() => $pdf->output(),
                    'item-request-' . $this->itemRequest->request_number . '.pdf'
                )->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            Log::error("Mailable: PDF generation failed", [
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $pdfStart, 3) . 's'
            ]);
            return [];
        }
    }
}
