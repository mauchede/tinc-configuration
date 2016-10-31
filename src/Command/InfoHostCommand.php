<?php

namespace Mauchede\TincConfiguration\Command;

use Mauchede\TincConfiguration\Manager\ConfigurationManager;
use Mauchede\TincConfiguration\Model\Configuration;
use Mauchede\TincConfiguration\Model\Host;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * InfoHostCommand gives information about a specific host of the tinc configuration.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class InfoHostCommand extends AbstractCommand
{
    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        parent::__construct();

        $this->configurationManager = $configurationManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function doConfigure()
    {
        $this
            ->setName('host:info')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the host')
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

        if (empty($configuration->getExternalHosts())) {
            $this->output->writeln('<comment>There are no host to analyze.</comment>');

            return self::EXIT_SUCCESS;
        }

        $externalHost = $this->getHost($configuration);
        $this->output->writeln(
            [
                'Host name:',
                sprintf('<info>%s</info>', $externalHost->getName()),
                '',
                'Public IP:',
                sprintf('<info>%s</info>', $externalHost->getPublicIp()),
                '',
                'Private CIDR:',
                sprintf('<info>%s</info>', $externalHost->getPrivateCIDR()),
                '',
                'Public key:',
                sprintf('<info>%s</info>', $externalHost->getPublicKey()),
                '',
                sprintf('Is %s connected to this host?', $configuration->getCurrentHost()->getName()),
                $configuration->isConnectedHost($externalHost) ? '<info>Yes</info>' : '<info>No</info>',
            ]
        );

        return self::EXIT_SUCCESS;
    }

    /**
     * @param Configuration $configuration
     *
     * @return Host
     */
    private function getHost(Configuration $configuration)
    {
        $externalHosts = $configuration->getExternalHosts();
        $keys = array_keys($externalHosts);

        $question = new ChoiceQuestion('Name of the host', $keys);
        $question->setNormalizer(
            function ($value) use ($externalHosts, $keys) {
                if (is_numeric($value) && isset($keys[$value])) {
                    $value = $keys[$value];
                }

                return isset($externalHosts[$value]) ? $externalHosts[$value] : $value;
            }
        );
        $question->setValidator(
            function ($value) {
                if (!$value instanceof Host) {
                    throw new \InvalidArgumentException(sprintf('Host "%s" does not exist.', $value));
                }

                return $value;
            }
        );

        return $this->getOrAskOption('name', $question);
    }
}
