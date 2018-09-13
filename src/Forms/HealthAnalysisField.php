<?php

namespace Vulcan\Seo\Forms;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use Vulcan\Seo\Analysis\Analysis;
use Vulcan\Seo\Extensions\PageHealthExtension;

/**
 * Class HealthAnalysisField
 * @package Vulcan\Seo\Forms
 */
class HealthAnalysisField extends LiteralField
{
    protected $schemaComponent = 'HealthAnalysisField';

    protected $template = self::class;

    /**
     * @var int
     */
    protected $result;

    /**
     * @var \Page
     */
    protected $page;

    /**
     * HealthAnalysisField constructor.
     *
     * @param string                               $name
     * @param \SilverStripe\Forms\FormField|string $title
     * @param \Page                                $page
     */
    public function __construct($name, $title, SiteTree $page)
    {
        $this->setPage($page);
        Requirements::javascript('vulcandigital/silverstripe-seo:dist/javascript/main.min.js');
        Requirements::css('vulcandigital/silverstripe-seo:dist/css/styles.min.css');

        parent::__construct($name, ArrayData::create(['Title' => $title, 'Results' => $this->runAnalyses()])->renderWith(self::class));
    }

    /**
     * Runs all analyses and returns an ArrayList
     *
     * @return ArrayList
     */
    public function runAnalyses()
    {
        $analyses = $this->getAnalyses();
        $output = ArrayList::create([]);

        foreach ($analyses as $analysisClass) {
            /** @var Analysis $analysis */
            $analysis = $analysisClass::create($this->getPage());
            $output->push($analysis->inspect());
        }

        return $output;
    }

    /**
     * Fetches a list of all Analysis subclasses
     *
     * @return array
     */
    public function getAnalyses()
    {
        $classes = ClassInfo::subclassesFor(Analysis::class);
        $output = [];

        /** @var Analysis $class */
        foreach ($classes as $class) {
            if ($class === Analysis::class) {
                continue;
            }

            $output[] = $class;
        }

        return $output;
    }

    /**
     * @param SiteTree $page
     * @return $this
     */
    public function setPage(SiteTree $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return SiteTree|PageHealthExtension
     */
    public function getPage()
    {
        return $this->page;
    }
}
