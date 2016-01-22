<?php

/**
 * This file is part of DTForce\DoctrineMigrations
 *
 * Copyright (c) 2016 DTForce (http://www.dtforce.com)
 * Copyright (c) 2014 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace DTForce\DoctrineMigrations\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration as BaseConfiguration;
use Doctrine\DBAL\Migrations\OutputWriter;
use DTForce\DoctrineMigrations\Exception\Configuration\MigrationClassNotFoundException;
use Nette\DI\Container;


final class Configuration extends BaseConfiguration
{

	/** @var Container  */
	private $container;


	public function __construct(Container $container, Connection $connection, OutputWriter $outputWriter = NULL)
	{
		$this->container = $container;
		parent::__construct($connection, $outputWriter);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getMigrationsToExecute($direction, $to)
	{
		$versions = parent::getMigrationsToExecute($direction, $to);
		foreach ($versions as $version) {
			$this->container->callInjects($version->getMigration());
		}
		return $versions;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getVersion($version)
	{
		$version = parent::getVersion($version);
		$this->container->callInjects($version->getMigration());
		return $version;
	}


	/**
	 * {@inheritdoc}
	 */
	public function setMigrationsDirectory($directory)
	{
		$this->createDirectoryIfNotExists($directory);
		parent::setMigrationsDirectory($directory);
	}


	/**
	 * {@inheritdoc}
	 */
	public function registerMigration($version, $class)
	{
		$this->ensureMigrationClassExists($class);
		parent::registerMigration($version, $class);
	}


	/**
	 * @param string $directory
	 */
	private function createDirectoryIfNotExists($directory)
	{
		if ( ! file_exists($directory)) {
			mkdir($directory, 0755, TRUE);
		}
	}


	/**
	 * @param string $class
	 *
	 * @throws MigrationClassNotFoundException
	 */
	private function ensureMigrationClassExists($class)
	{
		if ( ! class_exists($class)) {
			throw new MigrationClassNotFoundException(
				sprintf(
					'Migration class "%s" was not found. Is it placed in "%s" namespace?',
					$class,
					$this->getMigrationsNamespace()
				)
			);
		}
	}

}
