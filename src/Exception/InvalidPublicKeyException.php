<?php

namespace Mauchede\TincConfiguration\Exception;

/**
 * InvalidPublicKeyException is thrown when the content of a public key is invalid.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
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
