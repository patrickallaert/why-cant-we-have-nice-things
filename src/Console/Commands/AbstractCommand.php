<?php
namespace History\Console\Commands;

use History\Console\HistoryStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand
{
    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * Run the command.
     *
     * @param OutputInterface $output
     */
    public function __invoke(OutputInterface $output)
    {
        $this->wrapOutput($output);
        $this->run();
    }

    /**
     * @param OutputInterface $output
     */
    protected function wrapOutput(OutputInterface $output)
    {
        $this->output = new HistoryStyle(new ArrayInput([]), $output);
    }
}
