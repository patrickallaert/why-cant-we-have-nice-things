<?php

namespace History\Console\Commands\Sync;

use Exception;
use History\CommandBus\Commands\FetchMetadataCommand;
use History\Console\Commands\AbstractCommand;
use History\Entities\Models\User;
use League\Tactician\CommandBus;

class MetadataCommand extends AbstractCommand
{
    /**
     * @var CommandBus
     */
    protected $bus;

    /**
     * @param CommandBus $bus
     */
    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Run the command.
     */
    protected function run()
    {
        $users = User::all();
        $this->output->title('Refreshing metadata');

        foreach ($this->output->progressIterator($users) as $user) {
            try {
                $this->bus->handle(new FetchMetadataCommand($user));
            } catch (Exception $exception) {
                $this->output->writeln('<error>API limit reached</error>');

                return false;
            }
        }
    }
}
