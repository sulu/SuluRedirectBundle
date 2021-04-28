# Configuration

The following code snippet shows all the available configuration options for the SuluRedirectBundle.

```yml
# config/packages/sulu_redirect.yaml

sulu_redirect:

    # When enabled, this feature automatically creates redirects with http status code 410 when a document with route or an route entity is removed.
    gone_on_remove:
        enabled: true

    # The directory where the uploaded csv files should be stored
    imports:
        path: '%kernel.project_dir%/var/uploads/redirects'

    # Configuring the objects for `redirect_route` allows setting a custom entity or repository class
    objects:
        redirect_route:
            model: Sulu\Bundle\RedirectBundle\Entity\RedirectRoute
            repository: Sulu\Bundle\RedirectBundle\Entity\RedirectRouteRepository
```
