<?php namespace Kmm\Pkg\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


// Completely for new module


class PracticePackagecommand extends AbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'practice:package';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Collection of package commands, such as register, remove, suspend';

	/**
	 * The package repo
	 * 
	 * @var string
	 */
	protected $packages;

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// get arguments
		$package = $this->argument('name');
		$this->email = $this->argument('email');
	
		// get options		
		$register = $this->option('register');
		$remove = $this->option('remove');
		$suspend = $this->option('suspend');

		// TODO: set master db connection
		// $this->setConnection();

		// $packageRepo = $this->app['config']->get('pkg::package_repository');
		// $this->packageRepo = new $packageRepo();
		// $this->packages = $this->packageRepo->findByPackage($package);

	    // foreach($this->package as $package){
	    // 	foreach ($package->modules()->get() as $menus){
	    // 		echo $menus;
	    // 	}
	    // }
	    
	    $this->schema = $this->getNewSchema();

	    if($this->schema!=null){
	    	if($register){
	    		$this->registerPackage($package);
	    	}
	    }else{
	    	$this->error('Tenant does not extis');
	    }

	    
	}

	/**
	 * registerPackage register new package
	 * @param  string $package 
	 */
	public function registerPackage($package='')
	{
		$this->info("Seeding {$package} to {$this->schema}");
		
		$this->setNewConnection();

		$tenantPackageRepo = $this->app['config']->get('pkg::package_repository');
		$packageRepo = new $tenantPackageRepo();


		foreach ($this->packages as $package) {
			$packageRepo->save($package);
			
			\App\Project\Packages\Models\Package::create([
				'name' => "ramesh"
			]);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The name of the module being created.'),
			array('email', InputArgument::REQUIRED, 'Tenant mail id.')
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
			array('name', null, InputOption::VALUE_OPTIONAL, 'name of the module option.', null),
			array('register', null, InputOption::VALUE_NONE, 'register new module.', null),
			array('suspend', null, InputOption::VALUE_NONE, 'suspend module.', null),
			array('remove', null, InputOption::VALUE_NONE, 'remove module.', null),
		);
	}

}
