<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import an existing redirect file.
 */
class ImportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sulu.redirects.import')
            ->addArgument('fileName', InputArgument::REQUIRED)
            ->setDescription('Read a file and import content to redirect-system.')
            ->setHelp(
                <<<'EOT'
The <info>{$this->getName()}</info> command import a file to redirect system.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat(' %current% [%bar%] %elapsed:6s% %memory:6s%');

        $output->writeln(sprintf('Import of file "%s" will be started:', basename($input->getArgument('fileName'))));

        $import = $this->getContainer()->get('sulu_redirect.import');

        $errors = [];
        foreach ($import->import($input->getArgument('fileName')) as $item) {
            $progressBar->advance();

            if ($item->getException()) {
                $errors[] = $item;
            }
        }

        $progressBar->finish();

        if (0 === count($errors)) {
            return;
        }

        $output->writeln('');
        $output->writeln('');
        $output->writeln('Following lines failed:');

        foreach ($errors as $error) {
            $output->writeln(
                sprintf(
                    ' * Line %s: "%s"',
                    $error->getLineNumber(),
                    $error->getException()->getMessage()
                )
            );
        }
    }
}
