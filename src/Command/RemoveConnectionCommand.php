<?php

namespace Mauchede\TincConfiguration\Command;

use Mauchede\TincConfiguration\Manager\ConfigurationManager;
use Mauchede\TincConfiguration\Model\Configuration;
use Mauchede\TincConfiguration\Model\Host;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

class RemoveConnectionCommand extends AbstractCommand
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
            ->setName('connection:remove')
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

        if (empty($configuration->getConnectedHosts())) {
            $this->output->writeln(sprintf('<comment>Host "%s" is not connected to any host.</comment>', $configuration->getCurrentHost()->getName()));

            return self::EXIT_SUCCESS;
        }

        $host = $this->getHost($configuration);
        $configuration->removeConnectedHost($host);

        $this->configurationManager->saveConfiguration($configurationFolder, $configuration);
        $this->output->writeln(sprintf('<info>Host "%s" is not longer connected to "%s".</info>', $configuration->getCurrentHost()->getName(), $host));

        return self::EXIT_SUCCESS;
    }

    /**
     * @param Configuration $configuration
     *
     * @return Host
     */
    private function getHost(Configuration $configuration)
    {
        $connectedHosts = $configuration->getConnectedHosts();
        $keys = array_keys($connectedHosts);

        $question = new ChoiceQuestion('Name of the host', $keys);
        $question->setNormalizer(
            function ($value) use ($connectedHosts, $keys) {
                if (is_numeric($value) && isset($keys[$value])) {
                    $value = $keys[$value];
                }

                return isset($connectedHosts[$value]) ? $connectedHosts[$value] : $value;
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
