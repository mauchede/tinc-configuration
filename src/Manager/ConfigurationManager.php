<?php

namespace Mauchede\TincConfiguration\Manager;

use IPTools\IP;
use IPTools\Network;
use Mauchede\TincConfiguration\Model\Configuration;
use Mauchede\TincConfiguration\Model\Host;
use Mauchede\TincConfiguration\Model\IpVersion;
use Mauchede\TincConfiguration\Model\PublicKey;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ConfigurationManager
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param Filesystem        $filesystem
     * @param Finder            $finder
     * @param \Twig_Environment $twig
     */
    public function __construct(Filesystem $filesystem, Finder $finder, \Twig_Environment $twig)
    {
        $this->filesystem = $filesystem;
        $this->finder = $finder;
        $this->twig = $twig;
    }

    /**
     * @param string $configurationFolder
     *
     * @return Configuration|null
     */
    public function loadConfiguration($configurationFolder)
    {
        $tincConfigurationFilename = sprintf('%s/tinc.conf', $configurationFolder);
        if (!$this->filesystem->exists($tincConfigurationFilename)) {
            return null;
        }

        $hosts = [];
        foreach ($this->finder->files()->in(sprintf('%s/hosts', $configurationFolder)) as $hostFile) {
            $hostData = file_get_contents($hostFile);

            preg_match('`Address = (.*)`', $hostData, $match);
            $publicIp = new IP($match[1]);

            preg_match('`Subnet = (.*)`', $hostData, $match);
            $privateNetwork = Network::parse($match[1]);

            preg_match('`-----BEGIN RSA PUBLIC KEY-----.*?-----END RSA PUBLIC KEY-----`sim', $hostData, $match);
            $publicKey = new PublicKey($match[0]);

            $hosts[$hostFile->getFilename()] = new Host(
                $hostFile->getFilename(),
                $publicKey,
                $publicIp,
                $privateNetwork
            );
        }

        $configurationData = file_get_contents($tincConfigurationFilename);

        preg_match('`AddressFamily = (.*)`', $configurationData, $match);
        $ipVersion = new IpVersion($match[1]);

        preg_match('`Interface = (.*)`', $configurationData, $match);
        $interface = $match[1];

        preg_match('`Name = (.*)`', $configurationData, $match);
        $currentHost = $hosts[$match[1]];

        $configuration = new Configuration($currentHost, $ipVersion, $interface);
        foreach ($hosts as $host) {
            if ($currentHost === $host) {
                continue;
            }

            $configuration->addHost($host);
        }

        preg_match_all('`ConnectTo = (.*)`', $configurationData, $matches);
        foreach ($matches[1] as $connectedHostName) {
            $configuration->addConnectedHost($hosts[$connectedHostName]);
        }

        return $configuration;
    }

    /**
     * @param string        $configurationFolder
     * @param Configuration $configuration
     */
    public function saveConfiguration($configurationFolder, Configuration $configuration)
    {
        $tincDownFilename = sprintf('%s/tinc-down', $configurationFolder);
        $this->filesystem->dumpFile(
            $tincDownFilename,
            $this->twig->render('tinc-down.twig', ['configuration' => $configuration])
        );
        $this->filesystem->chmod($tincDownFilename, 0755);

        $tincUpFilename = sprintf('%s/tinc-up', $configurationFolder);
        $this->filesystem->dumpFile(
            $tincUpFilename,
            $this->twig->render('tinc-up.twig', ['configuration' => $configuration])
        );
        $this->filesystem->chmod($tincUpFilename, 0755);

        $tincConfigurationFilename = sprintf('%s/tinc.conf', $configurationFolder);
        $this->filesystem->dumpFile(
            $tincConfigurationFilename,
            $this->twig->render('tinc.conf.twig', ['configuration' => $configuration])
        );

        $this->filesystem->remove(sprintf('%s/hosts', $configurationFolder));
        $this->filesystem->mkdir(sprintf('%s/hosts', $configurationFolder));
        foreach ($configuration->getHosts() as $host) {
            $this->filesystem->dumpFile(
                sprintf('%s/hosts/%s', $configurationFolder, $host->getName()),
                $this->twig->render('host.twig', ['host' => $host])
            );
        }
    }
}
