<?php

namespace App\Providers;

use App\Models\Addresses;
use App\Models\Plans;
use App\Models\Testimony;
use App\Models\Utilities;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('convert', function ($value) {
            return "<?php echo number_format($value, 2); ?>";
        });


        view()->composer('*', function($view){
            $view->with('plans', Plans::orderBy('id', 'asc')->get());

            $view->with('utilities', Utilities::orderBy('id', 'desc')->get());

            $view->with('address', Addresses::orderBy('id', 'desc')->get());

            $view->with('testimony', Testimony::orderBy('id', 'desc')->get());

        });

    }
}
