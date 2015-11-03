<?php
namespace History\Entities\Observers;

use History\Entities\Models\Company;

class CompanyObserver
{
    /**
     * @param Company $company
     */
    public function saving(Company $company)
    {
        $company->sluggify();
    }
}
