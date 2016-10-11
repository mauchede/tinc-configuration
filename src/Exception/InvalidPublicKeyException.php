<?php

namespace Mauchede\TincConfiguration\Exception;

class InvalidPublicKeyException extends \RuntimeException
{
    /**
     * @param string $content
     */
    public function __construct($content)
    {
        parent::__construct(sprintf("Public key is invalid:\n%s", $content));
    }
}
