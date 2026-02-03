<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\JobVacancy;

class RunDailyTask
{
    public function handle($request, Closure $next)
    {
        //info('RunDailyTask middleware triggered.');

        $lastRun = Cache::get('daily_task_last_run');

        if (!$lastRun || Carbon::parse($lastRun)->lt(Carbon::today())) {
            // 🔥 Your 12mn logic here:
            JobVacancy::where('closing_date', '<', Carbon::now())
                ->where('status', 'OPEN')
                ->update(['status' => 'CLOSED']);

            // update cache timestamp
            Cache::put('daily_task_last_run', Carbon::now());

            info('RunDailyTask!');
        }

        //info('daily task executed.');
        return $next($request);
    }
}
