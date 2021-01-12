# UPGRADE

## 2.x

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
