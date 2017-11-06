<?php

namespace Asahasrabuddhe\Repositories\Commands;

use Asahasrabuddhe\Repositories\Commands\Creators\RuleCreator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeRuleCommand.
 */
class MakeRuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:rule';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new rule class';
    /**
     * @var
     */
    protected $creator;
    /**
     * @var
     */
    protected $composer;

    /**
     * @param RuleCreator $creator
     */
    public function __construct(RuleCreator $creator)
    {
        parent::__construct();
        // Set the creator.
        $this->creator = $creator;
        // Set the composer.
        $this->composer = app()['composer'];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get the arguments.
        $arguments = $this->argument();
        // Get the options.
        $options = $this->option();
        // Write rule.
        $this->writeRule($arguments, $options);
        // Dump autoload.
        $this->composer->dumpAutoloads();
    }

    /**
     * Write the rule.
     *
     * @param $arguments
     * @param $options
     */
    public function writeRule($arguments, $options)
    {
        // Set rule.
        $rule = $arguments['rule'];
        // Set model.
        $model = $options['model'];
        // Create the rule.
        if ($this->creator->create($rule, $model)) {
            // Information message.
            $this->info('Succesfully created the rule class.');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['rule', InputArgument::REQUIRED, 'The rule name.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model name.', null],
        ];
    }
}
