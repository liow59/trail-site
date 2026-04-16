<?php

declare(strict_types=1);

namespace Trail\Src;

class HelloAsso
{
    private string $apiUrl;
    private string $clientId;
    private string $clientSecret;
    private string $orgSlug;
    private string $formSlug;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->apiUrl       = $_ENV['HELLOASSO_API_URL'] ?? 'https://api.helloasso.com/v5';
        $this->clientId     = $_ENV['HELLOASSO_CLIENT_ID'];
        $this->clientSecret = $_ENV['HELLOASSO_CLIENT_SECRET'];
        $this->orgSlug      = $_ENV['HELLOASSO_ORG_SLUG'];
        $this->formSlug     = $_ENV['HELLOASSO_FORM_SLUG'];
    }

    /**
     * Obtenir un token OAuth2
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $ch = curl_init('https://api.helloasso.com/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException('HelloAsso OAuth2 failed: ' . $response);
        }

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'];

        return $this->accessToken;
    }

    /**
     * Générer le lien de paiement HelloAsso pour un coureur
     * HelloAsso utilise un checkout personnalisé
     */
    public function getPaymentUrl(int $runnerId, string $email, string $prenom, string $nom): string
    {
        // URL du checkout HelloAsso avec paramètres pré-remplis
        // Les paramètres returnUrl et errorUrl permettent de rediriger après paiement
        $params = http_build_query([
            'returnUrl'  => $_ENV['APP_URL'] . '/callback.php?runner_id=' . $runnerId . '&status=success',
            'errorUrl'   => $_ENV['APP_URL'] . '/callback.php?runner_id=' . $runnerId . '&status=error',
            'backUrl'    => $_ENV['APP_URL'] . '/inscription.php',
            'firstName'  => $prenom,
            'lastName'   => $nom,
            'email'      => $email,
        ]);

        return "https://www.helloasso.com/associations/{$this->orgSlug}/evenements/{$this->formSlug}/payer?{$params}";
    }

    /**
     * Récupérer les détails d'une commande HelloAsso
     */
    public function getOrder(string $orderId): ?array
    {
        $token = $this->getAccessToken();

        $ch = curl_init("{$this->apiUrl}/orders/{$orderId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("HelloAsso getOrder failed [{$httpCode}]: {$response}");
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Valider la signature du webhook HelloAsso
     */
    public function validateWebhook(string $payload, string $signature): bool
    {
        $secret = $_ENV['APP_SECRET'];
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Récupérer les inscriptions de la billetterie
     */
    public function getFormOrders(): array
    {
        $token = $this->getAccessToken();

        $url = "{$this->apiUrl}/organizations/{$this->orgSlug}/forms/Event/{$this->formSlug}/orders";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }
}
