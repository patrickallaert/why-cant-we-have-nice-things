<?php
namespace History\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

class HistoryStyle extends SymfonyStyle
{
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
     * @param array    $entries
     * @param callable $callback
     */
    public function progressIterator($entries, callable $callback)
    {
        $this->progressStart(count($entries));
        foreach ($entries as $entry) {
            if ($callback($entry) === false) {
                return;
            }

            $this->progressAdvance();
        }

        $this->progressFinish();
    }
}