<?php

namespace Mauchede\TincConfiguration\Command;

use Mauchede\TincConfiguration\Manager\ConfigurationManager;
use Symfony\Component\Console\Helper\Table;

class InfoCommand extends AbstractCommand
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
        $this->setName('info');
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

        $currentHost = $configuration->getCurrentHost();

        $this->output->writeln(
            [
                'Host name:',
                sprintf('<info>%s</info>', $currentHost->getName()),
                '',
                'Public IP:',
                sprintf('<info>%s</info>', $currentHost->getPublicIp()),
                '',
                'Private CIDR:',
                sprintf('<info>%s</info>', $currentHost->getPrivateCIDR()),
                '',
                'Public key:',
                sprintf('<info>%s</info>', $currentHost->getPublicKey()),
            ]
        );

        $externalHosts = $configuration->getExternalHosts();
        if (0 !== count($externalHosts)) {
            $this->output->writeln(
                [
                    '',
                    'Host(s) on the network:',
                ]
            );

            $table = new Table($this->output);
            $table->setHeaders(
                [
                    'Name',
                    'Public IP',
                    'Private CIDR',
                    sprintf('Is %s connected to this host?', $currentHost->getName()),
                ]
            );
            foreach ($externalHosts as $externalHost) {
                $table->addRow(
                    [
                        $externalHost->getName(),
                        $externalHost->getPublicIp(),
                        $externalHost->getPrivateCIDR(),
                        $configuration->isConnectedHost($externalHost) ? 'Yes' : 'No',
                    ]
                );
            }
            $table->render();
        }

        return self::EXIT_SUCCESS;
    }
}
