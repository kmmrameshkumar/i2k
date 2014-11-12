<?php namespace Kmm\Pkg\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Collection;

class BundlePracticeCommand extends AbstractCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'bundle:practice';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Collection of practice commands such as register, remove, suspend.';


	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->email = $this->argument('email');
		$register = $this->option('register');
		$remove = $this->option('remove');
		$suspend = $this->option('suspend');
		$force = $this->option('force');
		$package_activated = $this->option('package_activated');
		
		$this->setConnection();

		$repo = $this->app['config']->get('pkg::user_repository');
		$this->userRepo = new $repo();
		$this->user = $this->userRepo->isActive($this->email);
		$this->user_packages = $this->user->Packages()->whereActive(0)->orderBy('user_packages.id', 'DESC')->get(['user_packages.id','user_id', 'package_id', 'active'])->first();
		if(!isset($this->user_packages->package_id)){
			$this->error($this->email." doesn't have an active account. not activated");
			return;
		}

		if($package_activated) {
			$this->modifyPackageActivation();
			$this->info('modifyPackageActivation'.$this->email);
			return;
		}

		if(isset($this->user->id)) {
		
			$packageRepo = $this->app['config']->get('pkg::package_repository');
			$pkgRepo = new $packageRepo();
			// $this->user_packages->package_id = 2;
			$pkg = $pkgRepo->findByPackageId($this->user_packages->package_id);

			
			$moduleRepo = $this->app['config']->get('pkg::tag_repository');
			$modRepo = new $moduleRepo();
			
			
			$collection_package = new Collection;

			foreach ($pkg as $p) {
				$ms = $p->tags()->get();
				$this->package_name = $p->name;
				$this->tags_data[$p->name] = $ms->toArray();
				// dd($p->name);
				foreach ($ms as $m) {
					// var_dump($m->name);
					$mods = $modRepo->findByTagId($m->id);
					foreach ($mods as $mod) {
						// $this->modules_data[] = $mod->modules()->get()->toArray();
						$this->modules_data[$p->name][$mod->name][] = $mod->modules()->get()->toArray();
					}
				}
			}

		// dd($this->tags_data['Neuro']);
		// dd($this->modules_data['Neuro']);
		// dd('wait');

		// foreach ($tags as $pkg => $tag) {
		// 	foreach ($tag as $key => $m) {
		// 		$tag_name = $tag[$key]['name'];
		// 		foreach ($packages[$pkg][$tag_name] as $key => $modules) {
		// 			foreach ($modules as $key => $module) {
		// 				$s[] = $module;
		// 			}
		// 		}
		// 	}
		// }

			// dd($this->tags_data);
			// dd($this->modules_data['Ortho']["OrthoHeader4"]);

			// foreach ($pkg as $p) {
				
			// 	$ms = $p->tags()->get();

			// 	$collection_package->push($ms);

			// 	$this->modules_data[$p->name][] = $ms->toArray();

			// 	foreach ($ms as $m) {
			// 		// var_dump($m->name);
			// 		$mods = $modRepo->findByTagId($m->id);
			// 		dd($m->name);
			// 		foreach ($mods as $mod) {
			// 			$this->modules_data[$p->name][$m->name][] = $mod->modules()->get()->toArray();
			// 		}
			// 	}
			// }

			// $collection_package->each(function($pkg){
			// 	var_dump($pkg)
			// });

			// dd($this->tags_data);
			// dd($this->modules_data);
			// dd('wait');
			// foreach ($object as $key => $value) {
			// 	print_r($value->toArray());
			// }
			// Use it in abstract class
			$this->packageRepo = $pkg->toArray();

			$schema = $this->app['config']->get('pkg::schema');

			$this->schema = $schema.$this->user->id;

			$this->package_id = $this->user_packages->package_id;

			if ($register) {

				$this->info('Creating Practice "'.$this->email.'"');
				$this->register();
				// TODO: find out how to change db
				$this->call('migrate', ['--database'=>$this->getMasterDatabase()]);
				$this->modifyPackageActivation();
				$this->info('Practice successfully created to '.$this->email);

			}elseif ($remove) {
				
				$this->info('Removing Practice "'.$this->email.'"');
				$this->removePractice($force);
				$this->remove();
				$this->info('Practice removed successfully');

			}elseif ($suspend) {

				$this->info('Suspending Practice "'.$this->email.'"');
				$this->suspend();
				$this->info('Practice suspended successfully');

			}else{
				$this->info('No option provided. Practice not created!');
			}

		}else{

			$this->error('Invalid email or email is not activated');
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
			array('email', InputArgument::REQUIRED, 'The name of the practice being created.')
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
			array('email', null, InputOption::VALUE_OPTIONAL, 'An email option.', null),
			array('register', null, InputOption::VALUE_NONE, 'register new practice.', null),
			array('suspend', null, InputOption::VALUE_NONE, 'suspend practice.', null),
			array('remove', null, InputOption::VALUE_NONE, 'remove practice.', null),
			array('force', null, InputOption::VALUE_NONE, 'force remove practice.', null),
			array('package_activated', null, InputOption::VALUE_NONE, 'package activated for practice.', null),

		);
	}

}
