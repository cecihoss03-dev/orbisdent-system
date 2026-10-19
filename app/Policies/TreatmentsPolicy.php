<?php

namespace App\Policies;

class TreatmentsPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'treatments';
    }
}
