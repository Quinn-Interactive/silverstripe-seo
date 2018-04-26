<?php

namespace Vulcan\Seo\Analysis;

/**
 * Class FocusKeywordContentAnalysis
 * @package Vulcan\Seo\Analysis
 */
class FocusKeywordContentAnalysis extends Analysis
{
    const FOCUS_KEYWORD_UNSET = -1;
    const FOCUS_KEYWORD_NOT_FOUND = 0;
    const FOCUS_KEYWORD_SUCCESS = 1;

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
            return static::FOCUS_KEYWORD_UNSET;
        }

        if (!strstr(strtolower($this->getContent()), $this->getKeyword())) {
            return static::FOCUS_KEYWORD_NOT_FOUND;
        }

        return static::FOCUS_KEYWORD_SUCCESS;
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::FOCUS_KEYWORD_UNSET     => [
                'The focus keyword has not been set, consider setting this to improve content analysis',
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
     * By default, this will only check the default "Content" field, override $seo_content_fields in the correct order
     * of display to include other fields
     *
     * @deprecated 2.0 Use $this->getContent() instead
     *
     * @return string
     */
    public function getContentFromDom()
    {
        return strtolower($this->getContent());
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return strtolower($this->getPage()->FocusKeyword);
    }

    /**
     * Find occurrences of the focus keyword in the rendered content
     */
    public function findOccurrences()
    {
        $content = strtolower($this->getContent());

        if (!strlen($content) || !$this->getKeyword()) {
            return 0;
        }

        return substr_count($content, $this->getKeyword());
    }
}
