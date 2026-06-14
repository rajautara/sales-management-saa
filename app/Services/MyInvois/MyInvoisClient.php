<?php

namespace App\Services\MyInvois;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * HTTP client for the LHDN MyInvois API (OAuth2 + document submission /
 * status / cancellation). Targets the sandbox (preprod) or production
 * endpoints selected in config/myinvois.php.
 *
 * @see https://sdk.myinvois.hasil.gov.my/einvoicingapi/
 */
class MyInvoisClient
{
    /**
     * Obtain (and cache) an OAuth2 access token via the client-credentials grant.
     */
    public function accessToken(): string
    {
        $clientId = config('myinvois.client_id');
        $clientSecret = config('myinvois.client_secret');

        if (blank($clientId) || blank($clientSecret)) {
            throw new RuntimeException('MyInvois credentials are not configured (MYINVOIS_CLIENT_ID / MYINVOIS_CLIENT_SECRET).');
        }

        return Cache::remember(
            'myinvois.token.'.md5((string) $clientId.config('myinvois.environment')),
            now()->addMinutes(50),
            function () use ($clientId, $clientSecret) {
                $response = Http::asForm()
                    ->timeout((int) config('myinvois.timeout'))
                    ->post($this->identityUrl().'/connect/token', [
                        'grant_type' => 'client_credentials',
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'scope' => 'InvoicingAPI',
                    ]);

                if ($response->failed()) {
                    throw new RuntimeException('MyInvois authentication failed (HTTP '.$response->status().'): '.$response->body());
                }

                $token = $response->json('access_token');

                if (! is_string($token) || $token === '') {
                    throw new RuntimeException('MyInvois returned no access token.');
                }

                return $token;
            }
        );
    }

    /**
     * Submit one or more signed documents.
     *
     * @param  array<int, array<string, mixed>>  $documents  each: format, document (base64), documentHash, codeNumber
     * @return array<string, mixed> { submissionUid, acceptedDocuments[], rejectedDocuments[] }
     */
    public function submitDocuments(array $documents): array
    {
        $response = $this->request()->post($this->apiUrl().'/api/v1.0/documentsubmissions', [
            'documents' => $documents,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('MyInvois submission failed (HTTP '.$response->status().'): '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Fetch a document's validation status and details by UUID.
     *
     * @return array<string, mixed>
     */
    public function getDocumentDetails(string $uuid): array
    {
        $response = $this->request()->get($this->apiUrl().'/api/v1.0/documents/'.$uuid.'/details');

        if ($response->failed()) {
            throw new RuntimeException('MyInvois status lookup failed (HTTP '.$response->status().'): '.$response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Cancel a validated document within the 72-hour window.
     */
    public function cancelDocument(string $uuid, string $reason): void
    {
        $response = $this->request()->put($this->apiUrl().'/api/v1.0/documents/state/'.$uuid.'/state', [
            'status' => 'cancelled',
            'reason' => $reason,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('MyInvois cancellation failed (HTTP '.$response->status().'): '.$response->body());
        }
    }

    public function portalUrl(): string
    {
        return $this->urls()['portal'];
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->timeout((int) config('myinvois.timeout'))
            ->acceptJson()
            ->asJson();
    }

    protected function identityUrl(): string
    {
        return $this->urls()['identity'];
    }

    protected function apiUrl(): string
    {
        return $this->urls()['api'];
    }

    /**
     * @return array{identity: string, api: string, portal: string}
     */
    protected function urls(): array
    {
        $env = config('myinvois.environment') === 'production' ? 'production' : 'sandbox';

        return config('myinvois.urls.'.$env);
    }
}
