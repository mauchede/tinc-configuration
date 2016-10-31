<?php

namespace Mauchede\TincConfiguration\Exception;

use Mauchede\TincConfiguration\Model\Host;

/**
 * UnknownHostException is thrown when the used host does not exist.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
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
