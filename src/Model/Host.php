<?php

namespace Mauchede\TincConfiguration\Model;

use IPTools\IP;
use IPTools\Network;

/**
 * Host is a representation of a tinc host.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class Host
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Network
     */
    private $privateNetwork;

    /**
     * @var IP
     */
    private $publicIp;

    /**
     * @var PublicKey
     */
    private $publicKey;

    /**
     * @param string    $name
     * @param PublicKey $publicKey
     * @param IP        $publicIp
     * @param Network   $privateNetwork
     */
    public function __construct($name, PublicKey $publicKey, IP $publicIp, Network $privateNetwork)
    {
        $this->name = $name;
        $this->publicKey = $publicKey;
        $this->privateNetwork = $privateNetwork;
        $this->publicIp = $publicIp;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPrivateCIDR()
    {
        return $this->privateNetwork->getCIDR();
    }

    /**
     * @return IP
     */
    public function getPrivateIp()
    {
        return $this->privateNetwork->getIP();
    }

    /**
     * @return IP
     */
    public function getPrivateNetmask()
    {
        return $this->privateNetwork->getNetmask();
    }

    /**
     * @return IP
     */
    public function getPublicIp()
    {
        return $this->publicIp;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
}
