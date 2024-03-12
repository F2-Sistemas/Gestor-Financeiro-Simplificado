## App Docs

### [APP README](../README.md)

### Helpers
* **`site_config_set`**

```php
site_config_get('app.name'); // (default app.name)

site_config_set('app.name', 'string', 'Teste');//true
site_config_get('app.name'); // "Teste"
site_config_delete('app.name'); // true

// If has no value, get default from config('...')
site_config_get('app.name'); // (default app.name)
```
