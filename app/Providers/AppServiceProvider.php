<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\AbstractPaginator;
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
        AbstractPaginator::defaultView('pagination::bootstrap-4');

        Builder::macro('addSubSelect', function ($column, $query) {
            if (is_null($this->getQuery()->columns)) {
                $this->select($this->getQuery()->from.'.*');
            }

            return $this->selectSub($query->limit(1)->getQuery(), $column);
        });
    }
}
