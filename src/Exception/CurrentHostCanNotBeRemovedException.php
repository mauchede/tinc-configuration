<?php

namespace Mauchede\TincConfiguration\Exception;

class CurrentHostCanNotBeRemovedException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Current host can not be removed.');
    }
}
