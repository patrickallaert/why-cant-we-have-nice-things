<?php
namespace History\Console\Commands;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Run the command.
     */
    abstract protected function run();

    /**
     * Run the command.
     *
     * @param OutputInterface $output
     */
    public function __invoke(OutputInterface $output)
    {
        $this->output = new SymfonyStyle(new ArrayInput([]), $output);
        $this->run();
    }

    /**
     * @param string $message
     */
    protected function comment($message)
    {
        $this->output->writeln('<comment>'.$message.'</comment>');
    }

    /**
     * Show progress as we loop through an iterable.
     *
     * @param array    $entries
     * @param callable $callback
     */
    protected function progressIterator($entries, callable $callback)
    {
        $progress = new ProgressBar($this->output, count($entries));
        foreach ($entries as $entry) {
            if ($callback($entry) === false) {
                return;
            }

            $progress->advance();
        }

        $progress->finish();
    }
}
