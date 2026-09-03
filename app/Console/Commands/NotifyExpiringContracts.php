<?php

namespace App\Console\Commands;

use App\Enums\v1\General\CommonStatus;
use App\Models\Clients\ContractModel;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('background:notify-expiring-contracts {--days=15 : Días de anticipación para considerar un contrato próximo a vencer}')]
#[Description('Notifica por Telegram los contratos de clientes que están próximos a vencer.')]
class NotifyExpiringContracts extends Command
{
    /**
     * Execute the console command.
     * @return int
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function handle(): int
    {
        $days = (int)$this->option('days');
        $today = Carbon::today();
        $limit = $today->copy()->addDays($days);

        $contracts = ContractModel::query()
            ->with('client')
            ->where('status_id', CommonStatus::ACTIVE->value)
            ->whereDate('contract_end_date', '>=', $today)
            ->whereDate('contract_end_date', '<=', $limit)
            ->orderBy('contract_end_date')
            ->get();

        if ($contracts->isEmpty()) {
            $this->info("No hay contratos próximos a vencer.");
            return self::SUCCESS;
        }

        /**
         * Dividiendo contratos en grupos de 20 para evitar mensajes demasiado grandes.
         */

        $chunks = $contracts->chunk(20);

        foreach ($chunks as $index => $chunk) {
            $message = $this->buildMessage(contracts: $chunk, days: $days, withHeader: $index === 0);
            $this->sendTelegramMessage($message);
        }

        $this->info("Se notificaron {$contracts->count()} contrato(s) próximos a vencer.");

        return self::SUCCESS;
    }

    /**
     * Construye el mensaje que será enviado a telegram.
     *
     * @param Collection $contracts
     * @param int $days
     * @param bool $withHeader
     * @return string
     */
    protected function buildMessage(Collection $contracts, int $days, bool $withHeader): string
    {
        $lines = [];

        if ($withHeader) {
//            $lines[] = "*Contratos próximos a vencer* (Próximos {$days} días)})";
            $lines[] = '📋 <b>Contratos próximos a vencer</b>';
//            $lines[] = $this->escapeMarkdownV2("(Próximos {$days} días)");
            $lines[] = "⏳ Próximos {$days} día(s)";
            $lines[] = '================================';
            $lines[] = '';
        }

        foreach ($contracts as $contract) {
            $client = $contract->client;
            $clientName = $client
                ? trim("{$client->name} {$client->surname}")
                : "Cliente #{$contract->client_id} (no encontrado.)";

            $endDate = Carbon::parse($contract->contract_end_date)->format('Y/m/d');
            $diff = $contract->diff_days;

            //  Nivel de urgencia
            if ($diff <= 2) {
                $statusEmoji = '🔴';
                $statusText = $diff === 0 ? 'Hoy' : "Faltan {$diff} día(s)";
            } elseif ($diff <= 7) {
                $statusEmoji = '🟠';
                $statusText = "Faltan {$diff} días";
            } else {
                $statusEmoji = '🟢';
                $statusText = "Faltan {$diff} días";
            }

            $lines[] = '👤 <b>' . e($clientName) . '</b>';
            $lines[] = '📅 Vence: ' . e("{$endDate}");
            $lines[] = "{$statusEmoji} {$statusText}";
            $lines[] = "";
        }

        $lines[] = '================================';
        $lines[] = '📊 <b>Total:</b> ' . $contracts->count() . ' contrato(s)';

        return implode("\n", $lines);
    }

    /**
     * Escapa caracteres reservados de markdown de telegram para evitar
     * que un nombre con guiones, asteriscos, etc. Rompa el formato.
     *
     * @param string $text
     * @return string
     */
    protected function escapeMarkdown(string $text): string
    {
        return preg_replace('/([_*\[\]()~`>#+\-=|{}.!])/', '\\\\$1', $text);
    }

    protected function escapeMarkdownV2(string $text): string
    {
        return preg_replace(
            '/([_*\[\]()~`>#+\-=|{}.!])/',
            '\\\\$1',
            $text
        );
    }

    /**
     * Envía mensaje desde telegram.
     *
     * @param string $message
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    protected function sendTelegramMessage(string $message): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            Log::channel('expiring_contracts')
                ->warning('Telegram bot token o chat id no configurados. No se pudo enviar la notificación de contratos.');
            return;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        if ($response->failed()) {
            Log::channel('expiring_contracts')
                ->error("Error al enviar mensaje a Telegram (contratos por vencer)", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
        }

        Log::channel('expiring_contracts')->info('Notificación de contratos próximos a vencer enviada correctamente.');
    }
}
