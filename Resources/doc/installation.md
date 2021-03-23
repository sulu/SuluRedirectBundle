# Installation

### Install bundle using composer:

```bash
composer require sulu/redirect-bundle
```

### Add bundle to `config/bundles.php`:

**Tip**: If community flex recipes are enabled, this should be done automatically.

```php
    Sulu\Bundle\RedirectBundle\SuluRedirectBundle::class => ['all' => true],
```

### Add routing files to `config/routes/sulu_redirect_admin.yaml`:

**Tip**: If community flex recipes are enabled, this should be done automatically.

```yml
sulu_redirect_api:
    type: rest
    resource: "@SuluRedirectBundle/Resources/config/routing_api.yml"
    prefix: /admin/api

sulu_redirect:
    resource: "@SuluRedirectBundle/Resources/config/routing.yml"
    prefix: /admin/redirects
```

### Create necessary database tables

```bash
php bin/console doctrine:schema:update
```
