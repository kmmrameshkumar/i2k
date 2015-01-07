<?php

return [

	/**
	 * Default modules to migrate; 
	 * Module names should be a string with comma without space
	 */
	'modules' => 'Packages,Tags,Modules,Users',

	/**
	 * Master user repository to get user details
	 */
	'user_repository' => '\App\Project\Users\Repositories\UserRepository',
	
	/**
	 * Master package repository to get user details
	 */
	'package_repository' => '\App\Project\Packages\Repositories\PackageRepository',

	/**
	 * Master module repository to get user details
	 */
	'module_repository' => '\App\Project\Modules\Repositories\ModuleRepository',

	/**
	 * Master module repository to get user details
	 */
	'tag_repository' => '\App\Project\Tags\Repositories\TagRepository',
	/**
	 * Schema prefix
	 */
	'schema' => 'tenant_',

	/**
	 * seed defaults
	 */
	'seed' => true,


];