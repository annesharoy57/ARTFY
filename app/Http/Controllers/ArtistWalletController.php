<?php

namespace App\Http\Controllers;

use App\Services\ExhibitionAwardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtistWalletController extends Controller
{
    public function index(Request $request): View
    {
        app(ExhibitionAwardService::class)->settleAllClosedExhibitions();

        $transactions = $request->user()
            ->artistWalletTransactions()
            ->with('exhibition')
            ->latest()
            ->get();
        $balance = $transactions->sum(
            fn ($transaction): float => (float) $transaction->amount
        );

        return view('artfy.artist-wallet', compact(
            'transactions',
            'balance'
        ));
    }
}
