<?php

namespace Mauchede\TincConfiguration\Model;

use Mauchede\TincConfiguration\Exception\InvalidPublicKeyException;

/**
 * PublicKey is the representation of a tinc public key.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class PublicKey
{
    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content
     *
     * @throws InvalidPublicKeyException if the content is not a public key.
     */
    public function __construct($content)
    {
        if (0 === preg_match('`-----BEGIN RSA PUBLIC KEY-----.*?-----END RSA PUBLIC KEY-----`sm', $content)) {
            throw new InvalidPublicKeyException($content);
        }

        $this->content = $content;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
