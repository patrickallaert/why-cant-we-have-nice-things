<?php

namespace History\Console\Commands;

use History\Console\HistoryStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand
{
    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * Run the command.
     *
     * @param OutputInterface $output
     * @param InputInterface  $input
     */
    public function __invoke(OutputInterface $output, InputInterface $input)
    {
        $this->input = $input;
        $this->wrapOutput($output);

        // We don't define run as abstract so
        // we can overload arguments
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
