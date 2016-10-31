<?php

namespace Mauchede\TincConfiguration\Tests;

use IPTools\IP;
use IPTools\Network;
use Mauchede\TincConfiguration\Model\Host;
use Mauchede\TincConfiguration\Model\PublicKey;

/**
 * HostTest contains all unit tests of Host.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class HostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the instantiation of Host.
     */
    public function testHostInstantiation()
    {
        $name = 'host';
        $publicKey = new PublicKey(file_get_contents(__DIR__.'/../Fixtures/valid_key.pub'));
        $publicIp = new IP('1.1.1.1');
        $privateNetwork = Network::parse('10.0.0.1/32');

        $host = new Host($name, $publicKey, $publicIp, $privateNetwork);

        $this->assertEquals($name, $host->__toString());
        $this->assertEquals($name, $host->getName());
        $this->assertEquals($privateNetwork->getCIDR(), $host->getPrivateCIDR());
        $this->assertEquals($privateNetwork->getIP(), $host->getPrivateIp());
        $this->assertEquals($privateNetwork->getNetmask(), $host->getPrivateNetmask());
        $this->assertEquals($publicIp, $host->getPublicIp());
        $this->assertEquals($publicKey, $host->getPublicKey());
    }
}
