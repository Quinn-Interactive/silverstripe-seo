<?php

namespace Vulcan\Seo\Analysis;

/**
 * Class WordCountAnalysis
 * @package Vulcan\Seo\Analysis
 */
class WordCountAnalysis extends Analysis
{
    /**
     * You must override this in your subclass and perform your own checks. An integer must be returned
     * that references an index of the array you return in your response() method override in your subclass.
     *
     * @return int
     */
    public function run()
    {
        $wordCount = $this->getWordCount();

        if ($wordCount < 300) {
            return 0;
        }

        return 1;
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            0 => ['The content of this page contains ' . $this->getWordCount() . ' words which is less than the 300 recommended minimum', 'danger'],
            1 => ['The content of this page contains ' . $this->getWordCount() . ' which is above the 300 recommended minimum', 'success'],
        ];
    }

    /**
     * @return int
     */
    public function getWordCount()
    {
        return count(array_filter(explode(' ', $this->getPage()->collateContentFields())));
    }
}
