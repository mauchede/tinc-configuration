<?php

namespace Mauchede\TincConfiguration\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * AbstractCommand is an abstract implementation Command.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    const EXIT_FAILURE = 1;

    const EXIT_SUCCESS = 0;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    final protected function configure()
    {
        $this->addOption('configuration-folder', null, InputOption::VALUE_REQUIRED, 'Folder of the tinc configuration', '/etc/tinc');

        $this->doConfigure();
    }

    /**
     * Configures the current command.
     */
    abstract protected function doConfigure();

    /**
     * Executes the current command.
     *
     * @return int
     */
    abstract protected function doExecute();

    /**
     * {@inheritdoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        try {
            return $this->doExecute();
        } catch (\Exception $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return self::EXIT_FAILURE;
        }
    }

    /**
     * @return string
     */
    protected function getOptionConfigurationFolder()
    {
        $question = new Question('Folder of the tinc configuration');
        $question->setValidator(
            function ($value) {
                if (!is_dir($value)) {
                    throw new \InvalidArgumentException(sprintf('Folder "%s" does not exist.', $value));
                }

                if (!is_readable($value)) {
                    throw new \InvalidArgumentException(sprintf('Folder "%s" is not readable.', $value));
                }

                if (!is_writable($value)) {
                    throw new \InvalidArgumentException(sprintf('Folder "%s" is not writable.', $value));
                }

                return $value;
            }
        );

        return $this->getOrAskOption('configuration-folder', $question);
    }

    /**
     * Gets the value of an option. If the option is blank or the value is invalid, the question will be asked.
     *
     * @param string   $name
     * @param Question $question
     *
     * @return mixed
     *
     * @throws \Exception if value is incorrect and it is not possible to ask.
     */
    protected function getOrAskOption($name, Question $question)
    {
        $value = $this->input->getOption($name);

        $normalizer = $question->getNormalizer();
        if (null !== $normalizer) {
            $value = $normalizer($value);
        }

        try {
            if (empty($value)) {
                return $this->getHelper('question')->ask($this->input, $this->output, $question);
            }

            $validator = $question->getValidator();
            if (null !== $validator) {
                $value = $validator($value);
            }

            return $value;
        } catch (\Exception $exception) {
            if (!$this->input->isInteractive()) {
                throw $exception;
            }

            $this->output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return $this->getHelper('question')->ask($this->input, $this->output, $question);
        }
    }
}
