<?php

namespace App\Policies;

class PatientsPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'patients';
    }
}
