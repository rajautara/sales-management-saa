<?php

namespace App\Livewire\Receipts;

use App\Models\Receipt;
use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Livewire\Traits\HasWhatsappShare;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasWhatsappShare;

    public Receipt $receipt;

    public function mount(Receipt $receipt): void
    {
        $this->receipt = $receipt->load(['payment.invoice.customer', 'payment.invoice.items.product']);
    }

    public function render()
    {
        return view('livewire.receipts.show');
    }
}
