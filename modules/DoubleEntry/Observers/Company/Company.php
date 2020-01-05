<?php

namespace Modules\DoubleEntry\Observers\Company;

use App\Models\Company\Company as Model;
use Artisan;

class Company
{
    /**
     * Listen to the created event.
     *
     * @param  Model  $company
     * @return void
     */
    public function created(Model $company)
    {
        // Create seeds
        Artisan::call('doubleentry:seed', [
            'company' => $company->id
        ]);
    }
}