<?php

namespace Vulcan\Seo\Analysis;

use SilverStripe\i18n\i18n;

/**
 * Class MetaDescAnalysis
 * @package Vulcan\Seo\Analysis
 */
class MetaDescAnalysis extends Analysis
{
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
            return 0;
        }

        if (strlen($desc) < 160) {
            return 1;
        }

        if (strlen($desc) > 320) {
            return 2;
        }

        if ($keyword && !strstr(strtolower($desc), strtolower($keyword))) {
            return 3;
        }

        if ($keyword && strtolower(substr($desc, 0, strlen($keyword))) !== strtolower($keyword)) {
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
            0 => [i18n::_t('VULCANSEO.Analysis.MetaDescNotSet', 'The meta description has not been set, a potentially unwanted snippet may be taken from the page and displayed instead'), 'danger'],
            1 => [i18n::_t('VULCANSEO.Analysis.MetaDescTooShort', 'The meta description is too short'), 'danger'],
            2 => [i18n::_t('VULCANSEO.Analysis.MetaDescTooLong', 'The meta description is too long'), 'danger'],
            3 => [i18n::_t('VULCANSEO.Analysis.MetaDescMissingFocusKeyword', 'The meta description does not contain the focus keyword'), 'warning'],
            4 => [i18n::_t('VULCANSEO.Analysis.MetaDescMissingFocusKeywordAtStart', 'The meta description contains the focus keyword but is not at the beginning'), 'warning'],
            5 => [i18n::_t('VULCANSEO.Analysis.MetaDescPerfect', 'The meta description is perfect!'), 'success']
        ];
    }
}
