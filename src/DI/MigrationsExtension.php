<?php

/**
 * This file is part of DTForce\DoctrineMigrations
 *
 * Copyright (c) 2016 DTForce (http://www.dtforce.com)
 * Copyright (c) 2014 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace DTForce\DoctrineMigrations\DI;

use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand;
use DTForce\DoctrineMigrations\Configuration\Configuration;
use Nette\DI\CompilerExtension;
use Symfony\Component\Console\Application;


final class MigrationsExtension extends CompilerExtension
{

	/**
	 * @var string[]
	 */
	private $defaults = [
		'table' => 'doctrine_migrations',
		'directory' => '%appDir%/../migrations',
		'namespace' => 'Migrations'
	];


	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration()
	{
		$containerBuilder = $this->getContainerBuilder();

		$this->compiler->parseServices(
			$containerBuilder,
			$this->loadFromFile(__DIR__ . '/../config/services.neon')
		);

		$config = $this->getValidatedConfig();

		$this->addConfigurationDefinition($config);
	}


	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile()
	{
		$containerBuilder = $this->getContainerBuilder();
		$containerBuilder->prepareClassList();

		$this->setConfigurationToCommands();
		$this->loadCommandsToApplication();
	}


	private function addConfigurationDefinition(array $config)
	{
		$containerBuilder = $this->getContainerBuilder();
		$containerBuilder->addDefinition($this->prefix('configuration'))
			->setClass(Configuration::class)
			->addSetup('setMigrationsTableName', [$config['table']])
			->addSetup('setMigrationsDirectory', [$config['directory']])
			->addSetup('setMigrationsNamespace', [$config['namespace']])
			->addSetup('registerMigrationsFromDirectory', [$config['directory']]);
	}


	private function setConfigurationToCommands()
	{
		$containerBuilder = $this->getContainerBuilder();
		$configurationDefinition = $containerBuilder->getDefinition($containerBuilder->getByType(Configuration::class));

		foreach ($containerBuilder->findByType(AbstractCommand::class) as $commandDefinition) {
			$commandDefinition->addSetup('setMigrationConfiguration', ['@' . $configurationDefinition->getClass()]);
		}
	}


	private function loadCommandsToApplication()
	{
		$containerBuilder = $this->getContainerBuilder();
		$applicationDefinition = $containerBuilder->getDefinition($containerBuilder->getByType(Application::class));
		foreach ($containerBuilder->findByType(AbstractCommand::class) as $name => $commandDefinition) {
			$applicationDefinition->addSetup('add', ['@' . $name]);
		}
	}


	/**
	 * @return array
	 */
	private function getValidatedConfig()
	{
		$configuration = $this->getConfig($this->defaults);
		$this->validateConfig($configuration);

		$configuration['directory'] = $this->getContainerBuilder()->expand($configuration['directory']);

		return $configuration;
	}

}
