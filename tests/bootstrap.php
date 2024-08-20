<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Controllers\ContentController;

if (!class_exists('Page') && class_exists(SiteTree::class)) {
    class Page extends SiteTree
    {
        private static $table_name = 'Page';

        private static $db = [
            'Field' => 'Varchar'
        ];
    }
}

if (!class_exists('PageController') && class_exists(ContentController::class)) {
    class PageController extends ContentController
    {
    }
}

require_once dirname(__DIR__) . '/vendor/silverstripe/framework/tests/bootstrap.php';
