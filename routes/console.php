<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\ExhibitionAwardService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command(
    'exhibitions:settle-closed',
    function (ExhibitionAwardService $awardService): int {
        $settledCount = $awardService->settleAllClosedExhibitions();
        $this->info("Automatically credited awards for {$settledCount} closed exhibition(s).");

        return self::SUCCESS;
    }
)->purpose('Automatically credit prizes and commission for closed exhibitions');

Schedule::command('exhibitions:settle-closed')
    ->everyMinute()
    ->withoutOverlapping();
