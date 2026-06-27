<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseReceiptController extends Controller
{
    /**
     * Stream an expense receipt to the current user.
     *
     * The Expense is resolved via implicit route-model binding, so the
     * BelongsToCompany global scope already restricts it to the authenticated
     * user's company — a user cannot fetch another tenant's receipt by id.
     */
    public function show(Expense $expense): StreamedResponse
    {
        abort_if(blank($expense->receipt_attachment), 404);

        // Receipts are stored on the private 'local' disk. Fall back to the
        // legacy 'public' disk for records created before this change.
        $disk = Storage::disk('local')->exists($expense->receipt_attachment)
            ? 'local'
            : 'public';

        abort_unless(Storage::disk($disk)->exists($expense->receipt_attachment), 404);

        return Storage::disk($disk)->response($expense->receipt_attachment);
    }
}
