<?php

namespace Mauchede\TincConfiguration\Exception;

/**
 * CurrentHostCanNotBeRemovedException is thrown when the current host will be removed.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class CurrentHostCanNotBeRemovedException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Current host can not be removed.');
    }
}
