<?php

namespace App\Policies;

class InventoryPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'inventory';
    }
}
