# Static content
Generate static pages from symfony routes

## Configuration
Add static_content.yaml file in your config/packages folder

```
static_content:
  target_folder: static
  excluded_routes: ['app_logout', 'app_login']
  excluded_prefix_routes: ['ajax', 'admin']
```

## Edit htaccess 
Add this to the /public/.htaccess file to serve static pages automatically:

```
    RewriteCond %{DOCUMENT_ROOT}/static/%{REQUEST_URI}.html -f [NC]
    RewriteRule ^ static/%{REQUEST_URI}.html [L]
```    

where static is the default 'target_folder' parameter in config file

## Doctrine entities
to generate static pages for doctrine entities with for example a slug attribute,
you have to name your url like this: 
    /myentity/{myentity_slug}