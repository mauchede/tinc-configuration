<?php

namespace Mauchede\TincConfiguration\Tests;

use IPTools\IP;
use Mauchede\TincConfiguration\Model\IpVersion;

/**
 * IpVersionTest contains all unit tests of IpVersion.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class IpVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests ANY checking.
     */
    public function testIpV4AndIpV6Checking()
    {
        $version = IpVersion::ANY();

        $this->assertTrue($version->isIpAllowed(new IP('127.0.0.1')));
        $this->assertTrue($version->isIpAllowed(new IP('::1')));
    }

    /**
     * Tests IPV4 checking.
     */
    public function testIpV4Checking()
    {
        $version = IpVersion::IPV4();

        $this->assertTrue($version->isIpAllowed(new IP('127.0.0.1')));
        $this->assertFalse($version->isIpAllowed(new IP('::1')));
    }

    /**
     * Tests IPV6 checking.
     */
    public function testIpV6Checking()
    {
        $version = IpVersion::IPV6();

        $this->assertFalse($version->isIpAllowed(new IP('127.0.0.1')));
        $this->assertTrue($version->isIpAllowed(new IP('::1')));
    }
}
