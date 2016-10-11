<?php

namespace Mauchede\TincConfiguration\Exception;

use Mauchede\TincConfiguration\Model\Host;

class UnknownHostException extends \RuntimeException
{
    /**
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        parent::__construct(sprintf('Host "%s" is undefined.', $host->getName()));
    }
}
