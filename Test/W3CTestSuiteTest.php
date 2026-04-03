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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;

/**
 * The official W3C JSON-LD test suite.
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class W3CTestSuiteTest extends JsonTestCase
{
    /**
     * The base directory from which the test manifests, input, and output
     * files should be read.
     */
    private static $basedir = __DIR__ . '/json-ld-test-suite/';

    /**
     * The URL corresponding to the base directory
     */
    private static $baseurl = 'http://localhost:8080/Test/json-ld-test-suite/';

    /**
     * @var string The test's ID.
     */
    private $id;

    /**
     * Holds the Symfony Process which represents a basic PHP webserver call.
     */
    public static Process $process;

    public static function setUpBeforeClass(): void
    {
        self::$process = new Process(['php', '-S', 'localhost:8080', 'Test/router.php']);
        self::$process->start();

        // do not stop server automatically after a certain amount of time
        self::$process->setTimeout(null);

        // Wait until server responds
        $start = time();
        $connected = false;

        while (time() - $start < 5) {
            $fp = @fsockopen('localhost', 8080);
            if ($fp) {
                fclose($fp);
                $connected = true;
                break;
            }
            usleep(50000);
        }

        if (false === $connected) {
            $msg = 'Could not start PHP webserver in time. Error output: ';
            $msg .= self::$process->getErrorOutput();
            throw new \RuntimeException($msg);
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$process->stop();
    }

    /**
     * Returns the test identifier.
     *
     * @return string The test identifier
     */
    public function getTestId()
    {
        return $this->id;
    }

    /**
     * Tests expansion.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('expansion')]
    #[DataProvider('expansionProvider')]
    public function testExpansion($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(self::$basedir . $test->{'expect'}));
        $result = JsonLD::expand(self::$basedir . $test->{'input'}, $options);

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides expansion test cases.
     */
    public static function expansionProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'expand-manifest.jsonld',
            self::$baseurl . 'expand-manifest.jsonld'
        );
    }

    /**
     * Tests compaction.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('compaction')]
    #[DataProvider('compactionProvider')]
    public function testCompaction($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(self::$basedir . $test->{'expect'}));
        $result = JsonLD::compact(
            self::$basedir . $test->{'input'},
            self::$basedir . $test->{'context'},
            $options
        );

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides compaction test cases.
     */
    public static function compactionProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'compact-manifest.jsonld',
            self::$baseurl . 'compact-manifest.jsonld'
        );
    }

    /**
     * Tests flattening.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('flattening')]
    #[DataProvider('flattenProvider')]
    public function testFlatten($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(self::$basedir . $test->{'expect'}));
        $context = (isset($test->{'context'}))
            ? self::$basedir . $test->{'context'}
            : null;

        $result = JsonLD::flatten(self::$basedir . $test->{'input'}, $context, $options);

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides flattening test cases.
     */
    public static function flattenProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'flatten-manifest.jsonld',
            self::$baseurl . 'flatten-manifest.jsonld'
        );
    }

    /**
     * Tests remote document loading.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('remote')]
    #[DataProvider('remoteDocumentLoadingProvider')]
    public function testRemoteDocumentLoading($name, $test, $options)
    {
        if (in_array('jld:NegativeEvaluationTest', $test->{'@type'})) {
            $expect = $test->{'expect'};
            $this->expectException(JsonLdException::class);
            $this->expectExceptionMessage($expect);
        } else {
            $expected = json_decode($this->replaceBaseUrl(file_get_contents(self::$basedir . $test->{'expect'})));
        }

        unset($options->base);

        $result = JsonLD::expand($this->replaceBaseUrl(self::$baseurl . $test->{'input'}), $options);

        if (isset($expected)) {
            $this->assertJsonEquals($expected, $result);
        }
    }

    /**
     * Provides remote document loading test cases.
     */
    public static function remoteDocumentLoadingProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'remote-doc-manifest.jsonld',
            self::$baseurl . 'remote-doc-manifest.jsonld'
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
     *
     * @deprecated TODO remove when introducing PHP8 support and releasing a new major version, because links are broken!
     */
    private function replaceBaseUrl($input) {
        return str_replace('http://json-ld.org/', 'http://localhost:8080/', $input);
    }

    /**
     * Tests errors (uses flattening).
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('errors')]
    #[DataProvider('errorProvider')]
    public function testError($name, $test, $options)
    {
        $this->expectException(JsonLdException::class);
        $this->expectExceptionMessage($test->{'expect'});

        JsonLD::flatten(
            self::$basedir . $test->{'input'},
            (isset($test->{'context'})) ? self::$basedir . $test->{'context'} : null,
            $options
        );
    }

    /**
     * Provides error test cases.
     */
    public static function errorProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'error-manifest.jsonld',
            self::$baseurl . 'error-manifest.jsonld'
        );
    }

    /**
     * Tests framing.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('framing')]
    #[DataProvider('framingProvider')]
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

        $expected = json_decode(file_get_contents(self::$basedir . $test->{'expect'}));
        $result = JsonLD::frame(
            self::$basedir . $test->{'input'},
            self::$basedir . $test->{'frame'},
            $options
        );

        $this->assertJsonEquals($expected, $result);
    }

    /**
     * Provides framing test cases.
     */
    public static function framingProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'frame-manifest.jsonld',
            self::$baseurl . 'frame-manifest.jsonld'
        );
    }

    /**
     * Tests conversion to RDF quads.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('toRdf')]
    #[DataProvider('toRdfProvider')]
    public function testToRdf($name, $test, $options)
    {
        $expected = trim(file_get_contents(self::$basedir . $test->{'expect'}));
        $quads = JsonLD::toRdf(self::$basedir . $test->{'input'}, $options);

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
    public static function toRdfProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'toRdf-manifest.jsonld',
            self::$baseurl . 'toRdf-manifest.jsonld'
        );
    }

    /**
     * Tests conversion from quads.
     *
     * @param string $name    The test name.
     * @param object $test    The test definition.
     * @param object $options The options to configure the algorithms.
     */
    #[Group('fromRdf')]
    #[DataProvider('fromRdfProvider')]
    public function testFromRdf($name, $test, $options)
    {
        $expected = json_decode(file_get_contents(self::$basedir . $test->{'expect'}));

        $parser = new NQuads();
        $quads = $parser->parse(file_get_contents(self::$basedir . $test->{'input'}));

        $result = JsonLD::fromRdf($quads, $options);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provides conversion to quads test cases.
     */
    public static function fromRdfProvider()
    {
        return new TestManifestIterator(
            self::$basedir . 'fromRdf-manifest.jsonld',
            self::$baseurl . 'fromRdf-manifest.jsonld'
        );
    }
}
