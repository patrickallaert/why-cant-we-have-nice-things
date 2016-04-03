<?php

namespace History\Services\Traits;

use History\Console\HistoryStyle;

trait HasOutputTrait
{
    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @param HistoryStyle $output
     */
    public function setOutput(HistoryStyle $output)
    {
        $this->output = $output;
    }
}
