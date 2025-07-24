<?php

namespace QuinnInteractive\Seo\Forms;

use SilverStripe\Forms\FormField;
use QuinnInteractive\Seo\Analysis\Analysis;
use QuinnInteractive\Seo\Extensions\PageHealthExtension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\View\Requirements;

/**
 * Class HealthAnalysisField
 * @package QuinnInteractive\Seo\Forms
 */
class HealthAnalysisField extends LiteralField
{

    /**
     * @var \Page
     */
    protected $page;

    /**
     * @var int
     */
    protected $result;
    protected $schemaComponent = 'HealthAnalysisField';

    protected $template = self::class;

    /**
     * HealthAnalysisField constructor.
     *
     * @param string                               $name
     * @param FormField|string $title
     * @param \Page                                $page
     */
    public function __construct($name, $title, SiteTree $page)
    {
        $this->setPage($page);
        Requirements::javascript('quinninteractive/silverstripe-seo:dist/javascript/main.min.js');
        Requirements::css('quinninteractive/silverstripe-seo:dist/css/styles.min.css');

        parent::__construct($name, ArrayData::create(
            [
                'Title'      => $title,
                'Results'    => $this->runAnalyses(),
            ]
        )->renderWith(self::class));
    }

    /**
     * Fetches a list of all Analysis subclasses
     *
     * @return array
     */
    public function getAnalyses()
    {
        $classes = ClassInfo::subclassesFor(Analysis::class);
        $output  = [];

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
     * @return SiteTree|PageHealthExtension
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Runs all analyses and returns an ArrayList
     *
     * @return ArrayList
     */
    public function runAnalyses()
    {
        $analyses = $this->getAnalyses();
        $output   = ArrayList::create([]);

        foreach ($analyses as $analysisClass) {
            /** @var Analysis $analysis */
            $analysis = $analysisClass::create($this->getPage());
            try {
                $output->push($analysis->inspect());
            } catch (\Exception $e) {
                $output->push(
                    ArrayData::create(
                        [
                            'Title'   => 'An error occurred',
                            'Message' => $e->getMessage(),
                            'Type'    => 'danger',
                        ]
                    )
                );
            }
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
}
