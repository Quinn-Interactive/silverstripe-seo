<?php

namespace QuinnInteractive\Seo\Analysis;

/**
 * Class FocusKeywordContentAnalysis
 * @package QuinnInteractive\Seo\Analysis
 */
class FocusKeywordContentAnalysis extends Analysis
{
    public const FOCUS_KEYWORD_NOT_FOUND = 0;
    public const FOCUS_KEYWORD_SUCCESS   = 1;
    public const FOCUS_KEYWORD_UNSET     = -1;

    private static $hidden_levels = [
        'default'
    ];

    /**
     *
     */
    public function findOccurrences()
    {
        $content = $this->getContentFromDom();

        if (!strlen($content) || !$this->getKeyword()) {
            return 0;
        }

        return substr_count($content, $this->getKeyword());
    }

    /**
     * By default, this will only check the default "Content" field, override $seo_content_fields in the correct order
     * of display to include other fields
     *
     * @return string
     */
    public function getContentFromDom()
    {
        $dom    = $this->getPage()->getRenderedHtmlDomParser();

        if (!$dom) {
           return '';
        }


        $result = $dom->find('body', 0);

        return strtolower(strip_tags($result ? $result->innertext() : ''));
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        if ($keyword = $this->getPage()->FocusKeyword) {
            return strtolower($keyword);
        }

        return '';
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::FOCUS_KEYWORD_UNSET     => [
                'The focus keyword has not been set; consider setting this to improve content analysis',
                'default'
            ],
            static::FOCUS_KEYWORD_NOT_FOUND => [
                'The focus keyword was not found in the content of this page',
                'danger'
            ],
            static::FOCUS_KEYWORD_SUCCESS   => [
                'The focus keyword was found <strong>' . $this->findOccurrences() . '</strong> times.',
                'success'
            ]
        ];
    }

    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        if (!$this->getKeyword()) {
            return static::FOCUS_KEYWORD_UNSET;
        }

        if (!strstr($this->getContentFromDom(), $this->getKeyword())) {
            return static::FOCUS_KEYWORD_NOT_FOUND;
        }

        return static::FOCUS_KEYWORD_SUCCESS;
    }
}
