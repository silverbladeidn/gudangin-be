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

class ItemRequestPartialApproved extends Mailable
{
    use Queueable, SerializesModels;

    public ItemRequest $itemRequest;
    public string $status;

    public function __construct(ItemRequest $itemRequest, string $status)
    {
        $this->itemRequest = $itemRequest;
        $this->status = $status;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Item Request ' . ucfirst($this->status) . ' - ' . $this->itemRequest->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.item_request_partially_approved',
            with: [
                'itemRequest' => $this->itemRequest,
                'status'      => $this->status,
            ]
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.item_request_partially_approved', [
            'itemRequest' => $this->itemRequest,
        ]);

        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                'item-request-' . $this->itemRequest->request_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
