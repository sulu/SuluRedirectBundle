<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Command;

use Sulu\Bundle\RedirectBundle\Import\FileImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import an existing redirect file.
 */
class ImportCommand extends Command
{
    /** @var FileImport */
    private $import;

    /**
     * ImportCommand constructor.
     */
    public function __construct(FileImport $import)
    {
        parent::__construct('sulu:redirects:import');
        $this->import = $import;
    }

    public function configure(): void
    {
        $this->addArgument('fileName', InputArgument::REQUIRED)
            ->setDescription('Read a file and import content to redirect-system.')
            ->setHelp(
                <<<'EOT'
The <info>{$this->getName()}</info> command import a file to redirect system.
EOT
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat(' %current% [%bar%] %elapsed:6s% %memory:6s%');

        $output->writeln(sprintf('Import of file "%s" will be started:', basename($input->getArgument('fileName'))));

        $errors = [];
        foreach ($this->import->import($input->getArgument('fileName')) as $item) {
            $progressBar->advance();

            if ($item->getException()) {
                $errors[] = $item;
            }
        }

        $progressBar->finish();

        if (0 === count($errors)) {
            return 0;
        }

        $output->writeln('');
        $output->writeln('');
        $output->writeln('Following lines failed:');

        foreach ($errors as $error) {
            $exception = $error->getException();

            $output->writeln(
                sprintf(
                    ' * Line %s: "%s"',
                    $error->getLineNumber(),
                    $exception ? $exception->getMessage() : ''
                )
            );
        }

        return 1;
    }
}
