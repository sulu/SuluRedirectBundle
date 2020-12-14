# Upgrading

## dev-master

### Database change

To support multiple webspaces a host field was added to the redirect entity and
the following database migration need to be run.

```sql
ALTER TABLE `re_redirect_routes` ADD `sourceHost` VARCHAR(255) DEFAULT NULL;
DROP INDEX `UNIQ_3DB4B4315F8A7F73` ON `re_redirect_routes`;
CREATE UNIQUE INDEX `UNIQ_3DB4B4315F8A7F73738AA078` ON `re_redirect_routes` (`source`, `sourceHost`);
```
