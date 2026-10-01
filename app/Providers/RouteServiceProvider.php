<?php

namespace App\Providers;

use App\Helpers\Qs;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
     protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //parent::boot();

        Route::bind('id', function($value){
            return Qs::decodeHash($value);
        });

        Route::bind('ttr', function($value){
            // تحقق إذا كان الـ value pure number (ID) أو hashed
            if (is_numeric($value)) {
                // Pure ID
                $model = \App\Models\TimeTableRecord::find($value);
            } else {
                // فك الـ hash أولاً
                $id = Qs::decodeHash($value);
                $model = \App\Models\TimeTableRecord::find($id);
            }
            
            if (!$model) {
                abort(404);
            }
            return $model;
        });

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
    }
}
