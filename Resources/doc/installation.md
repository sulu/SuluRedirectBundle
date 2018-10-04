# Installation

Install bundle over composer:

```bash
composer require sulu/redirect-bundle
```

Add bundle to AbstractKernel:

```php
new Sulu\Bundle\RedirectBundle\SuluRedirectBundle(),
```

Add routing files to `app/config/admin/routing.yml`:

```yml
sulu_redirect_api:
    type: rest
    resource: "@SuluRedirectBundle/Resources/config/routing_api.xml"
    prefix: /admin/api

sulu_redirect:
    resource: "@SuluRedirectBundle/Resources/config/routing.xml"
    prefix: /admin/redirects
```

## Initialize bundle

Create assets:

```bash
php bin/console assets:install
```

Create translations:

```
php bin/console sulu:translate:export
```

Create tables

```bash
php bin/console doctrine:schema:update
```

## Available Configuration

```yml
sulu_redirect:

    # When enabled, this feature automatically creates redirects with http status code 410 when a document with route or an route entity is removed.
    gone_on_remove:
        enabled:              true
    imports:
        path:                 '%kernel.root_dir%/../var/uploads/redirects'
    objects:
        redirect_route:
            model:                Sulu\Bundle\RedirectBundle\Entity\RedirectRoute
            repository:           Sulu\Bundle\RedirectBundle\Entity\RedirectRouteRepository
```

