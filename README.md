# Picqer Extended Integration for Shopware 6
Shopware extension for Picqer

## Installation
This project can easily be installed through Composer.

```
    composer require picqer/shopware6-plugin
```

## Activate Plugin
1. Log onto your Shopware Administration portal and navigate to Extensions > My extensions.
2. Install and activate the Picqer Extended Integration plugin.
3. Fill out the general configuration information:
    + Subdomain: is the prefix of your domain name. If your log on to 'my-shop.picqer.com', then fill in 'my-shop'.
    + Connection Key: can be found in Picqer > Settings > Webshops > Shopware shop. Copy and paste this field.

Orders will now be pushed to Picqer immediately.

## Uninstall
1. Log onto your Shopware Administration portal and navigate to Extensions > My extensions.
2. Uninstall the Picqer plugin.
3. ``` composer remove picqer/shopware-plugin ```
4. Remove the Picqer plugin. 