<?php

namespace App\Policies;

class AppointmentsPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'appointments';
    }
}
