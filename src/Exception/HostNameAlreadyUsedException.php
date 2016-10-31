<?php

namespace Mauchede\TincConfiguration\Exception;

use Mauchede\TincConfiguration\Model\Host;

/**
 * HostNameAlreadyUsedException is thrown when the name of an added host is already used.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
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
