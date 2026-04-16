# 🏔️ Trail des Crêtes – Site Web

Site d'inscription pour le Trail des Crêtes 2025.  
Stack : PHP 8.2 · MySQL 8 · Nginx · Docker · HelloAsso · Brevo

---

## Démarrage rapide (local / DEV)

```bash
# 1. Cloner le repo
git clone git@github.com:VOTRE_USER/trail-site.git
cd trail-site

# 2. Configurer l'environnement
cp .env.example .env
# → Éditer .env avec vos valeurs

# 3. Lancer Docker
docker compose up -d

# 4. Vérifier
docker compose ps
# → Ouvrir http://localhost
```

---

## Configuration HelloAsso

1. Créer un compte sur [dev.helloasso.com](https://dev.helloasso.com)
2. Créer une application → récupérer `client_id` et `client_secret`
3. Renseigner dans `.env` :
   - `HELLOASSO_CLIENT_ID`
   - `HELLOASSO_CLIENT_SECRET`
   - `HELLOASSO_ORG_SLUG` (slug de votre association)
   - `HELLOASSO_FORM_SLUG` (slug du formulaire billetterie)
4. Configurer le webhook dans HelloAsso → `https://votre-domaine.fr/webhook.php`

---

## Configuration Brevo (emails)

1. Créer un compte sur [brevo.com](https://brevo.com)
2. Paramètres → SMTP & API → Générer une clé API
3. Renseigner `BREVO_API_KEY` dans `.env`

---

## Déploiement

### Sur les VPS Hetzner (première fois)

```bash
# Sur chaque VPS (DEV et PROD)
git clone git@github.com:VOTRE_USER/trail-site.git ~/trail-site
cd ~/trail-site
cp .env.example .env
# → Éditer .env
mkdir -p ~/backups
docker compose up -d
```

### CI/CD automatique via GitHub Actions

| Action | Déclencheur | Cible |
|--------|-------------|-------|
| Push sur `main` | Automatique | VPS DEV |
| `git tag v1.x.x && git push --tags` | Manuel | VPS PROD |

**Secrets GitHub à configurer** (Settings → Secrets → Actions) :

| Secret | Description |
|--------|-------------|
| `DEV_HOST` | IP VPS DEV |
| `DEV_USER` | User SSH DEV |
| `DEV_SSH_KEY` | Clé SSH privée DEV |
| `PROD_HOST` | IP VPS PROD |
| `PROD_USER` | User SSH PROD |
| `PROD_SSH_KEY` | Clé SSH privée PROD |

---

## SSL (Let's Encrypt) en production

```bash
docker compose run --rm certbot certonly \
  --webroot -w /var/www/certbot \
  -d votre-domaine.fr \
  --email contact@votre-domaine.fr \
  --agree-tos --no-eff-email
```

Puis décommenter le bloc `server 443` dans `nginx/conf.d/default.conf`.

---

## Structure

```
trail-site/
├── .github/workflows/     # CI/CD GitHub Actions
├── app/
│   ├── public/            # Racine web (Nginx)
│   │   ├── index.php      # Page d'accueil
│   │   ├── inscription.php
│   │   ├── callback.php   # Retour HelloAsso
│   │   ├── webhook.php    # Webhook HelloAsso
│   │   └── assets/css/
│   ├── admin/             # Panel admin (protégé)
│   │   ├── login.php
│   │   └── dashboard.php
│   └── src/               # Classes PHP
│       ├── Database.php
│       ├── Runner.php
│       ├── HelloAsso.php
│       └── Mailer.php
├── nginx/conf.d/
├── mysql/init.sql
├── docker-compose.yml
└── .env.example
```

---

## Admin

URL : `https://votre-domaine.fr/admin/`  
Identifiants : définis dans `.env` (`ADMIN_USER` / `ADMIN_PASS`)
