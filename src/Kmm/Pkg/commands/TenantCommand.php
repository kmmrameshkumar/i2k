<?php namespace Kmm\Pkg\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TenantCommand extends AbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bundle:tenant';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'tenant id';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$email = $this->option('email');

		$repo = $this->app['config']->get('pkg::user_repository');
		$this->userRepo = \App::make($repo);
		$user = $this->userRepo->findByEmail($email);
		
		$this->info($user->id);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}
	
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('email', null, InputOption::VALUE_OPTIONAL, 'id of the module.', null),
		);
	}

}
