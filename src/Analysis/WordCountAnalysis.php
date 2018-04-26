<?php

namespace Vulcan\Seo\Analysis;

use Vulcan\Seo\Seo;

/**
 * Class WordCountAnalysis
 * @package Vulcan\Seo\Analysis
 */
class WordCountAnalysis extends Analysis
{
    const WORD_COUNT_BELOW_MIN = 0;
    const WORD_COUNT_ABOVE_MIN = 1;

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
            return static::WORD_COUNT_BELOW_MIN;
        }

        return static::WORD_COUNT_ABOVE_MIN;
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::WORD_COUNT_BELOW_MIN => [
                'The content of this page contains ' . $this->getWordCount() . ' words which is less than the 300 recommended minimum',
                'danger'
            ],
            static::WORD_COUNT_ABOVE_MIN => [
                'The content of this page contains ' . $this->getWordCount() . ' which is above the 300 recommended minimum',
                'success'
            ],
        ];
    }

    /**
     * @return int
     */
    public function getWordCount()
    {
        $this->getContent();
        return count(explode(' ', $this->getContent()));
    }
}
