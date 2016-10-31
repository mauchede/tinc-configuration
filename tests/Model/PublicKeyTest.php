<?php

namespace Mauchede\TincConfiguration\Tests;

use Mauchede\TincConfiguration\Exception\InvalidPublicKeyException;
use Mauchede\TincConfiguration\Model\PublicKey;

/**
 * PublicKeyTest contains all unit tests of PublicKey.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class PublicKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the instantiation of PublicKey with an invalid key.
     */
    public function testPublicKeyInstantiationWithInvalidKey()
    {
        $content = file_get_contents(__DIR__.'/../Fixtures/invalid_key.pub');

        $this->expectException(InvalidPublicKeyException::class);
        $this->expectExceptionMessage(sprintf("Public key is invalid:\n%s", $content));

        new PublicKey($content);
    }

    /**
     * Tests the instantiation of PublicKey with a valid key.
     */
    public function testPublicKeyInstantiationWithValidKey()
    {
        $content = file_get_contents(__DIR__.'/../Fixtures/valid_key.pub');

        $publicKey = new PublicKey($content);

        $this->assertEquals($content, $publicKey->getContent());
    }
}
