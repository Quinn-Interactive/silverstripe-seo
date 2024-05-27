<?php

if (!class_exists('Page') && class_exists('\SilverStripe\CMS\Model\SiteTree')) {
    class Page extends \SilverStripe\CMS\Model\SiteTree
    {
        private static $table_name = 'Page';

        private static $db = [
            'Field' => 'Varchar'
        ];
    }
}

if (!class_exists('PageController') && class_exists('\SilverStripe\CMS\Controllers\ContentController')) {
    class PageController extends \SilverStripe\CMS\Controllers\ContentController
    {
    }
}

require_once dirname(__DIR__) . '/vendor/silverstripe/framework/tests/bootstrap.php';
