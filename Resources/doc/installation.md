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

### Configure user roles in Sulu

Make sure that the user roles for redirects are set in Sulu for relevant users roles, otherwise the item is not visible in the navigation and the users are not able manage the redirects. 

![image](https://user-images.githubusercontent.com/1311487/115698291-c01b3e00-a364-11eb-9895-35f10426b47d.png)
