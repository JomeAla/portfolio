<?php
// Simple route add script - add to web.php around line 510

$route_code = '
// Sales Funnels (adding missing routes)
Route::get(\'/marketing/funnels\', [MarketingController::class, \'funnelsIndex\'])->name(\'admin.marketing.funnels\');
Route::get(\'/marketing/funnels/create\', [MarketingController::class, \'funnelsCreate\'])->name(\'admin.marketing.funnels.create\');
Route::post(\'/marketing/funnels\', [MarketingController::class, \'funnelsStore\'])->name(\'admin.marketing.funnels.store\');
Route::get(\'/marketing/funnels/{funnel}/edit\', [MarketingController::class, \'funnelsEdit\'])->name(\'admin.marketing.funnels.edit\');
Route::put(\'/marketing/funnels/{funnel}\', [MarketingController::class, \'funnelsUpdate\'])->name(\'admin.marketing.funnels.update\');
Route::delete(\'/marketing/funnels/{funnel}\', [MarketingController::class, \'funnelsDestroy\'])->name(\'admin.marketing.funnels.destroy\');
Route::post(\'/marketing/funnels/{funnel}/clone\', [MarketingController::class, \'funnelsClone\'])->name(\'admin.marketing.funnels.clone\');
';

echo $route_code;