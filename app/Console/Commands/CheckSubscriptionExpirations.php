<?php

namespace App\Console\Commands;

use App\Models\SystemAdmin;
use App\Models\TenantSubscription;
use App\Services\WhatsApp\WhatsAppNotifier;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class CheckSubscriptionExpirations extends Command
{
    protected $signature = 'orbis:check-subscriptions {--dry : Solo muestra, no envía ni marca}';

    protected $description = 'Avisa (WhatsApp + panel) de suscripciones por vencer y marca como vencidas las expiradas';

    public function handle(WhatsAppNotifier $whatsapp): int
    {
        $days  = (int) config('orbis.subscriptions.alert_days_before', 2);
        $today = today();
        $limit = $today->copy()->addDays($days);

        // 1) Por vencer: end_date entre hoy y hoy+N, aún sin avisar.
        //    Usar "<= limit" (y no "== limit") evita perder avisos si el
        //    scheduler no corrió un día.
        $expiring = TenantSubscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$today, $limit])
            ->whereNull('expiry_alert_sent_at')
            ->orderBy('end_date')
            ->get();

        if ($expiring->isNotEmpty()) {
            $lines = $expiring->map(function ($s) use ($today) {
                $left = $today->diffInDays($s->end_date, false);
                $when = match (true) {
                    $left <= 0 => 'vence HOY',
                    $left === 1 => 'vence mañana',
                    default => "vence en {$left} días",
                };

                return "• {$s->tenant?->business_name} ({$s->plan?->name}) — {$when} ({$s->end_date->format('d/m/Y')})"
                    .($s->tenant?->phone ? " · Tel: {$s->tenant->phone}" : '');
            })->implode("\n");

            $message = "⚠️ *Orbis — Suscripciones por vencer*\n\n{$lines}";

            $this->line($message);

            if (! $this->option('dry')) {
                $sent = $whatsapp->send($message);

                // Aviso dentro del panel admin (campana), siempre.
                SystemAdmin::all()->each(fn (SystemAdmin $admin) => Notification::make()
                    ->title('Suscripciones por vencer')
                    ->body($expiring->count().' clínica(s) vencen en '.$days.' días o menos.')
                    ->warning()
                    ->sendToDatabase($admin));

                // Solo se marca como avisada si WhatsApp aceptó el mensaje;
                // si falló, se reintenta en la próxima corrida.
                if ($sent) {
                    TenantSubscription::whereIn('id', $expiring->pluck('id'))
                        ->update(['expiry_alert_sent_at' => now()]);
                } else {
                    $this->error('WhatsApp no pudo enviarse; se reintentará.');
                }
            }
        } else {
            $this->info('Sin suscripciones por avisar.');
        }

        // 2) Ya vencidas: se marcan 'expired' (no se suspende la clínica
        //    automáticamente; eso lo decides tú desde Clínicas).
        if (! $this->option('dry')) {
            $n = TenantSubscription::where('status', 'active')
                ->whereNotNull('end_date')
                ->where('end_date', '<', $today)
                ->update(['status' => 'expired']);

            if ($n) {
                $this->info("{$n} suscripción(es) marcadas como vencidas.");
            }
        }

        return self::SUCCESS;
    }
}
