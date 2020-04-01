<?php

namespace QuinnInteractive\Seo\Tests\Extensions;

use SilverStripe\Control\Director;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use QuinnInteractive\Seo\Extensions\PageSeoExtension;
use QuinnInteractive\Seo\Seo;

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
        $this->assertContains(Director::absoluteBaseURL(), Seo::getCanonicalUrlLink($this->page)[0]);
    }

    public function testArticleTags()
    {
        /** @var DBDatetime $created */
        $created = $this->page->dbObject('Created');

        /** @var DBDatetime $lastEdited */
        $lastEdited = $this->page->dbObject('LastEdited');

        $this->assertContains(
            $created->Rfc3339(),
            Seo::getArticleTags($this->page)[0]
        );
        $this->assertContains(
            $lastEdited->Rfc3339(),
            Seo::getArticleTags($this->page)[1]
        );
    }

    public function testMetaTags()
    {
        $tags = $this->page->MetaTags(false);

        $this->assertContains(Seo::getCanonicalUrlLink($this->page)[0], $tags);
        $this->assertContains(Seo::getArticleTags($this->page)[0], $tags);
        $this->assertContains(Seo::getArticleTags($this->page)[1], $tags);
        $this->assertContains(implode(PHP_EOL, Seo::getFacebookMetaTags($this->page)), $tags);
        $this->assertContains(implode(PHP_EOL, Seo::getTwitterMetaTags($this->page)), $tags);
    }
}
