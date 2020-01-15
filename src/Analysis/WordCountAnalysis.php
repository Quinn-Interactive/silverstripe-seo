<?php

namespace QuinnInteractive\Seo\Analysis;

use QuinnInteractive\Seo\Seo;

/**
 * Class WordCountAnalysis
 * @package QuinnInteractive\Seo\Analysis
 */
class WordCountAnalysis extends Analysis
{
    const WORD_COUNT_ABOVE_MIN = 1;
    const WORD_COUNT_BELOW_MIN = 0;

    /**
     * @return int
     */
    public function getWordCount()
    {
        return count(array_filter(explode(' ', Seo::collateContentFields($this->getPage()))));
    }

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::WORD_COUNT_BELOW_MIN => [
                sprintf(
                    'The content of this page contains %s words which is less than the 300 recommended minimum',
                    $this->getWordCount()
                ),
                'danger'
            ],
            static::WORD_COUNT_ABOVE_MIN => [
                sprintf(
                    'The content of this page contains %s words which is above the 300 recommended minimum',
                    $this->getWordCount()
                ),
                'success'
            ],
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
        $wordCount = $this->getWordCount();

        if ($wordCount < 300) {
            return static::WORD_COUNT_BELOW_MIN;
        }

        return static::WORD_COUNT_ABOVE_MIN;
    }
}
