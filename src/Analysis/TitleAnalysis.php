<?php

namespace Vulcan\Seo\Analysis;

/**
 * Class TitleAnalysis
 * @package Vulcan\Seo\Analysis
 */
class TitleAnalysis extends Analysis
{
    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        $title = $this->getPage()->Title;
        $keyword = $this->getPage()->FocusKeyword;

        if (strtolower($title) == 'home') {
            return -1;
        }

        if (strlen($title) < 25) {
            return 0;
        }

        if (strlen($title) < 40) {
            return 1;
        }

        if (strlen($title) > 70) {
            return 2;
        }

        if ($keyword && !strstr(strtolower($title), strtolower($keyword))) {
            return 3;
        }

        if ($keyword && strtolower(substr($title, 0, strlen($keyword))) !== strtolower($keyword)) {
            return 4;
        }

        return 5;
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            -1 => ['The page title should be changed from "Home", these titles almost always reduce click-through rate, though ensure to leave the navigation label as-is', 'danger'],
            0 => ['The page title is too short', 'danger'],
            1 => ['The page title is a little short, but is above the absolute character minimum of 25', 'warning'],
            2 => ['The page title is too long', 'danger'],
            3 => ['The page title does not contain the focus keyword', 'warning'],
            4 => ['The page title contains the focus keyword but is not at the beginning, consider moving it to the beginning', 'warning'],
            5 => ['The page title is between the 40 character minimum and the recommended 70 character maximum', 'success']
        ];
    }
}
