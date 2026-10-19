<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotifier
{
    /** Devuelve true si el mensaje fue aceptado por el proveedor. */
    public function send(string $message, ?string $to = null): bool
    {
        $to = preg_replace('/\D+/', '', $to ?? (string) config('orbis.whatsapp.admin_phone'));

        if (! $to) {
            Log::warning('WhatsApp: falta ORBIS_ADMIN_WHATSAPP, no se envió el mensaje.');

            return false;
        }

        try {
            return match (config('orbis.whatsapp.driver')) {
                'callmebot' => $this->callmebot($to, $message),
                'twilio'    => $this->twilio($to, $message),
                default     => $this->log($to, $message),
            };
        } catch (\Throwable $e) {
            Log::error('WhatsApp: error al enviar', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function log(string $to, string $message): bool
    {
        Log::info("WhatsApp [log] -> +{$to}\n{$message}");

        return true;
    }

    /** CallMeBot: gratis, pensado para enviarte mensajes A TI MISMA. */
    private function callmebot(string $to, string $message): bool
    {
        $res = Http::timeout(20)->get('https://api.callmebot.com/whatsapp.php', [
            'phone'  => $to,
            'text'   => $message,
            'apikey' => config('orbis.whatsapp.callmebot.apikey'),
        ]);

        if (! $res->successful()) {
            Log::error('WhatsApp CallMeBot falló', ['status' => $res->status(), 'body' => $res->body()]);
        }

        return $res->successful();
    }

    /** Twilio WhatsApp (sandbox o número aprobado). */
    private function twilio(string $to, string $message): bool
    {
        $cfg = config('orbis.whatsapp.twilio');

        $res = Http::timeout(20)
            ->asForm()
            ->withBasicAuth($cfg['sid'], $cfg['token'])
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$cfg['sid']}/Messages.json", [
                'From' => 'whatsapp:'.$cfg['from'],
                'To'   => 'whatsapp:+'.$to,
                'Body' => $message,
            ]);

        if (! $res->successful()) {
            Log::error('WhatsApp Twilio falló', ['status' => $res->status(), 'body' => $res->body()]);
        }

        return $res->successful();
    }
}
