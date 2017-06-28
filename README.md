
## Contribution

Feel free to contribute to the feeds and with for generation of them.
Add tools and libraries for feed generation to the tools folder.



# Urb-it Merchant Feeds

Feeds are one of many methods of delivering product data to
the Urb-it platform.
## Table of Contents

- [Layout](#layout)
- [Tools](#tools)
- [Products](#products)
- [Inventory](#inventory)
- [Contribution](#contribution)
## Layout

```
├── docs (are used to generate the readme)
├── examples (examples of feeds to use)
│   ├── inventory
│   └── products
├── schemas (the schemas to validate against)
│   ├── inventory
│   └── products
├── tools (different methods of generating feeds)
```
## Tools 
 
### Test and Validate your feeds.

[JSON Schema](http://json-schema.org/) is used as specification for feeds and 
also the method of validating your feeds against the schema.

Use [Online Validators](http://www.jsonschemavalidator.net/) to make sure the
format of your feeds adhere to the specification of the schema.

## Products

### Objects
* [`Attributes`](#reference-attributes)
* [`Dimensions`](#reference-dimensions)
* [`feed_format`](#reference-feed_format)
* [`Inventory List`](#reference-inventory-list) (root object)
* [`price`](#reference-price)
* [`schedule`](#reference-schedule)


---------------------------------------
<a name="reference-attributes"></a>
#### Attributes

Allows the customer to specify additional attributes

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**name**|`string`|Name / Title of the attribute| :white_check_mark: Yes|
|**type**|`string`|The type of the attribute| :white_check_mark: Yes|
|**unit**|`string,null`|A unit describing the attribute|No|
|**value**|`string,number,boolean`|The actual value of the attribute| :white_check_mark: Yes|

Additional properties are allowed.

##### attributes.name :white_check_mark: 

Name / Title of the attribute

* **Type**: `string`
* **Required**: Yes

##### attributes.type :white_check_mark: 

The type of the attribute

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"string"`
   * `"number"`
   * `"boolean"`
   * `"datetimerange"`
   * `"float"`
   * `"text"`
   * `"time"`
   * `"url"`

##### attributes.unit

A unit describing the attribute

* **Type**: `string,null`
* **Required**: No

##### attributes.value :white_check_mark: 

The actual value of the attribute

* **Type**: `string,number,boolean`
* **Required**: Yes




---------------------------------------
<a name="reference-dimensions"></a>
#### Dimensions

Dimensions of the product

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**width**|`object`|Width of an product|No|
|**length**|`object`|Length of an product|No|
|**height**|`object`|Height of an product|No|
|**weight**|`object`|Weight of an product|No|

Additional properties are not allowed.

##### dimensions.width

Width of an product

* **Type**: `object`
* **Required**: No

##### dimensions.length

Length of an product

* **Type**: `object`
* **Required**: No

##### dimensions.height

Height of an product

* **Type**: `object`
* **Required**: No

##### dimensions.weight

Weight of an product

* **Type**: `object`
* **Required**: No




---------------------------------------
<a name="reference-feed_format"></a>
#### feed_format

Format of the json file

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**encoding**|`string`|Encoding the file is using| :white_check_mark: Yes|

Additional properties are allowed.

##### feed_format.encoding :white_check_mark: 

Encoding the file is using

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"UTF-8"`




---------------------------------------
<a name="reference-inventory-list"></a>
#### Inventory List

List of inventory per location

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**$schema**|`string`|Schema the feed is implementing| :white_check_mark: Yes|
|**content_language**|`string`|The language used for content| :white_check_mark: Yes|
|**content_type**|`string`|Describes the content of the feed| :white_check_mark: Yes|
|**created_at**|`string`|The date product was created in the system|No|
|**updated_at**|`string`|The date product was updated in the system|No|
|**target_country**|`array[]`|Describe intended target audience of the feed| :white_check_mark: Yes|
|**version**|`string`|State the version of the feed| :white_check_mark: Yes|
|**feed_format**|`object`|Format of the json file|No|
|**schedule**|`object`|Signal schema generation schedule|No|
|**entities**|`object` `[]`|| :white_check_mark: Yes|

Additional properties are allowed.

##### inventory.list.$schema :white_check_mark: 

Schema the feed is implementing

* **Type**: `string`
* **Required**: Yes
* **Format**: uri

##### inventory.list.content_language :white_check_mark: 

The language used for content

* **Type**: `string`
* **Required**: Yes

##### inventory.list.content_type :white_check_mark: 

Describes the content of the feed

* **Type**: `string`
* **Required**: Yes

##### inventory.list.created_at

The date product was created in the system

* **Type**: `string`
* **Required**: No

##### inventory.list.updated_at

The date product was updated in the system

* **Type**: `string`
* **Required**: No

##### inventory.list.target_country :white_check_mark: 

Describe intended target audience of the feed

* **Type**: `array[]`
* **Required**: Yes

##### inventory.list.version :white_check_mark: 

State the version of the feed

* **Type**: `string`
* **Required**: Yes

##### inventory.list.feed_format

Format of the json file

* **Type**: `object`
* **Required**: No

##### inventory.list.schedule

Signal schema generation schedule

* **Type**: `object`
* **Required**: No

##### inventory.list.entities :white_check_mark: 

* **Type**: `object` `[]`
* **Required**: Yes




---------------------------------------
<a name="reference-price"></a>
#### price

Type of prices used

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**currency**|`string`|Format from Offer.PriceSpecification.priceCurrency| :white_check_mark: Yes|
|**value**|`number`|Format from Offer.PriceSpecification.price| :white_check_mark: Yes|
|**type**|`string`|Indicates the type of price| :white_check_mark: Yes|
|**price_effective_date**|`string`|2016-02-24T13:00-0800/2016-02-29T15:30-0800|No|
|**vat**|`number`|Format|No|

Additional properties are allowed.

##### price.currency :white_check_mark: 

Format from Offer.PriceSpecification.priceCurrency

* **Type**: `string`
* **Required**: Yes

##### price.value :white_check_mark: 

Format from Offer.PriceSpecification.price

* **Type**: `number`
* **Required**: Yes

##### price.type :white_check_mark: 

Indicates the type of price

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"regular"`
   * `"sale"`

##### price.price_effective_date

2016-02-24T13:00-0800/2016-02-29T15:30-0800

* **Type**: `string`
* **Required**: No

##### price.vat

Format

* **Type**: `number`
* **Required**: No




---------------------------------------
<a name="reference-schedule"></a>
#### schedule

Signal schema generation schedule

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**interval**|`string`|How often is the feed being re-generated by the publisher| :white_check_mark: Yes|

Additional properties are allowed.

##### schedule.interval :white_check_mark: 

How often is the feed being re-generated by the publisher

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"HOURLY"`
   * `"DAILY"`
   * `"WEEKLY"`
   * `"MONTHLY"`



### Example Product Feed
``` 

{
  "$schema" : "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/products/2017-06-28-1/products.json",
  "content_language" : "sv",
  "attribute_language" : "sv",
  "content_type" : "products",
  "target_country" : ["SE"],
  "version" : "2017-01-20-1",
  "feed_format" : {
    "encoding" : "UTF-8"
  },
  "schedule" : {
    "interval" : "HOURLY"
  },
  "entities" : [{
    "name" : "Coffe Mugg",
    "description" : "Drink coffe and feel refreshed",
    "id" : "sku",
    "gtin" : "1455582344",
    "mpn" : "43509",
    "dimensions" : {
      "height" : {
        "value" : 30,
        "unit" : "cm"
      },
      "length" : {
        "value" : 30,
        "unit" : "cm"
      },
      "width" : {
        "value" : 30,
        "unit" : "cm"
      },
      "weight" : {
        "value" : 30,
        "unit" : "kg"
      }
    },
    "categories" : [{"name":"Porslin"},{"name":"Mugg"}],
    "item_group_id" : "11",
    "prices": [{
      "currency": "SEK",
      "value": 10000,
      "price_effective_date": "2016-02-24T13:00-0800/2016-02-29T15:30-0800",
      "type": "regular",
      "vat": 10000
    }],
    "brands" : [
      {
        "name" : "Rörstrand"
      },
      {
        "name" : "Swedish"
      }
    ],
    "attributes" : [
      {
        "name" : "volume",
        "type" : "number",
        "unit" : "cl",
        "value" : 45
      },
      {
        "name" : "mikrovågsugn",
        "type" : "string",
        "unit" : null,
        "value" : "Ja"
      },
      {
        "name" : "ungssäker",
        "type" : "boolean",
        "unit" : null,
        "value" : true
      }
    ],
    "size" : null,
    "sizeSystem" : null,
    "sizeType" : null,
    "color" : "rosa",
    "gender" : null,
    "material" : "porslin",
    "pattern" : null,
    "age_group" : null,
    "condition" : "new",
    "image_link" : "https://unsplash.it/g/200/300",
    "additional_image_links" : [
      "https://unsplash.it/200/300/?random",
      "https://unsplash.it/200/300/?random"
    ],
    "link" : "https://example.com/product/page/"
  }]
}

``` 


## Inventory

### Objects
* [`feed_format`](#reference-feed_format)
* [`Inventory List`](#reference-inventory-list) (root object)
* [`price`](#reference-price)
* [`schedule`](#reference-schedule)
* [`stock_level`](#reference-stock_level)


---------------------------------------
<a name="reference-feed_format"></a>
#### feed_format

Format of the json file

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**encoding**|`string`|Encoding the file is using| :white_check_mark: Yes|

Additional properties are allowed.

##### feed_format.encoding :white_check_mark: 

Encoding the file is using

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"UTF-8"`




---------------------------------------
<a name="reference-inventory-list"></a>
#### Inventory List

List of inventory per location

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**$schema**|`string`|Schema the feed is implementing| :white_check_mark: Yes|
|**content_language**|`string`|The language used for content| :white_check_mark: Yes|
|**content_type**|`string`|Describes the content of the feed| :white_check_mark: Yes|
|**target_country**|`array[]`|Describe intended target audience of the feed| :white_check_mark: Yes|
|**version**|`string`|State the version of the feed| :white_check_mark: Yes|
|**feed_format**|`object`|Format of the json file|No|
|**schedule**|`object`|Signal schema generation schedule|No|
|**entities**|`object` `[]`|| :white_check_mark: Yes|

Additional properties are allowed.

##### inventory.list.$schema :white_check_mark: 

Schema the feed is implementing

* **Type**: `string`
* **Required**: Yes
* **Format**: uri

##### inventory.list.content_language :white_check_mark: 

The language used for content

* **Type**: `string`
* **Required**: Yes

##### inventory.list.content_type :white_check_mark: 

Describes the content of the feed

* **Type**: `string`
* **Required**: Yes

##### inventory.list.target_country :white_check_mark: 

Describe intended target audience of the feed

* **Type**: `array[]`
* **Required**: Yes

##### inventory.list.version :white_check_mark: 

State the version of the feed

* **Type**: `string`
* **Required**: Yes

##### inventory.list.feed_format

Format of the json file

* **Type**: `object`
* **Required**: No

##### inventory.list.schedule

Signal schema generation schedule

* **Type**: `object`
* **Required**: No

##### inventory.list.entities :white_check_mark: 

* **Type**: `object` `[]`
* **Required**: Yes




---------------------------------------
<a name="reference-price"></a>
#### price

Type of prices used

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**currency**|`string`|Format from Offer.PriceSpecification.priceCurrency| :white_check_mark: Yes|
|**value**|`number`|Format from Offer.PriceSpecification.price| :white_check_mark: Yes|
|**type**|`string`|Indicates the type of price| :white_check_mark: Yes|
|**price_effective_date**|`string`|2016-02-24T13:00-0800/2016-02-29T15:30-0800|No|
|**vat**|`number`|Format|No|

Additional properties are allowed.

##### price.currency :white_check_mark: 

Format from Offer.PriceSpecification.priceCurrency

* **Type**: `string`
* **Required**: Yes

##### price.value :white_check_mark: 

Format from Offer.PriceSpecification.price

* **Type**: `number`
* **Required**: Yes

##### price.type :white_check_mark: 

Indicates the type of price

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"regular"`
   * `"sale"`

##### price.price_effective_date

2016-02-24T13:00-0800/2016-02-29T15:30-0800

* **Type**: `string`
* **Required**: No

##### price.vat

Format

* **Type**: `number`
* **Required**: No




---------------------------------------
<a name="reference-schedule"></a>
#### schedule

Signal schema generation schedule

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**interval**|`string`|How often is the feed being re-generated by the publisher| :white_check_mark: Yes|

Additional properties are allowed.

##### schedule.interval :white_check_mark: 

How often is the feed being re-generated by the publisher

* **Type**: `string`
* **Required**: Yes
* **Allowed values**:
   * `"HOURLY"`
   * `"15MIN"`
   * `"30MIN"`
   * `"45MIN"`
   * `"1MIN"`




---------------------------------------
<a name="reference-stock_level"></a>
#### stock_level

Stocklevels for product

**Properties**

|   |Type|Description|Required|
|---|----|-----------|--------|
|**quantity**|`number`|Currently stocked items| :white_check_mark: Yes|
|**location**|`string`|Location of current stock| :white_check_mark: Yes|

Additional properties are allowed.

##### stock_level.quantity :white_check_mark: 

Currently stocked items

* **Type**: `number`
* **Required**: Yes

##### stock_level.location :white_check_mark: 

Location of current stock

* **Type**: `string`
* **Required**: Yes



### Example Inventory Feed
``` 

{
  "$schema" : "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/inventory/2017-06-28-1/inventory.json",
  "content_language": "sv",
  "attribute_language": "sv",
  "content_type": "inventory",
  "target_country": ["SE"],
  "version": "2017-01-20-1",
  "feed_format": {
    "encoding": "UTF-8"
  },
  "schedule": {
    "interval": "HOURLY"
  },
  "entities": [{
    "id": "43535",
    "prices": [{
      "currency": "SEK",
      "value": 10000,
      "price_effective_date": "2016-02-24T13:00-0800/2016-02-29T15:30-0800",
      "type": "regular",
      "vat": 10000
    }],
    "inventory": [{
      "location": "1",
      "quantity": 5
    },
      {
        "location": "2",
        "quantity": 5
      }
    ]
  }]
}

``` 

