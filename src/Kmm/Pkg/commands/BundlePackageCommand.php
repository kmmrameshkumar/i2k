<?php namespace Kmm\Pkg\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BundlePackageCommand extends AbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bundle:package';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'A fresh package.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$modules = $this->option('modules');
		$install = $this->option('install');
		
		$defaultmodules = $this->app['config']->get('pkg::modules');

		if($modules) {
			$opts['--modules'] = $modules;
		}else
			$opts['--modules'] = $defaultmodules;

		$this->call('cache:clear');

		if(!$install) $this->call('migrate:rollback');
		
		
		$this->call('modules:migrate', $opts);

		$this->call('modules:seed', $opts);

		$this->info('Done!');
		
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
			array('modules', null, InputOption::VALUE_OPTIONAL, 'list of modules.', null),
			array('install', null, InputOption::VALUE_NONE, 'Install new package.', null),
		);
	}

}
