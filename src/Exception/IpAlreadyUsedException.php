<?php

namespace Mauchede\TincConfiguration\Exception;

use IPTools\IP;
use Mauchede\TincConfiguration\Model\Host;

/**
 * IpAlreadyUsedException is thrown when the public IP or the private IP of a host is already used.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class IpAlreadyUsedException extends \RuntimeException
{
    /**
     * @param IP   $ip
     * @param Host $host
     */
    public function __construct(IP $ip, Host $host)
    {
        parent::__construct(sprintf('IP "%s" is already used by host "%s".', $ip, $host->getName()));
    }
}
