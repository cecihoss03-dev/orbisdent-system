<?php

namespace App\Policies;

class OdontogramPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'odontogram';
    }
}
