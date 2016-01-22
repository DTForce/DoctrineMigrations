<?php

namespace DTForce\DoctrineMigrations\Tests\Configuration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use DTForce\DoctrineMigrations\Configuration\Configuration as ZenifyConfiguration;
use DTForce\DoctrineMigrations\Exception\Configuration\MigrationClassNotFoundException;
use DTForce\DoctrineMigrations\Tests\Configuration\ConfigurationSource\SomeService;
use DTForce\DoctrineMigrations\Tests\ContainerFactory;
use DTForce\DoctrineMigrations\Tests\Migrations\Version123;
use Nette\DI\Container;
use PHPUnit_Framework_TestCase;


final class ConfigurationTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Configuration
	 */
	private $configuration;


	protected function setUp()
	{
		$container = (new ContainerFactory)->create();
		$this->configuration = $container->getByType(Configuration::class);
	}


	public function testInject()
	{
		$migrations = $this->configuration->getMigrationsToExecute('up', 123);
		$this->assertCount(1, $migrations);

		/** @var Version $version */
		$version = $migrations[123];
		$this->assertInstanceOf(Version::class, $version);

		/** @var AbstractMigration|Version123 $migration */
		$migration = $version->getMigration();
		$this->assertInstanceOf(AbstractMigration::class, $migration);

		$this->assertInstanceOf(SomeService::class, $migration->someService);
	}


	public function testCreateDirectoryOnSet()
	{
		$migrationsDir = TEMP_DIR . '/migrations';
		$this->assertFileNotExists($migrationsDir);
		$this->configuration->setMigrationsDirectory($migrationsDir);
		$this->assertFileExists($migrationsDir);
	}


	public function testLoadMigrationsFromSubdirs()
	{
		$migrations = $this->configuration->getMigrations();
		$this->assertCount(2, $migrations);
	}


	public function testRegisterMigrationsClassExistCheck()
	{
		$migrationsDir = __DIR__ . '/ConfigurationSource/Migrations';

		$connectionMock = $this->prophesize(Connection::class);
		$containerMock = $this->prophesize(Container::class);

		$configuration = new ZenifyConfiguration($containerMock->reveal(), $connectionMock->reveal());

		$configuration->setMigrationsNamespace('Migrations');
		$configuration->setMigrationsDirectory($migrationsDir);

		$this->setExpectedException(
			MigrationClassNotFoundException::class,
			'Migration class "Migrations\Version789" was not found. Is it placed in "Migrations" namespace?'
		);
		$configuration->registerMigrationsFromDirectory($migrationsDir);
	}

}
