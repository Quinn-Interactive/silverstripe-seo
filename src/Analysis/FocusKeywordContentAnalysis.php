<?php

namespace Vulcan\Seo\Analysis;

/**
 * Class FocusKeywordContentAnalysis
 * @package Vulcan\Seo\Analysis
 */
class FocusKeywordContentAnalysis extends Analysis
{
    private static $hidden_levels = [
        'default'
    ];

    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        if (!$this->getKeyword()) {
            return 0;
        }

        if (!strstr($this->getContent(), $this->getKeyword())) {
            return 1;
        }

        return 2;
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            0 => ['The focus keyword has not been set, consider setting this to improve content analysis', 'default'],
            1 => ['The focus keyword was not found in the content of this page', 'danger'],
            2 => ['The focus keyword was found <strong>' . $this->findOccurrences() . '</strong> times.', 'success']
        ];
    }

    /**
     * By default, this will only check the default "Content" field, override $seo_content_fields in the correct order of display
     * to include other fields
     *
     * @return string
     */
    public function getContent()
    {
        $dom = $this->getPage()->getRenderedHtmlDomParser();
        $result = $dom->find('body', 0);

        return strtolower(strip_tags($result ? $result->innertext() : ''));
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return strtolower($this->getPage()->FocusKeyword);
    }

    /**
     *
     */
    public function findOccurrences()
    {
        if (!strlen($this->getContent()) || !$this->getKeyword()) {
            return 0;
        }

        return substr_count($this->getContent(), $this->getKeyword());
    }
}
