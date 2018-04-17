<?php

namespace Vulcan\Seo\Tests\Extensions;

use SilverStripe\Control\Director;
use SilverStripe\Dev\FunctionalTest;
use Vulcan\Seo\Extensions\PageSeoExtension;

class PageSeoExtensionTest extends FunctionalTest
{
    protected static $fixture_file = '../Pages.yml';

    /** @var \Page|PageSeoExtension */
    protected $page;

    public function setUp()
    {
        parent::setUp();

        $this->page = $this->objFromFixture(\Page::class, 'one');
    }

    public function testPageHasExtension()
    {
        $this->assertTrue($this->page->hasExtension(PageSeoExtension::class));

        $this->assertInstanceOf(\Page::class, $this->page->getOwner());
    }

    public function testCanonicalLink()
    {
        $this->assertContains(Director::absoluteBaseURL(), $this->page->getCanonicalUrlLink()[0]);
    }

    public function testArticleTags()
    {
        $this->assertContains(
            $this->page->dbObject('Created')->Rfc3339(),
            $this->page->getArticleTags()[0]
        );
        $this->assertContains(
            $this->page->dbObject('LastEdited')->Rfc3339(),
            $this->page->getArticleTags()[1]
        );
    }

    public function testMetaTags()
    {
        $tags = $this->page->MetaTags(false);

        $this->assertContains($this->page->getCanonicalUrlLink()[0], $tags);
        $this->assertContains($this->page->getArticleTags()[0], $tags);
        $this->assertContains($this->page->getArticleTags()[1], $tags);
        $this->assertContains(implode(PHP_EOL, $this->page->getFacebookMetaTags()), $tags);
        $this->assertContains(implode(PHP_EOL, $this->page->getTwitterMetaTags()), $tags);
    }
}
