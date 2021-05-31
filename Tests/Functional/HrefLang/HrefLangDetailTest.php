<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

/**
 * check if record of detail page also exists in the current language
 */
class HrefLangDetailTest extends AbstractHrefLangTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $fixtureRootPath = ORIGINAL_ROOT . 'typo3conf/ext/cs_seo/Tests/Functional/Fixtures/';

        $xmlFiles = [
            'pages-hreflang',
            'sys_category',
            'tx_csseo_domain_model_meta'
        ];

        foreach ($xmlFiles as $xmlFile) {
            $this->importDataSet($fixtureRootPath . 'Database/' . $xmlFile . '.xml');
        }

        $typoScriptFiles = [
            $fixtureRootPath . '/TypoScript/page.typoscript',
            'EXT:cs_seo/Configuration/TypoScript/setup.typoscript'
        ];

        $sitesNumbers = [1];
        foreach ($sitesNumbers as $siteNumber) {
            $sites = [];
            $sites[$siteNumber] = $fixtureRootPath . 'Sites/' . $siteNumber . '/config.yaml';
            $this->setUpFrontendRootPage($siteNumber, $typoScriptFiles, $sites);
        }
    }

    /**
     * @return array
     */
    public function checkHrefLangOutputDataProvider(): array
    {
        return [
            'sys_category: 1 without metadata' => [
                'http://localhost/hello/category/1',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello/category/1"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen/category/1"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen/category/1"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello/category/1"/>'
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom/category/1"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom/category/1"/>',
                    '<link rel="alternate" href="http://localhost/nl/welkom/category/1"/>'
                ]
            ],
            'sys_category: 1 in language 1' => [
                'http://localhost/de/willkommen/category/1',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello/category/1"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen/category/1"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen/category/1"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello/category/1"/>'
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom/category/1"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom/category/1"/>',
                    '<link rel="alternate" href="http://localhost/nl/welkom/category/1"/>'
                ]
            ],
            'sys_category: 1 in lang 2 with fallback to 1' => [
                'http://localhost/de-ch/willkommen/category/1',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello/category/1"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen/category/1"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen/category/1"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello/category/1"/>'
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom/category/1"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom/category/1"/>',
                    '<link rel="alternate" href="http://localhost/nl/welkom/category/1"/>'
                ]
            ],
            'sys_category: 2 with no translation' => [
                'http://localhost/hello/category/2',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'sys_category: 3 with canonical from metadata' => [
                'http://localhost/hello/category/3',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'sys_category: 4 with no_index' => [
                'http://localhost/hello/category/4',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
        ];
    }
}
