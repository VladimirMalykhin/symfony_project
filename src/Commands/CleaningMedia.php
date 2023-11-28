<?php
namespace App\Commands;

use App\Service\Tmp\TmpCleaning;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleaningMedia extends Command
{

    protected static $defaultName = 'app:clean-folders';

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Cleanes folder with images and videos.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to clean temp files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cleaning_object = new TmpCleaning('constructor_back_dev', 'img');
        $cleaning_object->clean();
        $cleaning_object2 = new TmpCleaning('constructor_back_dev', 'video');
        $cleaning_object2->clean();
        $cleaning_object3 = new TmpCleaning('constructor_back', 'img');
        $cleaning_object3->clean();
        $cleaning_object4 = new TmpCleaning('constructor_back', 'video');
        $cleaning_object4->clean();
        return Command::SUCCESS;

    }
}