# UPGRADE

## dev-master

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
