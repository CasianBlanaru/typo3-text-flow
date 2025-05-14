<?php
declare(strict_types=1);
namespace PixelCoda\TextFlow\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PixelCoda\TextFlow\Install\PatternInstaller;

/**
 * Command zum Importieren der Silbentrennungsmuster
 */
class PatternCommand extends Command
{
    /**
     * @var PatternInstaller
     */
    protected $patternInstaller;

    public function __construct(PatternInstaller $patternInstaller)
    {
        $this->patternInstaller = $patternInstaller;
        parent::__construct();
    }

    /**
     * Konfiguration des Commands
     */
    protected function configure(): void
    {
        $this->setDescription('Import hyphenation patterns for TextFlow')
            ->setHelp('Imports all necessary hyphenation patterns for the TextFlow extension.')
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Import patterns for a specific language only'
            );
    }

    /**
     * Ausführung des Commands
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $language = $input->getOption('language');

        try {
            if ($language) {
                $output->writeln("<info>Importing patterns for language: {$language}</info>");
                $this->patternInstaller->installPatternsForLanguage($language);
            } else {
                $output->writeln("<info>Importing patterns for all languages</info>");
                $this->patternInstaller->installAllPatterns();
            }

            $output->writeln("<info>Pattern installation completed successfully.</info>");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>An error occurred: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
