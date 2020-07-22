# silverstripe-seo

An all-in-one SEO module for SilverStripe.

**Note:** Version 1.0.4 of this repository replaces version 1.0.3 of `vulcandigital/silverstripe-seo`.

## Features

* SEO Health Analysis in the Page Editor ![SEO Health Analysis](https://i.imgur.com/L2MTFDd.png)
* Automatic Facebook OpenGraph meta-tag generation (can override) ![Facebook SEO Control](https://i.imgur.com/FcK0ExJ.png)
* Automatic Twitter meta-tag generation (can override) ![Twitter SEO Control](https://i.imgur.com/7I4rnXw.png)
    * Also adds a `TwitterAccountName` field to `SilverStripe\Security\Member` which is used for the creator tag. The creator is recorded when a new page is created and their Twitter account name will be used for the meta tag

### Example Meta Tags Output

```html
<link rel="canonical" href="http://atmtanks.local/"/>
<meta property="og:title" content="Home"/>
<meta property="og:description" content="ATM Industrial Panel Tanks (ATM) specialises in tank builds, modifications and maintenance. ATM has performed significant tank refurbishments, re-lines and roof replacements for Government Hospitals, Power Stations, Food Process Companies, Mines and more."/>
<meta property="og:type" content="website"/>
<meta property="og:url" content="http://atmtanks.local/"/>
<meta property="og:locale" content="en_GB" />
<meta property="og:site_name" content="ATM Industrial Panel Tanks" />
<meta name="twitter:card" content="summary"/>
<meta name="twitter:title" content="Home"/>
<meta name="twitter:description" content="ATM Industrial Panel Tanks (ATM) specialises in tank builds, modifications and maintenance. ATM has performed significant tank refurbishments, re-lines and roof replacements for Government Hospitals, Power Stations, Food Process Companies, Mines and more."/>
<meta name="twitter:creator" content="@zanderwar"/>
<meta name="twitter:site" content="@vulcandigital" />
<meta property="article:published_time" content="2018-04-08T00:22:10+10:00" />
<meta property="article:modified_time" content="2018-04-16T21:52:52+10:00" />
```

If you think you can add something beneficial to this output, please don't hesitate to submit a PR or open an issue to discuss its addition. See [CONTRIBUTING.md](CONTRIBUTING.md).

## Requirements

See [composer.json](composer.json) for details.

## Installation

```bash
composer require quinninteractive/silverstripe-seo
```

## Getting Started

The necessary extensions are automatically applied after installation of this module, and a dev/build.

## Writing Your Own Analysis

Health analyses have been abstracted to give developers the ability to create their own analysis checks.

To do this, you simply need to create a new class that extends `QuinnInteractive\Seo\Analysis\Analysis`.

As an example, let's create a new analysis that checks to see if `Hello World!` is the title of the current page.

First create the following file:

`mysite\code\Analysis\HelloWorldTitleAnalysis.php`

```php
<?php

namespace Vendor\Project\Analysis;

use QuinnInteractive\Seo\Analysis\Analysis;

class HelloWorldTitleAnalysis extends Analysis
{
    const FAILED = 0;
    const SUCCESS = 1;

    public function run()
    {
        if (!strstr($this->getPage()->Title, 'Hello World!')) {
            return static::FAILED;
        }

        return static::SUCCESS;
    }

    public function responses()
    {
        return [
            static::FAILED  => ['The string "Hello World!" does not appear in the page title', 'danger'],
            static::SUCCESS => ['Hoorah!!! "Hello World!" appears in the page title', 'success'],
        ];
    }
}
```

Then dev/build. You will immediately see this new analysis running in the CMS under the "SEO Health Analysis" accordion when editing any page, then change the title to include "Hello World" and you will notice the indicator will display success.

One thing to keep in mind is that the analysis always has access to the `\Page` object that it is running against via `$this->getPage()`, so your responses can also be dynamic.

If you have created an analysis and think it would be beneficial as an addition to this module then we urge you to submit a Pull Request and you will receive full credit for your work. See [CONTRIBUTING.md](CONTRIBUTING.md).

### Explained: `run()`

You must override this method as this is where you will perform all your checks, and then return with an integer respective of the keys you define in `responses()`. It's a good idea to use constants that represent those integers for readability

### Explained: `responses()`

All analyses must override the `responses()` method to provide response messages and the response level (which is used for the indicator).

`run()` should return an integer that matches a key in the array that `responses()` returns, for example if `run()` were to return `1`, then using the above example the message displayed would be `Hoorah!!! "Hello World!" appears in the page title` with an indicator level of `success`

The available indicator levels are: `default`, `danger`, `warning`, `success` which are grey, red, orange and green respectively.

You can optionally prevent certain levels from displaying in the content analysis tab. The following added to the above example would cause it to only display an entry if the indicator level is not of value `success`:

```php
private static $hidden_levels = [
    'success'
];
```

## Configuration Options

### `enable_creator_tag`

By default, this module adds an extension to `\SilverStripe\Security\Member` that adds a single field named `TwitterAccountName`, if this is set
and when this particular user creates a page, the `twitter:creator` meta tag will automatically generate with the Members account name

You can disable this via YML:

```yml
QuinnInteractive\Seo\Extensions\PageSeoExtension:
    enable_creator_tag: false
```

## Assumptions

This module assumes that you make use of the default `Content` field provided by `\Page`. If a specific page does not then you can specify one or multiple fields that contain your content.

They should be ordered in the correct order that they appear for the end user

In your `\Page` subclass you would have:

```php
public function seoContentFields()
{
    return [
        'Content',
        'MyBlock.Title',
        'MyBlock.Content',
        'BottomTitle',
        'BottomContent',
    ];
}
```

## Roadmap (subject to change)

* Finish implementing internationalisation to this module and its analyses
* More content analyses
* Given the ability to practically have content coming from anywhere on a SilverStripe page, the `seoContentFields` method was introduced to better improve content analysis by collating all content fields into a single string, this supports dot notation for `has_one` relationships, but may not (or does not) support `has_many` and `many_many` relationships at this time. Ideally moving forward we will want to use the DOM parser (partially implemented) and rely on this instead.
* Finding community support to help improve and better this module for all SilverStripe users

## License

[BSD-3-Clause](LICENSE.md)

## Version

1.0.12
