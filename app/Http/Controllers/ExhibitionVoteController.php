<?php

namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ExhibitionApplication;
use App\Models\ExhibitionVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExhibitionVoteController extends Controller
{
    public function store(
        Request $request,
        Exhibition $exhibition,
        ExhibitionApplication $application
    ): RedirectResponse {
        try {
            DB::transaction(function () use (
                $request,
                $exhibition,
                $application
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
                        'Voting is only available while the exhibition is open for visits.'
                    );
                }

                if ((int) $lockedApplication->user_id === (int) $request->user()->id) {
                    throw new RuntimeException(
                        'You cannot vote for your own exhibition artwork.'
                    );
                }

                if (ExhibitionVote::query()
                    ->where('exhibition_application_id', $lockedApplication->id)
                    ->where('user_id', $request->user()->id)
                    ->exists()) {
                    throw new RuntimeException(
                        'You have already voted for this artwork.'
                    );
                }

                ExhibitionVote::create([
                    'exhibition_application_id' => $lockedApplication->id,
                    'user_id' => $request->user()->id,
                ]);
            });
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Your vote has been recorded.');
    }
}
