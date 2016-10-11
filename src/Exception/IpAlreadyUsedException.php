<?php

namespace Mauchede\TincConfiguration\Exception;

use IPTools\IP;
use Mauchede\TincConfiguration\Model\Host;

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
