<?php

namespace QuinnInteractive\Seo\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

/**
 * Class MemberExtension
 * @package QuinnInteractive\Seo\Extensions
 *
 * @property string TwitterAccountName
 */
class MemberExtension extends Extension
{
    private static $db = [
        'TwitterAccountName' => 'Varchar(80)'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        if (PageSeoExtension::config()->get('enable_creator_tag')) {
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('TwitterAccountName')
            ], 'Password');
        } else {
            $fields->removeByName('TwitterAccountName');
        }
    }
}
