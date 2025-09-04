<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\JsonLD\Test;

use ML\JsonLD\Exception\JsonLdException;
use ML\JsonLD\JsonLD;
use ML\JsonLD\NQuads;
use ML\JsonLD\Test\TestManifestIterator;

/**
 * The official W3C JSON-LD test suite.
 *
 * @link http://www.w3.org/2013/json-ld-tests/ Official W3C JSON-LD test suite
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class W3CTestSuiteTest extends JsonTestCase
{
    /**
     * The URL corresponding to the base directory
     */
    public static string $baseurl = 'https://w3c.github.io/json-ld-api/tests/';

    /**
     * @var string The test's ID.
     */
    private $id;

    /**
     * Returns the test identifier.
     *
     * @return string The test identifier
     */
    public function getTestId()
    {
        return '';  // TODO: Fix test ID functionality if needed
    }

    /**
     * Tests expansion.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group expansion
     * @dataProvider expansionProvider
     */
    public function testExpansion($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'}));
        $result = JsonLD::expand(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'}, $options);

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides expansion test cases.
     */
    public static function expansionProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/expand-manifest.jsonld',
            static::$baseurl . 'expand-manifest.jsonld'
        );
    }

    /**
     * Tests compaction.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group compaction
     * @dataProvider compactionProvider
     */
    public function testCompaction($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'}));
        $result = JsonLD::compact(
            __DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'},
            __DIR__ . '/../vendor/json-ld/tests/' . $test->{'context'},
            $options
        );

        $this->assertJsonEquals($expected, $result);
    }


    /**
     * Provides compaction test cases.
     */
    public static function compactionProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/compact-manifest.jsonld',
            static::$baseurl . 'compact-manifest.jsonld'
        );
    }

    /**
     * Tests flattening.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group flattening
     * @dataProvider flattenProvider
     */
    public function testFlatten($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'}));
        $context = (isset($test->{'context'}))
            ? __DIR__ . '/../vendor/json-ld/tests/' . $test->{'context'}
            : null;

        $result = JsonLD::flatten(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'}, $context, $options);

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides flattening test cases.
     */
    public static function flattenProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/flatten-manifest.jsonld',
            static::$baseurl . 'flatten-manifest.jsonld'
        );
    }

    /**
     * Tests remote document loading.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group remote
     * @dataProvider remoteDocumentLoadingProvider
     */
    public function testRemoteDocumentLoading($name, $test, $options)
    {
        if (in_array('jld:NegativeEvaluationTest', $test->{'@type'})) {
            $this->expectException(JsonLdException::class);
            $this->expectExceptionMessage($test->{'expect'});
        } else {
            $expected = json_decode($this->replaceBaseUrl(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'})));
        }

        unset($options->base);

        $result = JsonLD::expand($this->replaceBaseUrl(static::$baseurl . $test->{'input'}), $options);

        if (isset($expected)) {
            $this->assertJsonEquals($expected, $result);
        }
    }

    /**
     * Provides remote document loading test cases.
     */
    public static function remoteDocumentLoadingProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/remote-doc-manifest.jsonld',
            static::$baseurl . 'remote-doc-manifest.jsonld'
        );
    }

    /**
     * Replaces the base URL 'http://json-ld.org/' with 'https://json-ld.org:443/'.
     *
     * The test location of the test suite has been changed as the site has been
     * updated to use HTTPS everywhere.
     *
     * @param string $input The input string.
     *
     * @return string The input string with all occurrences of the old base URL replaced with the new HTTPS-based one.
     */
    private function replaceBaseUrl(string $input): string 
    {
        return str_replace('http://json-ld.org/', 'https://json-ld.org:443/', $input);
    }

    /**
     * Tests errors (uses flattening).
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group errors
     * @dataProvider errorProvider
     */
    public function testError($name, $test, $options): void
    {
        $this->expectException(JsonLdException::class);
        $this->expectExceptionMessage($test->{'expect'});

        JsonLD::flatten(
            __DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'},
            (isset($test->{'context'})) ? __DIR__ . '/../vendor/json-ld/tests/' . $test->{'context'} : null,
            $options
        );
    }

    /**
     * Provides error test cases.
     */
    public static function errorProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/error-manifest.jsonld',
            static::$baseurl . 'error-manifest.jsonld'
        );
    }

    /**
     * Tests framing.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group framing
     * @dataProvider framingProvider
     */
    public function testFraming($name, $test, $options)
    {
        $ignoredTests = array(
            'frame-0005-in.jsonld',
            'frame-0009-in.jsonld',
            'frame-0010-in.jsonld',
            'frame-0012-in.jsonld',
            'frame-0013-in.jsonld',
            'frame-0023-in.jsonld',
            'frame-0024-in.jsonld',
            'frame-0027-in.jsonld',
            'frame-0028-in.jsonld',
            'frame-0029-in.jsonld',
            'frame-0030-in.jsonld'
        );

        if (in_array($test->{'input'}, $ignoredTests)) {
            $this->markTestSkipped(
                'This implementation uses deep value matching and aggressive re-embedding. See ISSUE-110 and ISSUE-119.'
            );
        }

        $expected = json_decode(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'}));
        $result = JsonLD::frame(
            __DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'},
            __DIR__ . '/../vendor/json-ld/tests/' . $test->{'frame'},
            $options
        );

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides framing test cases.
     */
    public static function framingProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/frame-manifest.jsonld',
            static::$baseurl . 'frame-manifest.jsonld'
        );
    }

    /**
     * Tests conversion to RDF quads.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group toRdf
     * @dataProvider toRdfProvider
     */
    public function testToRdf($name, $test, $options)
    {
        $expected = trim(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'}));
        $quads = JsonLD::toRdf(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'}, $options);

        $serializer = new NQuads();
        $result = $serializer->serialize($quads);

        // Sort quads (the expected quads are already sorted)
        $result = explode("\n", trim($result));
        sort($result);
        $result = implode("\n", $result);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provides conversion to RDF quads test cases.
     */
    public static function toRdfProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/toRdf-manifest.jsonld',
            static::$baseurl . 'toRdf-manifest.jsonld'
        );
    }

    /**
     * Tests conversion from quads.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     *
     * @group fromRdf
     * @dataProvider fromRdfProvider
     */
    public function testFromRdf($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'expect'}));

        $parser = new NQuads();
        $quads = $parser->parse(file_get_contents(__DIR__ . '/../vendor/json-ld/tests/' . $test->{'input'}));

        $result = JsonLD::fromRdf($quads, $options);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provides conversion to quads test cases.
     */
    public static function fromRdfProvider(): TestManifestIterator
    {
        return new TestManifestIterator(
            __DIR__ . '/../vendor/json-ld/tests/fromRdf-manifest.jsonld',
            static::$baseurl . 'fromRdf-manifest.jsonld'
        );
    }
}