# Urb-it - Magento CE / EE 1.x plugin

## Table of Contents

* [Installation](#installation)
* [Basic links](#basic-links)
* [Extension settings](#extension-settings)
    * [Feed Cache](#feed-cache)
    * [Product filtration](#product-filtration)
    * [Custom product attributes](#custom-product-attributes)
* [Troubleshooting](#troubleshooting)

## Installation

To install a Urb-it extensions, place an extension files (presented as `app` directory) to Magento root directory.
Uploaded files contain files of plugin only and not it not rewrite any other core Magento files.

## Basic links

There are two pages in the extension for generating feeds (on front-end side):

- Product Feed - `/urbit_productfeed/` or `/index.php/urbit_productfeed/`
- Inventory Feed - `/urbit_inventoryfeed/` or `/index.php/urbit_inventoryfeed/`

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
- by minimal stock of products (Product Feed only)

The filters by categories and tags are drop-down lists where you can select several options.
The number of products filter is a field for entering the whole stock count of products in the store.

If there is no selected filter parameter (no categories or tags are selected, or the number
of products for filtering is zero), the system skips the filtering by this parameter.


### Custom product attributes

**Product Feed extension only**

To generate a feed, you may need a number of parameters that are not standard for
cms Magento and are not present by default.

As these parameters in Magento can act custom attributes with unique names and descriptions
that can be created in the admin page. (`Catalog -> Attributes -> Manage Attributes`).


In this case, the extension provides functionality for standardization and validation of product feed.
On the product feed settings page, you can configure the correspondence of a set of custom attributes
with a set of parameters required to generate the feed.

You can specify dimension parameters (height, width, length), marking and product codes (EAN/UPC and MPN),
and parameters that characterize the product (color, size, gender, material, pattern, age group,
condition, size type, brands/manufacturers)


## Troubleshooting

In some versions of Magento 1.x, the Tag extension is disabled and filtration.
It can be turned on/off in the admin panel on the `System -> Configuration -> Advanced page` by changing
output option for `Mage_Tag` module.