<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    
    // Numéros de dossard par course
    const DOSSARD_RANGES = [
        'Course Enfant' => ['start' => 1,   'end' => 30],
        'Course 7.5km'  => ['start' => 100, 'end' => 174],
        'Course 15km'   => ['start' => 200, 'end' => 274],
    ];
    
    private $pdo;
    
    public function __construct() {
        $host = getenv('DB_HOST') ?: 'trail_mysql';
        $dbname = getenv('DB_NAME') ?: 'trail';
        $user = getenv('DB_USER') ?: 'trail';
        $pass = getenv('DB_PASS') ?: 'Bionicman.40';
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    }
    
    public function assignDossard($courseLabel) {
        $range = self::DOSSARD_RANGES[$courseLabel] ?? null;
        if (!$range) return null;
        
        // Trouver le prochain numéro disponible
        $stmt = $this->pdo->prepare(
            "SELECT MAX(dossard) as last FROM inscriptions WHERE course = ? AND statut IN ('paid','free')"
        );
        $stmt->execute([$courseLabel]);
        $last = $stmt->fetch(PDO::FETCH_ASSOC)['last'];
        
        $next = $last ? $last + 1 : $range['start'];
        
        if ($next > $range['end']) return null; // Plus de places
        
        return $next;
    }
    
    public function sendConfirmationEmail($inscription) {
        require_once '/var/www/html/vendor/autoload.php';
        
        $mail = new PHPMailer(true);
        
        try {
            // Config SMTP OVH
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: 'ssl0.ovh.net';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER') ?: 'contact@vogue-challex.fr';
            $mail->Password = getenv('SMTP_PASS') ?: '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Expéditeur / destinataire
            $mail->setFrom('contact@vogue-challex.fr', 'Trail de la Vogue Challaisienne');
            $mail->addAddress($inscription['email'], $inscription['prenom'] . ' ' . $inscription['nom']);
            $mail->addReplyTo('contact@vogue-challex.fr', 'Organisation');
            
            // Sujet
            $mail->Subject = '✅ Inscription confirmée - Trail de la Vogue Challaisienne 2026 - Dossard n°' . $inscription['dossard'];
            
            // Corps de l'email
            $repasHtml = '';
            if (!empty($inscription['repas'])) {
                $repas = json_decode($inscription['repas'], true);
                if ($repas) {
                    $repasHtml = '<h3 style="color:#a8c640;">🍽 Repas commandé</h3><ul>';
                    foreach ($repas as $r) {
                        $repasHtml .= '<li>' . htmlspecialchars($r['label']) . ' x' . $r['qty'] . ' — ' . number_format($r['amount'] / 100, 2) . ' €</li>';
                    }
                    $repasHtml .= '</ul>';
                }
            }
            
            $ticketHtml = '';
            if (!empty($inscription['ticket_url'])) {
                $ticketHtml = '<p><a href="' . htmlspecialchars($inscription['ticket_url']) . '" style="background:#a8c640;color:#1a1208;padding:0.75rem 1.5rem;border-radius:4px;text-decoration:none;font-weight:bold;">📄 Télécharger mon billet</a></p>';
            }
            
            $mail->isHTML(true);
            $mail->Body = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4ede0;margin:0;padding:0;">
  <div style="max-width:600px;margin:0 auto;background:#1a1208;color:#f4ede0;border-radius:8px;overflow:hidden;">
    
    <!-- Header -->
    <div style="background:#a8c640;padding:2rem;text-align:center;">
      <h1 style="color:#1a1208;margin:0;font-size:2rem;">▲ TRAIL DE LA VOGUE CHALLAISIENNE</h1>
      <p style="color:#1a1208;margin:0.5rem 0 0;">1ère Édition — Dimanche 6 Septembre 2026</p>
    </div>
    
    <!-- Dossard -->
    <div style="text-align:center;padding:2rem;border-bottom:1px solid rgba(168,198,64,0.3);">
      <p style="color:#d4b896;margin:0;">Votre numéro de dossard</p>
      <div style="font-size:5rem;font-weight:bold;color:#a8c640;line-height:1;">N°' . $inscription['dossard'] . '</div>
      <div style="background:rgba(168,198,64,0.15);border:1px solid #a8c640;border-radius:4px;display:inline-block;padding:0.5rem 1.5rem;margin-top:1rem;">
        <strong style="color:#a8c640;">' . htmlspecialchars($inscription['course']) . '</strong>
      </div>
    </div>
    
    <!-- Infos participant -->
    <div style="padding:1.5rem 2rem;border-bottom:1px solid rgba(255,255,255,0.1);">
      <h3 style="color:#a8c640;">👤 Vos informations</h3>
      <table style="width:100%;border-collapse:collapse;">
        <tr><td style="color:#d4b896;padding:0.25rem 0;width:40%;">Prénom / Nom</td><td>' . htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom']) . '</td></tr>
        <tr><td style="color:#d4b896;padding:0.25rem 0;">Email</td><td>' . htmlspecialchars($inscription['email']) . '</td></tr>
        <tr><td style="color:#d4b896;padding:0.25rem 0;">Course</td><td><strong style="color:#a8c640;">' . htmlspecialchars($inscription['course']) . '</strong></td></tr>
      </table>
    </div>
    
    <!-- Repas -->
    ' . ($repasHtml ? '<div style="padding:1.5rem 2rem;border-bottom:1px solid rgba(255,255,255,0.1);">' . $repasHtml . '</div>' : '') . '
    
    <!-- Infos événement -->
    <div style="padding:1.5rem 2rem;border-bottom:1px solid rgba(255,255,255,0.1);">
      <h3 style="color:#a8c640;">📍 Informations pratiques</h3>
      <table style="width:100%;border-collapse:collapse;">
        <tr><td style="color:#d4b896;padding:0.25rem 0;width:40%;">Date</td><td>Dimanche 6 Septembre 2026</td></tr>
        <tr><td style="color:#d4b896;padding:0.25rem 0;">Lieu</td><td>Parking de la Halle<br>381 Rue de la Mairie, 01630 Challex</td></tr>
        <tr><td style="color:#d4b896;padding:0.25rem 0;">Retrait dossard</td><td>À partir de 8h00 sur place</td></tr>
      </table>
    </div>
    
    <!-- Ticket -->
    ' . ($ticketHtml ? '<div style="padding:1.5rem 2rem;text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);">' . $ticketHtml . '</div>' : '') . '
    
    <!-- Footer -->
    <div style="padding:1.5rem 2rem;text-align:center;color:#d4b896;font-size:0.85rem;">
      <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:#a8c640;">www.vogue-challex.fr</a></p>
      <p>Contact : <a href="mailto:contact@vogue-challex.fr" style="color:#a8c640;">contact@vogue-challex.fr</a></p>
    </div>
    
  </div>
</body>
</html>';

            $mail->AltBody = 'Trail de la Vogue Challaisienne 2026 - Dossard N°' . $inscription['dossard'] . ' - ' . $inscription['course'] . ' - Dimanche 6 Septembre 2026 - Parking de la Halle, 381 Rue de la Mairie, 01630 Challex';
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log('Erreur email: ' . $e->getMessage());
            return false;
        }
    }
}
