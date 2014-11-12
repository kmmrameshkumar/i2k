<?php namespace Kmm\Pkg\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BundleTagCommand extends TagAbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bundle:tag';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Collection of tag commands, such as register, remove, suspend.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->package_name = $this->option('package');
		$this->tag_name = $this->option('tag');
		$this->modules = $this->option('modules');
		$this->label = $this->option('label');
		$this->slug = $this->option('slug');
		$this->icon = $this->option('icon');
		$this->active = $this->option('active');
		$this->order = $this->option('order');
		$this->show = $this->option('show');
		$this->package_id = $this->option('packageId');
		$this->tag_id = $this->option('tagId');
		
		$this->register = $this->option('register');
		$this->remove = $this->option('remove');
		$this->suspend = $this->option('suspend');
		$this->activate = $this->option('activate');
		$this->defaults = $this->option('defaults');
		
		if($this->defaults){
			$this->performDefaultTags();
		}else{
			$this->performTag();
		}

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
			array('package', null, InputOption::VALUE_OPTIONAL, 'name of the package.', null),
			array('packageId', null, InputOption::VALUE_OPTIONAL, 'id of the package.', null),
			array('tag', null, InputOption::VALUE_OPTIONAL, 'name of the tag.', null),
			array('tagId', null, InputOption::VALUE_OPTIONAL, 'id of the tad.', null),
			array('modules', null, InputOption::VALUE_OPTIONAL, 'list of existing modules.', null),
			array('label', null, InputOption::VALUE_OPTIONAL, 'name of the module.', null),
			array('slug', null, InputOption::VALUE_OPTIONAL, 'Link to module.', null),
			array('icon', null, InputOption::VALUE_OPTIONAL, 'module icon.', null),
			array('active', null, InputOption::VALUE_NONE, 'is active or not.', null),
			array('order', null, InputOption::VALUE_OPTIONAL, 'Tag order 1,2,...', null),
			array('show', null, InputOption::VALUE_NONE, 'show or hide in top', null),
			array('register', null, InputOption::VALUE_NONE, 'register new module.', null),
			array('remove', null, InputOption::VALUE_NONE, 'remove a module.', null),
			array('suspend', null, InputOption::VALUE_NONE, 'suspend a module.', null),
			array('activate', null, InputOption::VALUE_NONE, 'activate a module.', null),
			array('defaults', null, InputOption::VALUE_NONE, 'default modules.', null),
		);
	}

}
