<?php

namespace Mauchede\TincConfiguration\Command;

use IPTools\IP;
use IPTools\Network;
use Mauchede\TincConfiguration\Exception\InvalidIpVersionException;
use Mauchede\TincConfiguration\Manager\ConfigurationManager;
use Mauchede\TincConfiguration\Manager\KeyManager;
use Mauchede\TincConfiguration\Model\Configuration;
use Mauchede\TincConfiguration\Model\Host;
use Mauchede\TincConfiguration\Model\IpVersion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class InitCommand extends AbstractCommand
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
            ->setName('init')
            ->addOption('ip-version', null, InputOption::VALUE_REQUIRED, 'IP version to use', IpVersion::IPV4)
            ->addOption('interface', null, InputOption::VALUE_REQUIRED, 'Network interface to use', 'tun0')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the host')
            ->addOption('private-cidr', null, InputOption::VALUE_REQUIRED, 'Private CIDR to use')
            ->addOption('public-ip', null, InputOption::VALUE_REQUIRED, 'Public IP of the host')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute()
    {
        $configurationFolder = $this->getOptionConfigurationFolder();
        if (null !== $this->configurationManager->loadConfiguration($configurationFolder)) {
            $this->output->writeln(sprintf('<error>A configuration has been found in "%s".</error>', $configurationFolder));

            return self::EXIT_FAILURE;
        }

        $publicKey = $this->keyManager->generateKeys($configurationFolder);

        $name = $this->getOptionName();
        $interface = $this->getOptionInterface();
        $ipVersion = $this->getOptionIpVersion();
        $publicIp = $this->getOptionPublicIp($ipVersion);
        $privateNetwork = $this->getOptionPrivateCidr($ipVersion);

        $this->configurationManager->saveConfiguration(
            $configurationFolder,
            new Configuration(
                new Host($name, $publicKey, $publicIp, $privateNetwork),
                $ipVersion,
                $interface
            )
        );
        $this->output->writeln(sprintf('<info>Configuration of host "%s" has been created.</info>', $name));

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
    private function getOptionInterface()
    {
        return $this->getOrAskOption('interface', new Question('Network interface to use'));
    }

    /**
     * @return IpVersion
     */
    private function getOptionIpVersion()
    {
        $question = new ChoiceQuestion('IP version to use', IpVersion::keys());
        $question->setNormalizer(
            function ($value) {
                $keys = IpVersion::keys();
                if (is_numeric($value) && isset($keys[$value])) {
                    $value = $keys[$value];
                }

                $value = strtoupper($value);

                return IpVersion::isValidKey($value) ? IpVersion::values()[$value] : $value;
            }
        );
        $question->setValidator(
            function ($value) {
                if (!$value instanceof IpVersion) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not an IP version. Possible values are: %s', $value, implode(IpVersion::keys(), ' or ')));
                }

                return $value;
            }
        );

        return $this->getOrAskOption('ip-version', $question);
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
}
