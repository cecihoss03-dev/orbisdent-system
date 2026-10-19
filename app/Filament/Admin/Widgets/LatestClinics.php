<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestClinics extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimas clínicas registradas')
            ->query(fn () => Tenant::query()->with('plan')->latest()->limit(8))
            ->columns([
                TextColumn::make('business_name')->label('Clínica'),
                TextColumn::make('plan.name')->label('Plan')->placeholder('—'),
                TextColumn::make('phone')->label('Teléfono')->placeholder('—'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ['trial' => 'Prueba', 'active' => 'Activa', 'suspended' => 'Suspendida', 'cancelled' => 'Cancelada'][$state] ?? $state)
                    ->color(fn ($state) => ['trial' => 'info', 'active' => 'success', 'suspended' => 'warning', 'cancelled' => 'gray'][$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Registro')->since(),
            ])
            ->paginated(false);
    }
}
