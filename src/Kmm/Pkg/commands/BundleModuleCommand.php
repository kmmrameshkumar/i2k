<?php namespace Kmm\Pkg\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Project\Modules\Models\Module;

class BundleModuleCommand extends TagAbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bundle:module';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Collection of module commands, such as register, remove, suspend.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->module_name = $this->argument('module');
		
		$this->label = $this->option('label');
		$this->slug = $this->option('slug');
		$this->icon = $this->option('icon');
		$this->active = $this->option('active');
		$this->module_id = $this->option('moduleId');
		
		$this->register = $this->option('register');
		$this->remove = $this->option('remove');
		$this->suspend = $this->option('suspend');
		$this->activate = $this->option('activate');
		
		$this->performModule();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('module', InputArgument::REQUIRED, 'The name of the module being created.'),
		);
	}
	
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('moduleId', null, InputOption::VALUE_OPTIONAL, 'id of the module.', null),
			array('label', null, InputOption::VALUE_OPTIONAL, 'name of the module.', null),
			array('slug', null, InputOption::VALUE_OPTIONAL, 'Link to module.', null),
			array('icon', null, InputOption::VALUE_OPTIONAL, 'module icon.', null),
			array('active', null, InputOption::VALUE_NONE, 'is active or not.', null),
			array('register', null, InputOption::VALUE_NONE, 'register new module.', null),
			array('remove', null, InputOption::VALUE_NONE, 'remove a module.', null),
			array('suspend', null, InputOption::VALUE_NONE, 'suspend a module.', null),
			array('activate', null, InputOption::VALUE_NONE, 'activate a module.', null),
		);
	}

}
