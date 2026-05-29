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
  <p>Inscrivez-vous au Trail de la Vogue Challaisienne 2026</p>
</div>

<section class="section" style="max-width:700px;margin:0 auto;text-align:center;">

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2.5rem;text-align:left;">
    <div style="background:rgba(135,184,196,0.1);border:1px solid var(--sky);border-radius:4px;padding:1.25rem;">
      <div style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--sky);">3<small style="font-size:1.2rem">km</small></div>
      <div style="color:var(--sand);font-size:0.85rem;">Course Enfant · 8-11 ans</div>
      <div style="color:var(--sky);font-weight:700;margin-top:0.5rem;">Gratuit</div>
    </div>
    <div style="background:rgba(168,198,64,0.1);border:1px solid var(--lime);border-radius:4px;padding:1.25rem;">
      <div style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--lime);">7.5<small style="font-size:1.2rem">km</small></div>
      <div style="color:var(--sand);font-size:0.85rem;">À partir de 12 ans · 150 D+</div>
      <div style="color:var(--lime);font-weight:700;margin-top:0.5rem;">10 €</div>
    </div>
    <div style="background:rgba(224,120,80,0.1);border:1px solid #e07850;border-radius:4px;padding:1.25rem;">
      <div style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:#e07850;">15<small style="font-size:1.2rem">km</small></div>
      <div style="color:var(--sand);font-size:0.85rem;">À partir de 16 ans · 300 D+</div>
      <div style="color:#e07850;font-weight:700;margin-top:0.5rem;">15 €</div>
    </div>
  </div>

  <div style="background:rgba(168,198,64,0.05);border:1px solid rgba(168,198,64,0.2);border-radius:4px;padding:1.25rem;margin-bottom:2rem;text-align:left;">
    <p style="font-family:'DM Mono',sans-serif;font-size:0.75rem;color:var(--lime);letter-spacing:0.1em;margin-bottom:0.75rem;">// REPAS DE FIN DE COURSE</p>
    <div style="display:flex;justify-content:space-between;color:var(--sand);font-size:0.9rem;">
      <span>🥙 Kebab Poulet-Frites</span><span style="color:var(--lime);font-weight:600;">12 €</span>
    </div>
    <div style="display:flex;justify-content:space-between;color:var(--sand);font-size:0.9rem;margin-top:0.5rem;">
      <span>🥙 Kebab Agneau-Frites</span><span style="color:var(--lime);font-weight:600;">12 €</span>
    </div>
  </div>

  <a href="<?= $HELLOASSO_URL ?>" target="_blank" class="cta-btn" style="display:inline-block;font-size:1.1rem;padding:1rem 2.5rem;">
    S'inscrire maintenant →
  </a>

  <p style="color:var(--sand);font-size:0.8rem;margin-top:1.5rem;opacity:0.7;">
    🔒 Inscription et paiement sécurisés
  </p>

</section>

<footer style="text-align:center;padding:2rem;color:var(--sand);font-size:0.85rem;border-top:1px solid rgba(255,255,255,0.1);margin-top:4rem;">
  <p>© 2026 Vogue Challaisienne · <a href="https://www.vogue-challex.fr" style="color:var(--lime);text-decoration:none;">www.vogue-challex.fr</a></p>
</footer>

</body>
</html>
