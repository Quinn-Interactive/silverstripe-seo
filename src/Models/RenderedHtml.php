<?php

namespace Vulcan\Seo\Models;

use SilverStripe\ORM\DataObject;

/**
 * Class RenderedHtml
 * @package Vulcan\Seo\Models
 *
 * @property string Result
 *
 * @property int    PageID
 *
 * @method \Page Page
 */
class RenderedHtml extends DataObject
{
    private static $table_name = 'RenderedHtml';

    private static $db = [
        'Result' => 'Text'
    ];

    private static $has_one = [
        'Page' => \Page::class
    ];

    public static function findOrMake(\Page $page)
    {
        $record = static::get()->filter([
            'PageID' => $page->ID
        ])->first();

        if ($record) {
            return $record;
        }

        $record = static::create();
        $record->PageID = $page->ID;
        $record->Result = file_get_contents($page->AbsoluteLink());
        $record->write();

        return $record;
    }

    public function refresh()
    {
        $this->Result = file_get_contents($this->Page()->AbsoluteLink());
        $this->write();
    }
}
