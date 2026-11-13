<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\ExhibitionSettlement;
use App\Models\ArtistWalletTransaction;
use App\Services\ExhibitionAwardService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminExhibitionController extends Controller
{
    public function index(): View
    {
        app(ExhibitionAwardService::class)->settleAllClosedExhibitions();

        $totalExhibitionIncome = ExhibitionApplication::query()
            ->where('payment_status', 'verified')
            ->sum('registration_fee');
        $totalArtfyExhibitionCommission = ExhibitionSettlement::query()
            ->sum('admin_fund_amount');

        $exhibitions = Exhibition::query()
            ->with('settlement')
            ->withCount([
                'applications as reserved_artists_count' => fn ($query) => $query
                    ->reservesArtistPlace(),
                'applications as enrolled_artists_count' => fn ($query) => $query
                    ->reservesArtistPlace(),
                'applications as pending_payment_count' => fn ($query) => $query
                    ->activePaymentReservation(),
            ])
            ->withSum([
                'applications as income_total' => fn ($query) => $query
                    ->where('payment_status', 'verified'),
            ], 'registration_fee')
            ->withSum([
                'walletTransactions as prize_credits_total' => fn ($query) => $query
                    ->where('type', 'exhibition_prize'),
            ], 'amount')
            ->latest()
            ->paginate(12);

        return view('admin.exhibitions.index', compact(
            'exhibitions',
            'totalExhibitionIncome',
            'totalArtfyExhibitionCommission'
        ));
    }

    public function create(): View
    {
        return view('admin.exhibitions.form', [
            'exhibition' => new Exhibition,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['cover_image'] = $request->file('cover_image')?->store('exhibitions', 'public');
        $slug = Str::slug($data['title']);

        if ($slug === '') {
            $slug = 'exhibition-'.Str::lower(Str::random(8));
        }

        if (Exhibition::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(6));
        }

        Exhibition::create([...$data, 'slug' => $slug]);

        return redirect()->route('admin.exhibitions.index')
            ->with('success', 'Exhibition created.');
    }

    public function edit(Exhibition $exhibition): View
    {
        return view('admin.exhibitions.form', compact('exhibition'));
    }

    public function update(
        Request $request,
        Exhibition $exhibition
    ): RedirectResponse {
        $data = $this->validatedData($request);
        $previousCover = $exhibition->cover_image;
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('exhibitions', 'public');
        } else {
            unset($data['cover_image']);
        }
        $capacityError = DB::transaction(function () use (
            $exhibition,
            $data
        ): ?string {
            $lockedExhibition = Exhibition::query()
                ->whereKey($exhibition->id)
                ->lockForUpdate()
                ->firstOrFail();

            $reserved = $lockedExhibition->applications()
                ->reservesArtistPlace()
                ->count();

            if ($data['max_artists'] < $reserved) {
                return "Capacity cannot be lower than the {$reserved} current reservations.";
            }

            $lockedExhibition->update($data);

            return null;
        });

        if ($capacityError !== null) {
            if (isset($data['cover_image'])) {
                Storage::disk('public')->delete($data['cover_image']);
            }
            return back()->withInput()->withErrors([
                'max_artists' => $capacityError,
            ]);
        }

        if ($request->hasFile('cover_image') && $previousCover) {
            Storage::disk('public')->delete($previousCover);
        }

        return redirect()->route('admin.exhibitions.index')
            ->with('success', 'Exhibition updated.');
    }

    public function applications(Exhibition $exhibition): View
    {
        app(ExhibitionAwardService::class)->settleAllClosedExhibitions();

        $applications = $exhibition->applications()
            ->with(['artist.artistProfile'])
            ->withCount('votes')
            ->withSum('scores', 'score')
            ->latest()
            ->paginate(20);

        $confirmedIncome = $exhibition->applications()
            ->where('payment_status', 'verified')
            ->sum('registration_fee');
        $enrolledCount = $exhibition->applications()
            ->reservesArtistPlace()
            ->count();
        $pendingPaymentCount = $exhibition->applications()
            ->activePaymentReservation()
            ->count();
        $reservedCount = $exhibition->applications()
            ->reservesArtistPlace()
            ->count();
        $settlement = $exhibition->settlement;
        $adminFundTotal = ExhibitionSettlement::query()
            ->sum('admin_fund_amount');
        $prizeCredits = ArtistWalletTransaction::query()
            ->whereIn('exhibition_application_id', $exhibition->applications()
                ->select('id'))
            ->where('type', 'exhibition_prize')
            ->get()
            ->keyBy('exhibition_application_id');
        $prizeCreditsTotal = $prizeCredits->sum(
            fn (ArtistWalletTransaction $transaction): float => (float) $transaction->amount
        );

        return view('admin.exhibitions.applications', compact(
            'exhibition',
            'applications',
            'confirmedIncome',
            'enrolledCount',
            'pendingPaymentCount',
            'reservedCount',
            'settlement',
            'adminFundTotal',
            'prizeCredits',
            'prizeCreditsTotal'
        ));
    }

    public function finalizeAwards(
        Request $request,
        Exhibition $exhibition,
        ExhibitionAwardService $awardService
    ): RedirectResponse {
        try {
            $awardService->settle($exhibition, (int) $request->user()->id);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route(
            'admin.exhibitions.applications',
            $exhibition
        )->with('success', 'Exhibition awards finalized and artist prize balances credited.');
    }

    private function validatedData(Request $request): array
    {
        $request->merge([
            'accent_color' => $request->input('accent_color') ?: '#55613a',
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'theme' => ['nullable', 'string', 'max:180'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['required', 'string', 'max:10000'],
            'registration_opens_at' => ['required', 'date'],
            'registration_closes_at' => [
                'required',
                'date',
                'after_or_equal:registration_opens_at',
            ],
            'starts_at' => [
                'required',
                'date',
                'after:registration_closes_at',
            ],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'max_artists' => ['required', 'integer', 'min:1', 'max:10000'],
            'registration_fee' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999.99',
            ],
            'status' => ['required', 'in:draft,published'],
        ]);
    }
}
