<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'worker.auth' => \App\Http\Middleware\WorkerAuthMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Monitor system health (dead workers and timeouts) every minute
        $schedule->command('system:monitor-health')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Legacy commands (if they exist)
        if (class_exists(\App\Console\Commands\DetectDeadWorkers::class)) {
            $schedule->command('workers:detect-dead')
                ->everyMinute()
                ->withoutOverlapping();
        }

        if (class_exists(\App\Console\Commands\DetectTimedOutTasks::class)) {
            $schedule->command('tasks:detect-timeout')
                ->everyTwoMinutes()
                ->withoutOverlapping();
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
