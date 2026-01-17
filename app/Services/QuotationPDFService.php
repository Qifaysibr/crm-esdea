<?php

namespace App\Services;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationPDFService
{
    /**
     * Generate 3-page PDF for quotation
     */
    public function generate(Quotation $quotation)
    {
        $data = [
            'quotation' => $quotation,
            'items' => $quotation->items,
            'creator' => $quotation->creator,
        ];

        $pdf = Pdf::loadView('pdf.quotation', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf;
    }

    /**
     * Download PDF
     */
    public function download(Quotation $quotation)
    {
        $pdf = $this->generate($quotation);
        $filename = $this->getFilename($quotation);
        
        return $pdf->download($filename);
    }

    /**
     * Stream PDF (preview in browser)
     */
    public function stream(Quotation $quotation)
    {
        $pdf = $this->generate($quotation);
        $filename = $this->getFilename($quotation);
        
        return $pdf->stream($filename);
    }

    /**
     * Save PDF to storage
     */
    public function save(Quotation $quotation, $path = 'quotations')
    {
        $pdf = $this->generate($quotation);
        $filename = $this->getFilename($quotation);
        
        $fullPath = storage_path("app/public/{$path}/{$filename}");
        
        // Create directory if not exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        
        $pdf->save($fullPath);
        
        return "{$path}/{$filename}";
    }

    /**
     * Get filename for PDF
     */
    private function getFilename(Quotation $quotation)
    {
        $number = str_replace('/', '-', $quotation->quotation_number);
        return "Quotation-{$number}.pdf";
    }
}
