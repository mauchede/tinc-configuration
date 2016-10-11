<?php

namespace Mauchede\TincConfiguration\Model;

use IPTools\IP;
use MyCLabs\Enum\Enum;

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
