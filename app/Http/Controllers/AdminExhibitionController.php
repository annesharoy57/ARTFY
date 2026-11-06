<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminExhibitionController extends Controller
{
    public function index(): View
    {
        $totalExhibitionIncome = ExhibitionApplication::query()
            ->where('payment_status', 'verified')
            ->sum('registration_fee');

        $exhibitions = Exhibition::query()
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
            ->latest()
            ->paginate(12);

        return view('admin.exhibitions.index', compact(
            'exhibitions',
            'totalExhibitionIncome'
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
            return back()->withInput()->withErrors([
                'max_artists' => $capacityError,
            ]);
        }

        return redirect()->route('admin.exhibitions.index')
            ->with('success', 'Exhibition updated.');
    }

    public function applications(Exhibition $exhibition): View
    {
        $applications = $exhibition->applications()
            ->with(['artist.artistProfile'])
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

        return view('admin.exhibitions.applications', compact(
            'exhibition',
            'applications',
            'confirmedIncome',
            'enrolledCount',
            'pendingPaymentCount',
            'reservedCount'
        ));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:220'],
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
