<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MyEdi\MySegment;
use App\Services\MyEdi\ProcessSegment;
use App\Services\MyEdi\EdiTranslate;



class MyEdiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(MyEdi::class, function(){
            $segment = new MySegment();
            $process = new ProcessSegment();
            $translate = New EdiTranslate();
            return new MyEdi($segment, $process, $translate);
        });
    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
