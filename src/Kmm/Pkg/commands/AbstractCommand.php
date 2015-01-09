<?php namespace Kmm\Pkg\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Composer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Project\Users\Models\User;
use App\Project\Packages\Models\Package as Package;
use App\Project\Modules\Models\Module;
use App\Project\Menus\Models\Menu;
use App\Project\Roles\Models\RoleModule;
use App\Project\Tags\Models\TagModule;
use App\Project\UserModules\Models\UserModule;
use App\Project\Permissions\Models\Permission;
use App\Project\Packages\Models\UserPackage;
use DB, Config;

/**
* Bundle console commands
*/
abstract class AbstractCommand extends Command {

	/**
	 * IoC
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * @var string
	 */
	protected $schema;

	/**
	 * @var IoC
	 */
	protected $userRepo;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $packageRepo;
	
	/**
	 * @var string
	 */
	protected $modules;

	/**
	 * @var string
	 */
	protected $menus;

	/**
	 * @var Array
	 */
	protected $package_name;

	/**
	 * @var int
	 */
	protected $package_id;

	/**
	 * @var Array
	 */
	protected $tags_data;

	/**
	 * @var Array
	 */
	protected $modules_data;

	/**
	 * DI
	 *
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		parent::__construct();
		$this->app = $app;
	}

	/**
	 * register practice
	 */
	public function register()
	{
		// Create Database
		$this->createDatbase();

		// Set default database
		$this->setNewConnection();

		// Migrate default tables
		$this->migrate();

		// Seed user entry and modules
		$this->seed();
	}

	public function remove()
	{
		$db = \DB::statement('DROP DATABASE '.$this->schema);
		$this->info('Practice '.$this->email.' has been removed');
	}

	public function removePractice($force)
	{

		$u = User::whereEmail($this->email)->first();
		
		$u->deleted_at =  \Carbon\Carbon::now();

		$force ? $u->delete() : $u->save();

		$this->info('Removed from master');
	}

	public function suspend()
	{
		$this->info('...................suspend.......................');
	}

	/**
	 * masterDBConnection sets master db connection
	 */
	public function setConnection()
	{
		$config = $this->app->make('config');

	    $connections = $config->get('database.connections');
	    
	    $default = $connections[$config->get('database.default')];

	    $new = $default;

	    $new['database'] = $default['master'];

	    $this->app->make('config')->set('database.connections.'.$default['master'], $new);
	}

	public function getMasterDatabase()
	{
		$config = $this->app['config'];

	    $connections = $config->get('database.connections');
	    
	    $default = $connections[$config->get('database.default')];

		return $default['master'];
	}

	/**
	 * setNewDbConnection override the config file
	 */
	public function setNewConnection()
	{
	    $config = $this->app->make('config');

	    $connections = $config->get('database.connections');
	    
	    $default = $connections[$config->get('database.default')];

	    $new = $default;

	    $new['database'] = $this->schema;

	    return $this->app->make('config')->set('database.connections.'.$this->schema, $new);
	}


	/**
	 * createDatbase creats tenant database
	 * 
	 * @return Object
	 */
	public function createDatbase()
	{
		$db = \DB::statement('CREATE DATABASE IF NOT EXISTS '.$this->schema);
	}

	/**
	 * migrate migrate default tables
	 * 
	 * @return Object
	 */
	public function migrate()
	{
		$this->call('modules:migrate', ['--database'=>$this->schema]);
		\DB::statement("ALTER TABLE users DROP INDEX users_email_unique");
	}
	
	/**
	 * migrate seed default tables entries
	 * 
	 * @return Object
	 */
	public function seed()
	{

		// TODO: Fix this issue
		// $this->userRepo->save($this->user);

		$this->seedUser();
		$this->seedModules();
		$this->seedTags();

		// User::create( $this->user->toArray() );

		
		$this->call('modules:seed', ['--modules'=>"Defaults"]);

		$this->seedSecretaryPermissions();
	}

	public function seedSecretaryPermissions()
	{
		$modules = $this->getOnlyModules();

		foreach ($modules as $module) {
			$m = $module['name'];
			$mp = Config::get("{$m}::permissions");
			if(!empty($mp)){
				$exist_modules[] = $module['id'];
				foreach ($mp as $action) {
					Permission::create([
						'role_id' => 4,
						'type' => 'allow',
						'action' => $action,
						'resource' => $m
					]);
				}
			}
		}

		$tag_modules = TagModule::where('user_id', $this->user->id)->get();

		foreach ($tag_modules as $tag_module) {
			$except = [33,34,35];
			// Roles, Permissions, Users
			if(in_array($tag_module->module_id, $except)) continue;

			RoleModule::create([
				'tag_id' => $tag_module->tag_id,
				'module_id' => $tag_module->module_id,
				'order' => $tag_module->order,
				'show' => $tag_module->show,
				'parent_id' =>  $tag_module->parent_id,
				'role_id' =>  4,
				'is_customised' =>  $tag_module->is_customised
			]);

		}

	}

	/**
	 * seedUser add new tenant
	 */
	public function seedUser()
	{
		// TODO: Fix this issue
		
		User::create( $this->user->toArray() );

		$u = User::whereEmail($this->email)->first();
		$u->id = $this->user->id;
		$u->package_activated = 1;
		$u->save();
		
		DB::statement("ALTER TABLE users AUTO_INCREMENT = {$this->user->id};");

		$u->Packages()->attach([$this->package_id=>['active'=>1]]);
	}

	public function modifyPackageActivation()
	{
		$u = User::whereEmail($this->email)->first();
		$u->id = $this->user->id;
		$u->package_activated = 1;
		$u->save();

		$p = UserPackage::whereUserId($u->id)->whereActive(0)->first();
		if(isset($p->active)){
			$p->active = 1;
			$p->save();
		}
	}

	/**
	 * seedPackage
	 */
	public function seedPackage()
	{

		// $this->call('practice:package', ['--name'=>'Ortho', 'email'=>$this->user->email, '--register']);
		foreach($this->packageRepo as $package){
			
			$this->info("Package ".$package['name']);
			
			$new_pkg = Package::create([
				'name' => $package['name']
			]);

			// TODO: Fix this
			$pkg = Package::find($new_pkg->id);
			$pkg->id = $package['id'];
			$pkg->save();

			foreach($package['modules'] as $module)
			{
				
				$this->info("Module ".$module['name']);
				$moduleName = $module['name'];
				$new_mod = Module::create([
					'name' => $module['name']
				]);

				// TODO: Fix this
				$mod = Module::find($new_mod->id);
				$mod->id=$module['id'];
				$mod->save();

				$pkg->Modules()->attach([$mod->id]);

				UserModule::create([
					"user_id" => $this->user->id,
					"module_id" => $mod->id,
					"is_customised" => 0
				]);
		
				$this->menus($mod, $this->menus[$module['name']]);
			}
		}

		// foreach($packageRepo as $package){
		// 	   	foreach ($package->modules()->get() as $menus){
		// 	   		echo $menus;
		// 	   	}
		// 	}
	}

	public function menus($mod, $menus)
	{
		foreach($menus as $menu)
		{
			$item = Menu::create([
				'label' => $menu['label'],
				'slug' => $menu['slug'],
				'icon' => $menu['icon'],
				'active' => $menu['active']
			]);

			$mod->Menus()->attach([$item->id]);
		}

		
	}
	
	/**
	 * seedModules
	 */
	public function seedModules()
	{
		$modules = $this->getOnlyModules();

		foreach ($modules as $module) {
			
			if($module['active']==1){

				$this->call("bundle:module", [
						'module' => $module['name'],
						'--moduleId'=> $module['id'],
						'--label'=> $module['label'],
						'--slug'=> $module['slug'],
						'--icon'=> $module['icon'], 
						'--active'=> $module['active'],
						'--register'=>true
					]);
			}

		}
	}

	public function getOnlyModules()
	{
		$packages = $this->modules_data;
		$tags = $this->tags_data;

		foreach ($tags as $pkg => $tag) {
			foreach ($tag as $key => $m) {
				$tag_name = $tag[$key]['name'];
				foreach ($packages[$pkg][$tag_name] as $key => $modules) {
					foreach ($modules as $key => $module) {
						$s[] = $module;
					}
				}
			}
		}

		return $s;
	}
	
	public function getModules($tags)
	{
		foreach ($tags as $key => $modules) {
			foreach ($modules as $key => $module) {
				$list[] = $module['name'];
			}
		}
		
		return implode(",", $list);
	}

	public function seedTags()
	{
		$packages = $this->modules_data;
		$tags = $this->tags_data;

		$packages = $this->modules_data;
		$tags = $this->tags_data;
		
		foreach ($this->tags_data[$this->package_name] as $key => $value) {
			// var_dump($value);
			$tagId = $value['id'];
			$modules = $this->getModules($this->modules_data[$this->package_name][$value['name']]);

			if($value['active']==1){

				$this->call('bundle:tag', ['--package'=>$this->package_name,
						 '--packageId'=> $this->package_id,
						 '--tag'=>$value['name'],
						 '--tagId'=>$tagId,
						 '--modules'=>$modules,
						 '--label'=>$value['label'],
						 '--slug'=>$value['default_slug'],
						 '--icon'=>$value['icon'],
						 '--order'=>$value['order'],
						 '--show'=>$value['show'],
						 '--active'=>$value['active'],
						 '--register'=>true
					]);
		 	}
		}
	}

	/**
	 * seedMenus
	 */
	public function seedMenus()
	{
	
	}

	public function getNewSchema()
	{
		$repo = $this->app['config']->get('pkg::user_repository');
		$userRepo = new $repo();
		$user = $userRepo->isActive($this->email);

		$schema = $this->app['config']->get('pkg::schema');
		
		if(isset($user->id)) {
			$schema = $schema.$user->id;
		}else{
			$schema = null;
		}

		return $schema;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}
}
