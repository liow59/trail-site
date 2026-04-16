<?php

declare(strict_types=1);

namespace Trail\Src;

class Mailer
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;
    private \PDO $db;

    public function __construct()
    {
        $this->apiKey    = $_ENV['BREVO_API_KEY'];
        $this->fromEmail = $_ENV['BREVO_FROM_EMAIL'];
        $this->fromName  = $_ENV['BREVO_FROM_NAME'];
        $this->db        = Database::getInstance();
    }

    /**
     * Envoyer un email via l'API Brevo
     */
    private function send(string $to, string $toName, string $subject, string $htmlContent): bool
    {
        $payload = json_encode([
            'sender'      => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'          => [['email' => $to, 'name' => $toName]],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'api-key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 201;
    }

    /**
     * Email de confirmation d'inscription (après paiement)
     */
    public function sendConfirmation(array $runner): bool
    {
        $subject = "✅ Confirmation d'inscription – {$runner['course']} Trail 2025";

        $html = $this->renderConfirmationEmail($runner);

        $success = $this->send(
            $runner['email'],
            $runner['prenom'] . ' ' . $runner['nom'],
            $subject,
            $html
        );

        $this->logEmail((int) $runner['id'], 'confirmation', $success ? 'sent' : 'failed');

        return $success;
    }

    /**
     * Email d'attente de paiement
     */
    public function sendPendingPayment(array $runner, string $paymentUrl): bool
    {
        $subject = "⏳ Finalisez votre inscription – {$runner['course']} Trail 2025";

        $html = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto'>
            <h2>Bonjour {$runner['prenom']} !</h2>
            <p>Votre inscription est en attente de paiement.</p>
            <p>Cliquez sur le bouton ci-dessous pour finaliser votre paiement via HelloAsso :</p>
            <p style='text-align:center;margin:30px 0'>
                <a href='{$paymentUrl}'
                   style='background:#e84c3d;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px'>
                    Payer mon inscription
                </a>
            </p>
            <p style='color:#999;font-size:12px'>
                Ce lien est valable 24h. Si vous avez des questions, contactez-nous à {$this->fromEmail}
            </p>
        </div>";

        $success = $this->send(
            $runner['email'],
            $runner['prenom'] . ' ' . $runner['nom'],
            $subject,
            $html
        );

        $this->logEmail((int) $runner['id'], 'pending_payment', $success ? 'sent' : 'failed');

        return $success;
    }

    /**
     * Template HTML email de confirmation
     */
    private function renderConfirmationEmail(array $runner): string
    {
        $nomEvent = htmlspecialchars($_ENV['BREVO_FROM_NAME'] ?? 'Trail 2025');
        $course   = htmlspecialchars($runner['course']);
        $prenom   = htmlspecialchars($runner['prenom']);
        $nom      = htmlspecialchars($runner['nom']);
        $montant  = number_format((float) $runner['montant'], 2, ',', ' ');

        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='utf-8'></head>
        <body style='margin:0;padding:0;background:#f5f5f5'>
            <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                    <td align='center' style='padding:40px 20px'>
                        <table width='600' cellpadding='0' cellspacing='0'
                               style='background:white;border-radius:12px;overflow:hidden'>
                            <tr>
                                <td style='background:#1a1a2e;padding:30px;text-align:center'>
                                    <h1 style='color:white;margin:0;font-size:28px'>🏔️ {$nomEvent}</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:40px'>
                                    <h2 style='color:#1a1a2e'>Bonjour {$prenom} !</h2>
                                    <p style='color:#555;line-height:1.6'>
                                        Votre inscription est <strong style='color:#27ae60'>confirmée</strong>.
                                        Bienvenue sur la ligne de départ !
                                    </p>

                                    <div style='background:#f8f9fa;border-radius:8px;padding:20px;margin:20px 0'>
                                        <h3 style='margin:0 0 15px;color:#1a1a2e'>Récapitulatif</h3>
                                        <table width='100%'>
                                            <tr>
                                                <td style='color:#777;padding:5px 0'>Nom</td>
                                                <td style='text-align:right;font-weight:bold'>{$prenom} {$nom}</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#777;padding:5px 0'>Course</td>
                                                <td style='text-align:right;font-weight:bold'>{$course}</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#777;padding:5px 0'>Montant payé</td>
                                                <td style='text-align:right;font-weight:bold;color:#27ae60'>{$montant} €</td>
                                            </tr>
                                            <tr>
                                                <td style='color:#777;padding:5px 0'>N° dossard</td>
                                                <td style='text-align:right;font-weight:bold'>#{$runner['id']}</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <p style='color:#555;line-height:1.6'>
                                        Conservez cet email comme justificatif d'inscription.
                                        Vous recevrez les informations pratiques (horaires, parking, bénévoles)
                                        dans les semaines précédant l'événement.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style='background:#f8f9fa;padding:20px;text-align:center;color:#999;font-size:12px'>
                                    {$nomEvent} · {$this->fromEmail}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }

    private function logEmail(int $runnerId, string $type, string $status): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_logs (runner_id, type, status) VALUES (:runner_id, :type, :status)
        ");
        $stmt->execute([':runner_id' => $runnerId, ':type' => $type, ':status' => $status]);
    }
}
