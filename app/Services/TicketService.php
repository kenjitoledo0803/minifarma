<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Sale;

class TicketService
{
    /**
     * Resolve the ticket configuration for a branch, merging generic
     * "Network" defaults with "Branch" specific overrides.
     */
    public function getTicketConfig(Branch $branch): array
    {
        $defaults = [
            'show_logo' => true,
            'header_text' => "MINI FARMACIA\nwww.minifarmacia.com",
            'footer_text' => "¡Gracias por su compra!\nConserve este ticket para cambios.",
            'paper_size' => '80mm', // 58mm, 80mm, letter
            'show_phone' => true,
            'show_address' => true,
        ];

        // Merge DB JSON config over defaults
        $savedConfig = $branch->ticket_config ?? [];
        
        return array_merge($defaults, $savedConfig);
    }

    /**
     * Generate the HTML view for the ticket.
     * 
     * @param Sale $sale
     * @return \Illuminate\Contracts\View\View
     */
    public function renderTicket(Sale $sale)
    {
        $branch = $sale->branch;
        $config = $this->getTicketConfig($branch);

        $data = [
            'sale' => $sale,
            'items' => $sale->items, // Eager load this in controller
            'branch' => $branch,
            'header' => nl2br($config['header_text']),
            'footer' => nl2br($config['footer_text']),
            'logo' => $config['show_logo'] ? asset('images/logo_default.png') : null,
            'width_css' => $this->getWidthCss($config['paper_size']),
        ];

        return view('tickets.thermal_generic', $data);
    }

    protected function getWidthCss(string $size): string
    {
        return match($size) {
            '58mm' => '58mm',
            '80mm' => '80mm', 
            'letter' => '210mm',
            default => '80mm',
        };
    }
}
