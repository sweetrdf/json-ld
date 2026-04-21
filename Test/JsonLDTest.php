<?php

/*
 * (c) Konrad Abicht <hi@inspirito.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\JsonLD\Test;

use ML\JsonLD\JsonLD;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * This class contains additional tests regarding JsonLD-API.
 */
final class JsonLDTest extends TestCase
{
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

    /**
     * This function contains a reproducer for #7 as decribed here:
     * https://github.com/sweetrdf/json-ld/issues/5#issuecomment-4184490754
     *
     * Summary: Triggers faulty code which I introduced with https://github.com/lanthaler/JsonLD/pull/113.
     *
     * @see https://github.com/sweetrdf/json-ld/pull/7
     * @see https://github.com/sweetrdf/json-ld/issue/5
     */
    public function testRegressionTestPullRequest7(): void
    {
        $url = 'http://localhost:8080/Test/json-ld-test-suite/remote-doc-0011-in.jldt';

        $result1 = JsonLD::expand($url);
        $result2 = JsonLD::expand($url);

        $instance = [
            '@id' => 'http://localhost:8080/Test/json-ld-test-suite/remote-doc-0011-in.jldt',
            'http://example/vocab#term' => [
                (object) [
                    '@value' => 'value'
                ]
            ]
        ];
        $expectedResult = [(object)$instance];
        $this->assertEquals($expectedResult, $result1);
        $this->assertEquals($expectedResult, $result2);

        /*
         * When using the faulty code, $result1 would be an empty array:    array{}
         *
         * So the first assertion would fail.
         */
    }
}
