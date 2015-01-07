<?php namespace Kmm\Pkg\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Composer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Project\Packages\Models\Package;
use App\Project\Modules\Models\Module;
use App\Project\Tags\Models\Tag;
use App\Project\Tags\Models\TagModule;
use App\Project\Tags\Models\PackageTag;
use App\Project\Users\Models\User;

/**
* Bundle console commands
*/
abstract class TagAbstractCommand extends Command {

	/**
	 * IoC
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;
	
	/**
	 * @var string
	 */
	protected $package_name;

	/**
	 * @var string
	 */
	protected $tag_name;

	/**
	 * @var string
	 */
	protected $module_name;

	/**
	 * @var string
	 */
	protected $modules;
	
	/**
	 * @var string
	 */
	protected $label;
	
	/**
	 * @var string
	 */
	protected $slug;
	
	/**
	 * @var string
	 */
	protected $icon;
	
	/**
	 * @var bool
	 */
	protected $active;

	/**
	 * @var int
	 */
	protected $order;

	/**
	 * @var bool
	 */
	protected $show;
	
	/**
	 * @var int
	 */
	protected $email;

	/**
	 * @var bool
	 */
	protected $register;
	
	/**
	 * @var bool
	 */
	protected $remove;
	
	/**
	 * @var bool
	 */
	protected $suspend;
	
	/**
	 * @var bool
	 */
	protected $activate;


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
	 * register, remove or suspend a module
	 */
	public function performModule()
	{

		if($this->register){
			$module = Module::whereName($this->module_name)->first();
			
			if($module==null){

				if(isset($this->module_id)){

					$m = Module::create([
						'id' => $this->module_id,
						'name' => $this->module_name,
						'label' => $this->label,
						'slug' => $this->slug,
						'icon' => $this->icon,
						'active' => (int)$this->active
					]);

					$d = $m->id;
					$m->id = $this->module_id;
					$m->save();
					// $this->info('Module id changed '.$d. ' to '. $this->module_id);

				}else{

					$m = Module::create([
						'name' => $this->module_name,
						'label' => $this->label,
						'slug' => $this->slug,
						'icon' => $this->icon,
						'active' => (int)$this->active
					]);
				}

				$this->info('Module '.$this->module_name. ' registered');

			}else{
	
				$this->error('Module '.$this->module_name. ' already exits');
			}

		}elseif ($this->remove) {
			
			$module = Module::whereName($this->module_name)->first();
			$module->delete();
			
			$this->info('Module '.$this->module_name. ' removed');


		}elseif ($this->suspend) {

			$module = Module::whereName($this->module_name)->first();
			$module->active = 0;
			$module->save();

			$this->info('Module '.$this->module_name. ' suspended');

		}elseif ($this->activate) {

			$module = Module::whereName($this->module_name)->first();
			$module->active = 1;
			$module->save();

			$this->info('Module '.$this->module_name. ' activated');
		}
	}

	public function performDefaultModules()
	{
		TagModule::truncate();
		Module::truncate();
		
		Tag::truncate();
		
		PackageTag::truncate();
		Package::truncate();

		$ortho = new \App\Project\Packages\Seeds\Ortho;

		foreach ($ortho->modules() as $modules) {
			foreach ($ortho->$modules() as $module) {

				$this->register = true;

				$this->module_name = $module['name'];
				$this->label = $module['name'];
				$this->slug = $module['slug'];
				$this->icon = $module['icon'];
				$this->active = $module['active'];
				// $this->module_id = $module['moduleId'];

				$this->performModule();
			}
		}
		
	}

	public function performDefaultTags()
	{
		$this->performDefaultModules();

		$packages = ['Ortho', 'Neuro'];

		foreach ($packages as $package) {

			$collection = $this->getPackageClass($package);
			
			$this->package_name = $package;

			foreach ($collection->tags() as $tag) {

				$this->register = true;

				$this->tag_name = $this->package_name.$tag['name'];
				$this->label = $tag['name'];
				$this->slug = $tag['default_slug'];
				$this->icon = $tag['icon'];
				$this->active = $tag['active'];
				$this->order = $tag['order'];
				$this->show = $tag['show'];
				
				$modules = array_fetch($collection->$tag['module'](), 'name');
				
				$this->modules = implode(",", $modules);
				
				$this->performTag();
			}			
		}
		
	}

	public function getPackageClass($package)
	{
		$pkg_class = "App\Project\Packages\Seeds\\$package";

		return new $pkg_class;
	}

	public function performTag()
	{

		$modules = explode(",", $this->modules);

		$p_exists = false;
		$t_exists = false;
		$order = 1;
		foreach ($modules as $module) {

			$mod = Module::whereName($module)->first();
			
			if($mod!=null){
				
				if(!$p_exists){
					// create Package name return Package object
					$new_pkg = Package::whereName($this->package_name)->first();

					if($new_pkg==null) {

						$new_pkg = Package::create([
							'name' => $this->package_name
						]);
					
						// TODO: Fix this
						if($this->package_id!=null){
							$new_pkg = Package::find($new_pkg->id);
							$new_pkg->id = $this->package_id;
							$new_pkg->save();
						}

						$this->info('Package '.$this->package_name. ' registered');
					}
					$p_exists = true;
				}

				if(!$t_exists){
					$new_tag = Tag::whereName($new_pkg->package_name.$this->tag_name)->first();

					// create Tage name return Package object
					if($new_tag==null){
						$new_tag = Tag::create([
								'name' => $this->tag_name,
								'label' => $this->label,
								'default_slug' => $this->slug,
								'icon' => $this->icon,
								'active' => $this->active,
								'order' => $this->order,
								'show' => $this->show,
							]);
						
						// TODO: Fix this
						if($this->tag_id!=null){
							$new_tag = Tag::find($new_tag->id);
							$new_tag->id = $this->tag_id;
							$new_tag->save();
						}

						$this->info('Tag '.$new_tag->name. ' registered');
					}
					$t_exists = true;

					$new_pkg->Tags()->attach([$new_tag->id]);
				}
				
				$parent_id = $mod->id;

				// if($mod->id==1 || $mod->id==2) $parent_id = 3;
				$user_id = User::min('id');
				$new_tag->Modules()->attach([$mod->id=> ['order'=>$order, 'parent_id'=>$parent_id, 'user_id'=>$user_id]]);
				
				$order++;
			}else{
				$lower = strtolower($module);
				$this->error("Module  $module doesn't exists. Register and with\n<info>php artisan bundle:module $module --label=$lower --slug=$lower --icon=$lower --active --register</info>");
				// $this->call("bundle:module", ["module"=>$module, '--label'=>$lower, '--slug'=>$lower, '--icon'=>$lower, '--active'=>true, '--register'=>true]);
			}

		}
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