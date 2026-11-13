<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\ExhibitionScore;
use App\Services\ExhibitionAwardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class CoAdminExhibitionController extends Controller
{
    public function index(): View
    {
        app(ExhibitionAwardService::class)->settleAllClosedExhibitions();

        return view('coadmin.exhibitions.index', [
            'exhibitions' => Exhibition::query()
                ->where('status', 'published')
                ->withCount([
                    'applications as participant_count' => fn ($query) => $query
                        ->where('status', 'confirmed'),
                ])
                ->orderByDesc('starts_at')
                ->get(),
        ]);
    }

    public function show(Exhibition $exhibition, Request $request): View
    {
        app(ExhibitionAwardService::class)->settleAllClosedExhibitions();

        abort_unless($exhibition->status === 'published', 404);
        abort_unless($exhibition->visitHasStarted(), 404);

        $applications = $exhibition->applications()
            ->where('status', 'confirmed')
            ->with(['artist.artistProfile'])
            ->withCount('votes')
            ->with([
                'scores' => fn ($query) => $query
                    ->where('co_admin_id', $request->user()->id),
            ])
            ->orderBy('id')
            ->get();

        return view('coadmin.exhibitions.show', compact(
            'exhibition',
            'applications'
        ));
    }

    public function score(
        Request $request,
        Exhibition $exhibition,
        ExhibitionApplication $application
    ): RedirectResponse {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:1,10'],
        ]);

        try {
            DB::transaction(function () use (
                $request,
                $exhibition,
                $application,
                $validated
            ): void {
                $lockedExhibition = Exhibition::query()
                    ->whereKey($exhibition->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedApplication = ExhibitionApplication::query()
                    ->whereKey($application->id)
                    ->where('exhibition_id', $lockedExhibition->id)
                    ->where('status', 'confirmed')
                    ->firstOrFail();

                if (! $lockedExhibition->visitIsOpen()) {
                    throw new RuntimeException(
                        'Co-admin scoring is only available while the exhibition is open for visits.'
                    );
                }

                ExhibitionScore::updateOrCreate(
                    [
                        'exhibition_application_id' => $lockedApplication->id,
                        'co_admin_id' => $request->user()->id,
                    ],
                    ['score' => $validated['score']]
                );
            });
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Artwork score saved.');
    }
}
