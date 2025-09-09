# UPGRADE

## 2.1.0

## Deprecate usage of fos rest routing

We are no longer considering the [fos rest routing](https://github.com/handcraftedinthealps/RestRoutingBundle) as a best practice.
All bundles should use the Symfony routing system instead.

Inside your `config/routes/sulu_redirect_admin.yaml` you can remove the fos rest routing configuration.
First, remove all instances of `type: rest` and also replace `.yml` with `.yaml`:

```diff
# config/routes/sulu_redirect_admin.yaml`
 sulu_redirect_api:
-    type: rest
-    resource: "@SuluRedirectBundle/Resources/config/routing_api.yml"
+    resource: "@SuluRedirectBundle/Resources/config/routing_api.yaml"
     prefix: /admin/api
 
 sulu_redirect:
-    resource: "@SuluRedirectBundle/Resources/config/routing.yml"
+    resource: "@SuluRedirectBundle/Resources/config/routing.yaml"
     prefix: /admin/redirects
```

## 2.0.0

### Permission changed

The permissions has been implemented in the redirect bundle run the following SQL to give your roles add, edit, delete permissions:

```sql
UPDATE `se_permissions` SET `permissions` = 127 WHERE context = `sulu.modules.redirects`;
```

### Database change

To support multiple webspaces a sourceHost field was added to the RedirectRoute entity and
the following database migration need to be run:

```sql
ALTER TABLE `re_redirect_routes` ADD `sourceHost` VARCHAR(191) DEFAULT NULL;
DROP INDEX `UNIQ_3DB4B4315F8A7F73` ON `re_redirect_routes`;
CREATE UNIQUE INDEX `UNIQ_3DB4B4315F8A7F73738AA078` ON `re_redirect_routes` (`source`, `sourceHost`);
```

### Some interfaces changed

- ConverterInterface
- WriterInterface
- RedirectRouteManagerInterface
- RedirectRouteRepositoryInterface

### Rest Api changed

The resourceKey of the `_embedded` has changed from `redirect-routes` to `redirect_routes`.

### Database changes

To support utfmb4 which is default in sulu 2.0 we need the shorten indexed fields:

```sql
ALTER TABLE `re_redirect_routes` CHANGE `id` `id` VARCHAR(36) NOT NULL;
ALTER TABLE `re_redirect_routes` CHANGE `source` `source` VARCHAR(191) NOT NULL;
```

## 1.0

### Import action changed

To support permissions the RedirectImportController::importAction has been
renamed to RedirectImportController::postAction.

### Permission changed

The permissions has been implemented in the redirect bundle run the following SQL to give your roles add, edit, delete permissions:

```sql
UPDATE `se_permissions` SET `permissions` = 127 WHERE context = `sulu.modules.redirects`;
```

### Database change

To support multiple webspaces a sourceHost field was added to the RedirectRoute entity and
the following database migration need to be run:

```sql
ALTER TABLE `re_redirect_routes` ADD `sourceHost` VARCHAR(255) DEFAULT NULL;
DROP INDEX `UNIQ_3DB4B4315F8A7F73` ON `re_redirect_routes`;
CREATE UNIQUE INDEX `UNIQ_3DB4B4315F8A7F73738AA078` ON `re_redirect_routes` (`source`, `sourceHost`);
```
