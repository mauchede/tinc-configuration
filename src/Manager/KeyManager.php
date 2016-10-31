<?php

namespace Mauchede\TincConfiguration\Manager;

use Mauchede\TincConfiguration\Model\PublicKey;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * KeyManager gives the possibility to manage tinc keys.
 *
 * @author Morgan Auchede <morgan.auchede@gmail.com>
 */
class KeyManager
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $configurationFolder
     *
     * @return PublicKey
     *
     * @throws ProcessFailedException if key generation has failed.
     */
    public function generateKeys($configurationFolder)
    {
        $this->filesystem->remove(
            [
                sprintf('%s/rsa_key.priv', $configurationFolder),
                sprintf('%s/rsa_key.pub', $configurationFolder),
            ]
        );

        $process = new Process(sprintf('echo -ne "\n" | tincd -c %s -K 4096', $configurationFolder));
        $process->mustRun();

        return $this->getPublicKeyFromFile(sprintf('%s/rsa_key.pub', $configurationFolder));
    }

    /**
     * @param string $file
     *
     * @return PublicKey|null
     */
    public function getPublicKeyFromFile($file)
    {
        if (!is_file($file)) {
            return null;
        }

        return new PublicKey(file_get_contents($file));
    }
}
