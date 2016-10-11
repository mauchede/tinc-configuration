<?php

namespace Mauchede\TincConfiguration\Exception;

use Mauchede\TincConfiguration\Model\Host;

class HostNameAlreadyUsedException extends \RuntimeException
{
    /**
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        parent::__construct(sprintf('Host name "%s" is already used.', $host->getName()));
    }
}
