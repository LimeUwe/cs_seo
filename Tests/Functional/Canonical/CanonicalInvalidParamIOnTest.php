<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\Canonical;

use Nimut\TestingFramework\Http\Response;
use PHPUnit\Util\PHP\DefaultPhpProcess;
use TYPO3\CMS\Core\Tests\Functional\SiteHandling\SiteBasedTestTrait;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case, inspired by typo3/cms-seo extension
 *
 * Moutnpoints point here to there original URL to avoid duplicated content
 */
class CanonicalInvalidParamIOnTest extends AbstractCanonicalTest
{
    protected $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'cs_seo' => [
                'useAdditionalCanonicalizedUrlParametersOnly' => false,
            ],
        ]
    ];

    public function generateDataProvider(): array
    {
        return [
            // cs_seo tests
            'keep invalid param' => [
                'http://localhost/dummy-1-2-5?foo=bar',
                '<link rel="canonical" href="http://localhost/dummy-1-2-5?foo=bar',
            ],
        ];
    }
}