<?php

namespace QuinnInteractive\Seo\Analysis;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\i18n\i18n;

/**
 * Class MetaDescAnalysis
 * @package QuinnInteractive\Seo\Analysis
 */
class MetaDescAnalysis extends Analysis
{
    use Configurable;

    public const META_DESC_NO_FOCUS_KEYWORD = 2; // only checked if the focus keyword has been defined
    public const META_DESC_SUCCESS = 3;
    public const META_DESC_TOO_LONG = 1;
    public const META_DESC_TOO_SHORT = 0;
    public const META_DESC_UNSET = -1;
    private static $meta_desc_target_length = 160;
    private static $meta_desc_too_long_threshold = 320;

    /**
     * @return array
     */
    public function responses()
    {
        return [
            static::META_DESC_UNSET
                => [i18n::_t(
                    'VULCANSEO.Analysis.MetaDescNotSet',
                    'The meta description has not been set; ' .
                    'a potentially unwanted snippet may be taken from the page and displayed instead'
                ), 'danger'],
            static::META_DESC_TOO_SHORT
                => [i18n::_t(
                    'VULCANSEO.Analysis.MetaDescTooShort',
                    'The meta description is too short'
                ), 'danger'],
            static::META_DESC_TOO_LONG
                => [i18n::_t(
                    'VULCANSEO.Analysis.MetaDescTooLong',
                    'The meta description is too long'
                ), 'danger'],
            static::META_DESC_NO_FOCUS_KEYWORD
                => [i18n::_t(
                    'VULCANSEO.Analysis.MetaDescMissingFocusKeyword',
                    'The meta description does not contain the focus keyword'
                ), 'warning'],
            static::META_DESC_SUCCESS
                => [i18n::_t(
                    'VULCANSEO.Analysis.MetaDescPerfect',
                    'The meta description is perfect!'
                ), 'success']
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
        $desc = $this->getPage()->MetaDescription;
        $keyword = $this->getPage()->FocusKeyword;

        if (!$desc) {
            return static::META_DESC_UNSET;
        }

        if (strlen($desc) < static::config()->get('meta_desc_target_length')) {
            return static::META_DESC_TOO_SHORT;
        }

        if (strlen($desc) > static::config()->get('meta_desc_too_long_threshold')) {
            return static::META_DESC_TOO_LONG;
        }

        if ($keyword && !strstr(strtolower($desc), strtolower($keyword))) {
            return static::META_DESC_NO_FOCUS_KEYWORD;
        }

        return static::META_DESC_SUCCESS;
    }
}
