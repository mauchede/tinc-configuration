<?php

namespace Mauchede\TincConfiguration\Exception;

/**
 * CurrentHostCanNotBeConnectedException is thrown when the current host will be connected to itself.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class CurrentHostCanNotBeConnectedException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Current host can not be connected to itself.');
    }
}
