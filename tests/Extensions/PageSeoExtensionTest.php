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
        $this->assertContains(Director::absoluteBaseURL(), Seo::getCanonicalUrlLink($this->page));
    }

    public function testArticleTags()
    {
        /** @var DBDatetime $created */
        $created = $this->page->dbObject('Created');

        /** @var DBDatetime $lastEdited */
        $lastEdited = $this->page->dbObject('LastEdited');

        $this->assertContains(
            $created->Rfc3339(),
            Seo::getArticleTags($this->page)
        );
        $this->assertContains(
            $lastEdited->Rfc3339(),
            Seo::getArticleTags($this->page)
        );
    }

    public function testMetaComponents()
    {
        $tags = $this->page->MetaComponents();

        $this->assertContains(Seo::getCanonicalUrlLink($this->page), $tags['canonical']['attributes']);

        $this->assertContains(Seo::getArticleTags($this->page)['article:published_time'], $tags['article:published_time']['attributes']);
        $this->assertContains(Seo::getArticleTags($this->page)['article:modified_time'], $tags['article:modified_time']['attributes']);

        $this->assertContains(Seo::getFacebookMetaTags($this->page)['og:title'], $tags['og:title']['attributes']);
        $this->assertContains(Seo::getFacebookMetaTags($this->page)['og:description'], $tags['og:description']['attributes']);
        $this->assertContains(Seo::getFacebookMetaTags($this->page)['og:type'], $tags['og:type']['attributes']);
        $this->assertContains(Seo::getFacebookMetaTags($this->page)['og:url'], $tags['og:url']['attributes']);
        $this->assertContains(Seo::getFacebookMetaTags($this->page)['og:locale'], $tags['og:locale']['attributes']);
        $this->assertContains(Seo::getFacebookMetaTags($this->page)['og:site_name'], $tags['og:site_name']['attributes']);

        $this->assertContains(Seo::getTwitterMetaTags($this->page)['twitter:card'], $tags['twitter:card']['attributes']);
        $this->assertContains(Seo::getTwitterMetaTags($this->page)['twitter:title'], $tags['twitter:title']['attributes']);
        $this->assertContains(Seo::getTwitterMetaTags($this->page)['twitter:description'], $tags['twitter:description']['attributes']);
    }
}
