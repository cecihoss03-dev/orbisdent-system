<?php

namespace App\Policies;

class ReportsPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'reports';
    }
}
