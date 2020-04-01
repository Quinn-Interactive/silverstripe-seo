<?php

namespace QuinnInteractive\Seo\Tests\Analysis;

use SilverStripe\Dev\FunctionalTest;

class AnalysisTest extends FunctionalTest
{
    protected static $fixture_file = '../Pages.yml';

    public function testMethodsExist()
    {
        /** @var \Page $page */
        $page = $this->objFromFixture('Page', 'one');
        $testClass = TestAnalysis::create($page);

        $this->assertTrue(method_exists($testClass, 'inspect'));
        $this->assertTrue(method_exists($testClass, 'run'));
        $this->assertTrue(method_exists($testClass, 'responses'));

        $this->expectException(\RuntimeException::class);
        $testClass->inspect();
        $this->expectException(\RuntimeException::class);
        $testClass->run();
    }
}
