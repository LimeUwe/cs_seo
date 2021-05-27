<?php

declare(strict_types=1);

namespace Clickstorm\CsSeo\Tests\Functional\HrefLang;

use Clickstorm\CsSeo\Tests\Functional\AbstractFrontendTest;

class HrefLangCoreTest extends AbstractFrontendTest
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
     * @param string $url
     * @param array $expected
     *
     * @test
     * @dataProvider checkHrefLangOutputDataProvider
     */
    public function checkHrefLangOutput($url, $expectedTags, $notExpectedTags): void
    {
        /** @var \Nimut\TestingFramework\Http\Response $response */
        $response = $this->getFrontendResponseFromUrl(
            $url,
            $this->failOnFailure
        );

        $content = (string)$response->getContent();

        foreach ($expectedTags as $expectedTag) {
            self::assertStringContainsString($expectedTag, $content);
        }

        foreach ($notExpectedTags as $notExpectedTag) {
            self::assertStringNotContainsString($notExpectedTag, $content);
        }
    }

    /**
     * @return array
     */
    public function checkHrefLangOutputDataProvider(): array
    {
        return [
            'No translation available, so only hreflang tags expected for default language and fallback languages' => [
                'http://localhost/',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/"/>',
                ],
                [
                    '<link rel="alternate" hreflang="de-DE"'
                ]
            ],
            'English page, with German translation' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello"/>',
                ],
                []
            ],
            'German page, with English translation and English default' => [
                'http://localhost/de/willkommen',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello"/>',
                ],
                []
            ],
            'English page, with German and Dutch translation, without Dutch hreflang config' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello"/>',
                ],
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom"/>',
                ]
            ],
            'Dutch page, with German and English translation, without Dutch hreflang config' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/hello"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/willkommen"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/hello"/>',
                ],
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/nl/welkom"/>',
                    '<link rel="alternate" hreflang="" href="http://localhost/nl/welkom"/>',
                ]
            ],
            'English page with canonical' => [
                'http://localhost/contact',
                [
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/kontakt"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/kontakt"/>',
                ],
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/contact"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/contact"/>',
                ]
            ],
            'Swiss german page with canonical' => [
                'http://localhost/de-ch/uber',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/about"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/about"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/uber"/>',
                ],
                [
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/uber"/>',
                ]
            ],
            'Swiss german page with fallback to German, without content' => [
                'http://localhost/de-ch/produkte',
                [
                    '<link rel="alternate" hreflang="en-US" href="http://localhost/products"/>',
                    '<link rel="alternate" hreflang="x-default" href="http://localhost/products"/>',
                    '<link rel="alternate" hreflang="de-DE" href="http://localhost/de/produkte"/>',
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/produkte"/>',
                ],
                []
            ],
            'Languages with fallback should have hreflang even when page record is not translated, strict languages without translations shouldnt' => [
                'http://localhost/hello',
                [
                    '<link rel="alternate" hreflang="de-CH" href="http://localhost/de-ch/willkommen"/>',
                ],
                [
                    '<link rel="alternate" hreflang="fr-FR"',
                ]
            ],
            'Pages with disabled hreflang generation should not render any hreflang tag' => [
                'http://localhost/no-hreflang',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
            'Translated pages with disabled hreflang generation in original language should not render any hreflang tag' => [
                'http://localhost/de/kein-hreflang',
                [],
                [
                    '<link rel="alternate" hreflang="',
                ]
            ],
        ];
    }
}