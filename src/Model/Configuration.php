<?php

namespace Mauchede\TincConfiguration\Model;

use IPTools\IP;
use Mauchede\TincConfiguration\Exception\CurrentHostCanNotBeConnectedException;
use Mauchede\TincConfiguration\Exception\CurrentHostCanNotBeRemovedException;
use Mauchede\TincConfiguration\Exception\HostNameAlreadyUsedException;
use Mauchede\TincConfiguration\Exception\InvalidIpVersionException;
use Mauchede\TincConfiguration\Exception\IpAlreadyUsedException;
use Mauchede\TincConfiguration\Exception\UnknownHostException;

class Configuration
{
    /**
     * @var Host[]
     */
    private $connectedHosts = [];

    /**
     * @var Host
     */
    private $currentHost;

    /**
     * @var Host[]
     */
    private $hosts = [];

    /**
     * @var string
     */
    private $interface;

    /**
     * @var IpVersion
     */
    private $ipVersion;

    /**
     * @param Host           $currentHost
     * @param IpVersion|null $ipVersion
     * @param string         $interface
     */
    public function __construct(Host $currentHost, IpVersion $ipVersion, $interface)
    {
        $this->ipVersion = $ipVersion;
        $this->interface = $interface;

        $this->setCurrentHost($currentHost);
    }

    /**
     * @param Host $host
     *
     * @return self
     *
     * @throws CurrentHostCanNotBeConnectedException
     * @throws UnknownHostException
     */
    public function addConnectedHost(Host $host)
    {
        if (!isset($this->hosts[$host->getName()])) {
            throw new UnknownHostException($host);
        }

        if ($this->currentHost === $host) {
            throw new CurrentHostCanNotBeConnectedException();
        }

        $this->connectedHosts[$host->getName()] = $host;

        return $this;
    }

    /**
     * @param Host $host
     *
     * @return self
     *
     * @throws InvalidIpVersionException
     * @throws IpAlreadyUsedException
     * @throws HostNameAlreadyUsedException
     */
    public function addHost(Host $host)
    {
        $name = $host->getName();
        $privateIp = $host->getPrivateIp();
        $publicIp = $host->getPublicIp();

        if (!$this->ipVersion->isIpAllowed($privateIp)) {
            throw new InvalidIpVersionException($this->ipVersion, $privateIp);
        }
        if (!$this->ipVersion->isIpAllowed($publicIp)) {
            throw new InvalidIpVersionException($this->ipVersion, $publicIp);
        }

        $hostWithSameName = $this->findHostByName($name);
        if (null !== $hostWithSameName) {
            throw new HostNameAlreadyUsedException($hostWithSameName);
        }
        $hostWithSamePrivateIp = $this->findHostByPrivateIp($host->getPrivateIp());
        if (null !== $hostWithSamePrivateIp) {
            throw new IpAlreadyUsedException($privateIp, $hostWithSamePrivateIp);
        }
        $hostWithSamePublicIp = $this->findHostByPublicIp($host->getPublicIp());
        if (null !== $hostWithSamePublicIp) {
            throw new IpAlreadyUsedException($publicIp, $hostWithSamePublicIp);
        }

        $this->hosts[$host->getName()] = $host;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Host|null
     */
    public function findHostByName($name)
    {
        $hosts = $this->getHosts();

        return isset($hosts[$name]) ? $hosts[$name] : null;
    }

    /**
     * @param IP $ip
     *
     * @return Host|null
     */
    public function findHostByPrivateIp(IP $ip)
    {
        $ip = (string)$ip;

        foreach ($this->getHosts() as $host) {
            if ($ip === (string)$host->getPrivateIp()) {
                return $host;
            }
        }

        return null;
    }

    /**
     * @param IP $ip
     *
     * @return Host|null
     */
    public function findHostByPublicIp(IP $ip)
    {
        $ip = (string)$ip;

        foreach ($this->getHosts() as $host) {
            if ($ip === (string)$host->getPublicIp()) {
                return $host;
            }
        }

        return null;
    }

    /**
     * @return Host[]
     */
    public function getConnectedHosts()
    {
        return $this->connectedHosts;
    }

    /**
     * @return Host
     */
    public function getCurrentHost()
    {
        return $this->currentHost;
    }

    /**
     * @return Host[]
     */
    public function getExternalHosts()
    {
        $externalHosts = $this->hosts;
        unset($externalHosts[$this->currentHost->getName()]);

        return $externalHosts;
    }

    /**
     * @return Host[]
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * @return IpVersion
     */
    public function getIpVersion()
    {
        return $this->ipVersion;
    }

    /**
     * @param Host $host
     *
     * @return bool
     */
    public function isConnectedHost(Host $host)
    {
        return isset($this->connectedHosts[$host->getName()]);
    }

    /**
     * @param Host $host
     *
     * @return self
     */
    public function removeConnectedHost(Host $host)
    {
        unset($this->connectedHosts[$host->getName()]);

        return $this;
    }

    /**
     * @param Host $host
     *
     * @return self
     *
     * @throws CurrentHostCanNotBeRemovedException
     */
    public function removeHost(Host $host)
    {
        if ($this->currentHost === $host) {
            throw new CurrentHostCanNotBeRemovedException();
        }

        $this->removeConnectedHost($host);

        unset($this->hosts[$host->getName()]);

        return $this;
    }

    /**
     * @param Host $currentHost
     *
     * @return self
     *
     * @throws InvalidIpVersionException
     */
    private function setCurrentHost(Host $currentHost)
    {
        $privateIp = $currentHost->getPrivateIp();

        if (!$this->ipVersion->isIpAllowed($privateIp)) {
            throw new InvalidIpVersionException($this->ipVersion, $privateIp);
        }

        $this->currentHost = $currentHost;
        $this->addHost($currentHost);

        return $this;
    }
}
