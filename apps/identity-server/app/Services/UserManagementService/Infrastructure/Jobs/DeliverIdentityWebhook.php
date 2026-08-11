<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Jobs;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookDelivery;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookEndpoint;
use App\Services\UserManagementService\Infrastructure\Services\Identity\IdentityWebhookDestinationValidator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class DeliverIdentityWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 15;

    public function __construct(public readonly string $deliveryId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300, 900];
    }

    public function handle(IdentityWebhookDestinationValidator $validator): void
    {
        $delivery = IdentityWebhookDelivery::query()->findOrFail($this->deliveryId);
        if ($delivery->status === 'delivered') {
            return;
        }
        $endpoint = IdentityWebhookEndpoint::query()->findOrFail($delivery->endpoint_id);
        $validator->assertValid((string) $endpoint->url);
        $pinnedResolution = $this->pinnedResolution((string) $endpoint->url, $validator);
        $raw = json_encode($delivery->payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->getTimestamp();
        $signature = hash_hmac('sha256', $timestamp.'.'.$raw, (string) $endpoint->secret);
        $request = Http::connectTimeout(5)
            ->timeout(10)
            ->withoutRedirecting()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Identity-Event-Id' => $delivery->event_id,
                'X-Identity-Event' => $delivery->event,
                'X-Identity-Timestamp' => $timestamp,
                'X-Identity-Signature' => 'v1='.$signature,
            ])
            ->withBody($raw, 'application/json');
        if ($pinnedResolution !== null) {
            $request = $request->withOptions([
                'curl' => [CURLOPT_RESOLVE => [$pinnedResolution]],
            ]);
        }
        $response = $request->post($endpoint->url);

        $delivery->forceFill([
            'attempts' => $delivery->attempts + 1,
            'http_status' => $response->status(),
            'status' => $response->successful() ? 'delivered' : 'retrying',
            'failure' => $response->successful() ? null : $response->body(),
            'delivered_at' => $response->successful() ? now() : null,
        ])->save();
        if (! $response->successful()) {
            throw new RuntimeException('Identity webhook endpoint rejected the delivery.');
        }
        $endpoint->forceFill(['last_delivered_at' => now()])->save();
        if ($delivery->event === 'identity.user.deletion_requested' && $delivery->subject_id) {
            FinalizeIdentityUserDeletion::dispatch((string) $delivery->subject_id)->afterCommit();
        }
    }

    public function failed(?Throwable $exception): void
    {
        IdentityWebhookDelivery::query()->whereKey($this->deliveryId)->update([
            'status' => 'failed',
            'failure' => $exception?->getMessage(),
        ]);
    }

    private function pinnedResolution(
        string $url,
        IdentityWebhookDestinationValidator $validator,
    ): ?string {
        if (! app()->environment('production')) {
            return null;
        }

        $parts = parse_url($url);
        $host = trim((string) ($parts['host'] ?? ''), '[]');
        $port = (int) ($parts['port'] ?? (($parts['scheme'] ?? null) === 'https' ? 443 : 80));
        $address = $validator->resolvePublicAddresses($host)[0];
        $resolvedAddress = str_contains($address, ':') ? "[{$address}]" : $address;

        return "{$host}:{$port}:{$resolvedAddress}";
    }
}
