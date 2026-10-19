<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\TenancyServiceProvider::class,       // NUEVO
    App\Providers\Filament\AdminPanelProvider::class,  // NUEVO
    App\Providers\Filament\AppPanelProvider::class,    // NUEVO
];
