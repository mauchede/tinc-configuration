<?php

namespace Mauchede\TincConfiguration\Exception;

class CurrentHostCanNotBeConnectedException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Current host can not be connected.');
    }
}
