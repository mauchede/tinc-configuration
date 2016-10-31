<?php

namespace Mauchede\TincConfiguration\Exception;

use IPTools\IP;
use Mauchede\TincConfiguration\Model\IpVersion;

/**
 * InvalidIpVersionException is thrown when an IP does not match with an IP version.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class InvalidIpVersionException extends \InvalidArgumentException
{
    /**
     * @param IpVersion $version
     * @param IP        $ip
     */
    public function __construct(IpVersion $version, IP $ip)
    {
        parent::__construct(sprintf('IP "%s" does not match with the version "%s".', $ip, $version->getValue()));
    }
}
