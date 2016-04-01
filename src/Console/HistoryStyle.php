<?php

namespace History\Console;

use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HistoryStyle extends SymfonyStyle
{
    /**
     * HistoryStyle constructor.
     *
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     */
    public function __construct(InputInterface $input = null, OutputInterface $output = null)
    {
        $input = $input ?: new ArrayInput([]);
        $output = $output ?: new NullOutput();

        parent::__construct($input, $output);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->getVerbosity() === OutputInterface::VERBOSITY_DEBUG;
    }

    /**
     * @param string $message
     */
    public function comment($message)
    {
        $this->writeln('<comment>'.$message.'</comment>');
    }

    /**
     * @param string $message
     */
    public function error($message)
    {
        $this->writeln('<error>'.$message.'</error>');
    }

    /**
     * Show progress as we loop through an iterable.
     *
     * @param Collection|array $entries
     * @param callable         $callback
     *
     * @return \Generator|void
     */
    public function progressIterator($entries, callable $callback)
    {
        $this->progressStart(count($entries));
        foreach ($entries as $entry) {
            $result = $callback($entry);
            if ($result === false) {
                return;
            }

            yield $result;
            $this->progressAdvance();
        }

        $this->progressFinish();
    }
}
