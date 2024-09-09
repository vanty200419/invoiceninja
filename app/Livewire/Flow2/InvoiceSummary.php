<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2024. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Livewire\Flow2;

use App\Utils\Traits\WithSecureContext;
use Livewire\Attributes\On;
use Livewire\Component;

class InvoiceSummary extends Component
{
    use WithSecureContext;

    public $invoices;

    public function mount()
    {
        //@TODO for a single invoice - show all details, for multi-invoices, only show the summaries
        $this->invoices = $this->getContext()['invoices']; // $this->context['invitation']->invoice;
    }

    #[On(self::CONTEXT_UPDATE)]
    public function onContextUpdate(): void
    {
        // refactor logic for updating the price for eg if it changes with under/over pay

        $this->invoices = $this->getContext()['invoices'];
    }

    public function render(): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        return render('flow2.invoices-summary', [
            'invoice' => $this->invoices,
            'client' => $this->invoices->first()->client,
        ]);

    }
}