<?php

namespace Mauchede\TincConfiguration\Command;

use IPTools\IP;
use IPTools\Network;
use Mauchede\TincConfiguration\Exception\InvalidIpVersionException;
use Mauchede\TincConfiguration\Manager\ConfigurationManager;
use Mauchede\TincConfiguration\Manager\KeyManager;
use Mauchede\TincConfiguration\Model\Host;
use Mauchede\TincConfiguration\Model\IpVersion;
use Mauchede\TincConfiguration\Model\PublicKey;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

/**
 * AddHostCommand adds a host to the tinc configuration.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class AddHostCommand extends AbstractCommand
{
    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var KeyManager
     */
    private $keyManager;

    /**
     * @param ConfigurationManager $configurationManager
     * @param KeyManager           $keyManager
     */
    public function __construct(ConfigurationManager $configurationManager, KeyManager $keyManager)
    {
        parent::__construct();

        $this->configurationManager = $configurationManager;
        $this->keyManager = $keyManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function doConfigure()
    {
        $this
            ->setName('host:add')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the host')
            ->addOption('private-cidr', null, InputOption::VALUE_REQUIRED, 'Private CIDR to use')
            ->addOption('public-ip', null, InputOption::VALUE_REQUIRED, 'Public IP of the host')
            ->addOption('public-key', null, InputOption::VALUE_REQUIRED, 'Public key of the host')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute()
    {
        $configurationFolder = $this->getOptionConfigurationFolder();
        $configuration = $this->configurationManager->loadConfiguration($configurationFolder);
        if (null === $configuration) {
            $this->output->writeln(sprintf('<error>No configuration has been found in "%s".</error>', $configurationFolder));

            return self::EXIT_FAILURE;
        }

        $name = $this->getOptionName();
        $ipVersion = $configuration->getIpVersion();
        $publicIp = $this->getOptionPublicIp($ipVersion);
        $privateNetwork = $this->getOptionPrivateCidr($ipVersion);
        $publicKey = $this->getOptionPublicKey();

        $configuration->addHost(new Host($name, $publicKey, $publicIp, $privateNetwork));

        $this->configurationManager->saveConfiguration($configurationFolder, $configuration);
        $this->output->writeln(sprintf('<info>Host "%s" has been added.</info>', $name));

        return self::EXIT_SUCCESS;
    }

    /**
     * @param IpVersion $ipVersion
     *
     * @return Network
     */
    protected function getOptionPrivateCidr(IpVersion $ipVersion)
    {
        $question = new Question('Private CIDR to use');
        $question->setNormalizer(
            function ($value) {
                try {
                    return Network::parse($value);
                } catch (\Exception $exception) {
                    return $value;
                }
            }
        );
        $question->setValidator(
            function ($value) use ($ipVersion) {
                if (!$value instanceof Network) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not an CIDR.', $value));
                }

                if (!$ipVersion->isIpAllowed($value->getIP())) {
                    throw new InvalidIpVersionException($ipVersion, $value->getIP());
                }

                return $value;
            }
        );

        return $this->getOrAskOption('private-cidr', $question);
    }

    /**
     * @return string
     */
    private function getOptionName()
    {
        return $this->getOrAskOption('name', new Question('Name of the host'));
    }

    /**
     * @param IpVersion $ipVersion
     *
     * @return IP
     */
    private function getOptionPublicIp(IpVersion $ipVersion)
    {
        $question = new Question('Public IP of the host');
        $question->setNormalizer(
            function ($value) {
                try {
                    return new IP($value);
                } catch (\Exception $exception) {
                    return $value;
                }
            }
        );
        $question->setValidator(
            function ($value) use ($ipVersion) {
                if (!$value instanceof IP) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not an IP.', $value));
                }

                if (!$ipVersion->isIpAllowed($value)) {
                    throw new InvalidIpVersionException($ipVersion, $value);
                }

                return $value;
            }
        );

        return $this->getOrAskOption('public-ip', $question);
    }

    /**
     * @return PublicKey
     */
    private function getOptionPublicKey()
    {
        $question = new Question('Public key of the host');
        $question->setNormalizer(
            function ($value) {
                try {
                    return $this->keyManager->getPublicKeyFromFile($value);
                } catch (\Exception $exception) {
                    return $value;
                }
            }
        );
        $question->setValidator(
            function ($value) {
                if (!$value instanceof PublicKey) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not an public key.', $value));
                }

                return $value;
            }
        );

        return $this->getOrAskOption('public-key', $question);
    }
}
