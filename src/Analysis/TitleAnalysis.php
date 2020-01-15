<?php

namespace QuinnInteractive\Seo\Analysis;

/**
 * Class TitleAnalysis
 * @package QuinnInteractive\Seo\Analysis
 */
class TitleAnalysis extends Analysis
{
    const TITLE_FOCUS_KEYWORD_POSITION = 4;
    const TITLE_IS_HOME                = -1;
    const TITLE_NO_FOCUS_KEYWORD       = 3; // only checked if the focus keyword has been defined
    const TITLE_OK_BUT_SHORT           = 1;
    const TITLE_SUCCESS                = 5;
    const TITLE_TOO_LONG               = 2;
    const TITLE_TOO_SHORT              = 0;

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::TITLE_IS_HOME                => [
                'The page title should be changed from "Home"; ' .
                'that title almost always reduces click-through rate. ' .
                'Please retain "Home" as the Navigation Label, however.',
                'danger'
            ],
            static::TITLE_TOO_SHORT              => ['The page title is too short', 'danger'],
            static::TITLE_OK_BUT_SHORT           => [
                'The page title is a little short but is above the absolute character minimum of 25',
                'warning'
            ],
            static::TITLE_TOO_LONG               => ['The page title is too long', 'danger'],
            static::TITLE_NO_FOCUS_KEYWORD       => ['The page title does not contain the focus keyword', 'warning'],
            static::TITLE_FOCUS_KEYWORD_POSITION => [
                'The page title contains the focus keyword but is not at the beginning; ' .
                'consider moving it to the beginning',
                'warning'
            ],
            static::TITLE_SUCCESS                => [
                'The page title is between the 40 character minimum and the recommended 70 character maximum',
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
        $title   = $this->getPage()->Title;
        $keyword = $this->getPage()->FocusKeyword;

        if (strtolower($title) == 'home') {
            return static::TITLE_IS_HOME;
        }

        if (strlen($title) < 25) {
            return static::TITLE_TOO_SHORT;
        }

        if (strlen($title) < 40) {
            return static::TITLE_OK_BUT_SHORT;
        }

        if (strlen($title) > 70) {
            return static::TITLE_TOO_LONG;
        }

        if ($keyword && !strstr(strtolower($title), strtolower($keyword))) {
            return static::TITLE_NO_FOCUS_KEYWORD;
        }

        if ($keyword && strtolower(substr($title, 0, strlen($keyword))) !== strtolower($keyword)) {
            return static::TITLE_FOCUS_KEYWORD_POSITION;
        }

        return 5;
    }
}
