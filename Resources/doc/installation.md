# Installation

Install bundle over composer:

```bash
composer require sulu/redirect-bundle
```

Add bundle to config/bundles.php:

```php
    Sulu\Bundle\RedirectBundle\SuluRedirectBundle::class => ['all' => true],
```

Add routing files to `config/routes/sulu_redirect_admin.yaml`:

```yml
sulu_redirect_api:
    type: rest
    resource: "@SuluRedirectBundle/Resources/config/routing_api.yml"
    prefix: /admin/api

sulu_redirect:
    resource: "@SuluRedirectBundle/Resources/config/routing.yml"
    prefix: /admin/redirects
```

## Initialize bundle

Create tables

```bash
php bin/console doctrine:schema:update
```

## Available Configuration

```yml
sulu_redirect:
    imports:
        path:                     '%kernel.project_dir%/var/uploads/redirects'
    objects:
        redirect_route:
            model:                Sulu\Bundle\RedirectBundle\Entity\RedirectRoute
            repository:           Sulu\Bundle\RedirectBundle\Entity\RedirectRouteRepository
```

