# Urb-it - Magento 2.x plugin

## Table of Contents

* [Installation](#installation)
* [Basic links](#basic-links)
* [Extension settings](#extension-settings)
    * [Feed Cache](#feed-cache)
    * [Product filtration](#product-filtration)
    * [Custom product attributes](#custom-product-attributes)
* [Troubleshooting](#troubleshooting)

## Installation

To install a Urb-it extensions necessary to proceed next steps:
* Place an extension files (presented as `app` directory) to Magento root directory.
    Uploaded files contain files of plugin only and it not overwrite
    any other core Magento files or extensions.
* [Setup Magento Cron](http://devdocs.magento.com/guides/v2.0/config-guide/cli/config-cli-subcommands-cron.html) (if it not configured yet). 
* Go to Component Manager page (`System -> Web Setup Wizard -> Component Manager`) in Admin Panel
    - If Web Setup Wizard is missing in System tab then try open it directly by url `http://yoursite.com/setup/`
* Find `Urbit_ProductFeed`/`Urbit_InventoryFeed` and set it to `enable`

## Basic links

There are two pages in the extension for generating feeds (on front-end side):

- Product Feed - `/productfeed/feed/json`
- Inventory Feed - `/inventoryfeed/feed/json`

And also there are two pages of feed generation settings (in the admin panel)

- Product Feed - In the menu choose `Urbit -> Product Feed`
- Inventory Feed - In the menu, choose `Urbit -> Inventory Feed`


## Extension settings

Extension use Magento setting system and can be configured globally
(via `Default Config` option) or separately for each store view.

### Feed Cache

The extension uses a caching system to reduce a site load and speed up the plug-in during
the generation of the feed, so feed is created and saved to file at specific time intervals.

The refresh interval is specified on the `Cache duration` field.

Time of updating Inventory Feed specified in minutes, Product Feed - in hours.


### Product filtration

On extension settings pages you can configure a filtering of products that will be present in the created feed.

Filtering of products is possible by following parameters:
- by categories
- by product tags

The filters are drop-down lists where you can select several options.
The number of products filter is a field for entering the whole stock count of products in the store.

Because Magento 2 not have built-in Tags functional you can choose one of custom attribute
and set value that products for display should have.



### Custom product attributes

**Product Feed extension only**

To generate a feed, you may need a number of parameters that are not standard for
cms Magento and are not present by default.

As these parameters in Magento can act custom attributes with unique names and descriptions
that can be created in the admin page. (`Stores -> Attributes -> Product`).


In this case, the extension provides functionality for standardization and validation of product feed.
On the product feed settings page, you can configure the correspondence of a set of custom attributes
with a set of parameters required to generate the feed.

You can specify dimension parameters (height, width, length), it units, marking and product
codes (EAN/UPC and MPN), and parameters that characterize the product (color, size, gender, material,
pattern, age group, condition, size type, brands/manufacturers).


## Troubleshooting

In some Magento 2 configuration could be missed `Web Setup Wizard` link and it not available by it url.

In this case plugin should be installed via system console:

* Place an extension files (presented as app directory) to Magento root directory.
* Open Magento root folder in console
* Run command `php bin/magento module:enable Urbit_ProductFeed`
    or `php bin/magento module:enable Urbit_InventoryFeed` (depending on which extension should be enabled)
* Run command `php bin/magento setup:upgrade`