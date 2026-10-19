<?php

namespace App\Policies;

class InvoicesPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'invoices';
    }
}
