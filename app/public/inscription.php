<?php
$HELLOASSO_URL = 'https://www.helloasso.com/associations/la-vogue-challaisienne/evenements/trail-de-la-vogue-challaisienne-2026';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription - Trail de la Vogue Challaisienne 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="page-header">
  <a href="/" class="back-link">← Retour à l'accueil</a>
  <h1>Inscription</h1>
  <p>Vous allez être redirigé vers notre plateforme de paiement sécurisée</p>
</div>

<section class="section" style="text-align:center; max-width:600px; margin:0 auto;">
  <div style="font-size:4rem; margin-bottom:1.5rem;">🏃</div>
  <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2.5rem; color:var(--cream); margin-bottom:1rem;">Prêt à vous inscrire ?</h2>
  <p style="color:var(--sand); margin-bottom:2rem; font-size:1rem; line-height:1.6;">
    Cliquez sur le bouton ci-dessous pour accéder au formulaire d'inscription et procéder au paiement sécurisé.
  </p>

  <div style="background:rgba(168,198,64,0.08); border:1px solid rgba(168,198,64,0.2); border-radius:4px; padding:1.5rem; margin-bottom:2rem; text-align:left;">
    <p style="font-family:'DM Mono',sans-serif; font-size:0.8rem; color:var(--lime); letter-spacing:0.1em; margin-bottom:1rem;">// COURSES DISPONIBLES</p>
    <div style="display:flex; flex-direction:column; gap:0.75rem;">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <span style="color:var(--sky); font-weight:600;">Course Enfant (3km)</span>
        <span style="color:var(--sky); font-family:'DM Mono',sans-serif;">Gratuit</span>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <span style="color:var(--lime); font-weight:600;">Course 7.5km</span>
        <span style="color:var(--lime); font-family:'DM Mono',sans-serif;">10 €</span>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <span style="color:#e07850; font-weight:600;">Course 15km</span>
        <span style="color:#e07850; font-family:'DM Mono',sans-serif;">15 €</span>
      </div>
    </div>
    <hr style="border:none; border-top:1px solid rgba(255,255,255,0.1); margin:1rem 0;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <span style="color:var(--sand); font-weight:600;">🍽 Repas (optionnel)</span>
      <span style="color:var(--sand); font-family:'DM Mono',sans-serif;">12 €</span>
    </div>
  </div>

  <a href="<?= $HELLOASSO_URL ?>" target="_blank" class="cta-btn" style="display:inline-block; font-size:1.1rem; padding:1rem 2.5rem;">
    S'inscrire maintenant →
  </a>

  <p style="color:var(--sand); font-size:0.8rem; margin-top:1.5rem; opacity:0.7;">
    🔒 Paiement sécurisé — CB & PayPal acceptés
  </p>
</section>

<footer style="text-align:center; padding:2rem; color:var(--sand); font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.1); margin-top:4rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime); text-decoration:none;">www.vogue-challex.fr</a></p>
</footer>

</body>
</html>
