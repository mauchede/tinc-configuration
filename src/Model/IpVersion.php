<?php

namespace Mauchede\TincConfiguration\Model;

use IPTools\IP;
use MyCLabs\Enum\Enum;

/**
 * IpVersion is the representation of an IP version.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class IpVersion extends Enum
{
    const ANY = 'any';

    const IPV4 = 'ipv4';

    const IPV6 = 'ipv6';

    /**
     * @param IP $ip
     *
     * @return bool
     */
    public function isIpAllowed(IP $ip)
    {
        return self::ANY === $this->value || $this->value === strtolower($ip->getVersion());
    }
}
