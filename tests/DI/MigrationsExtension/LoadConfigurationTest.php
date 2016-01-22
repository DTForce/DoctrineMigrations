<?php

namespace DTForce\DoctrineMigrations\Tests\DI\MigrationsExtension;

use Assert\InvalidArgumentException;
use DTForce\DoctrineMigrations\Configuration\Configuration;
use DTForce\DoctrineMigrations\DI\MigrationsExtension;
use Nette\DI\Compiler;
use Nette\DI\ContainerBuilder;
use PHPUnit_Framework_TestCase;
use Symnedi\EventDispatcher\DI\EventDispatcherExtension;


class LoadConfigurationTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var MigrationsExtension
	 */
	private $extension;


	protected function setUp()
	{
		$containerBuilder = new ContainerBuilder;
		$containerBuilder->parameters = ['appDir' => __DIR__];

		$compiler = new Compiler($containerBuilder);

		$this->extension = new MigrationsExtension;
		$this->extension->setCompiler($compiler, 'migrations');
	}


	public function testLoadConfiguration()
	{
		$this->extension->loadConfiguration();
		$containerBuilder = $this->extension->getContainerBuilder();
		$containerBuilder->prepareClassList();

		$configurationDefinition = $containerBuilder->getDefinition($containerBuilder->getByType(Configuration::class));
		$this->assertSame(Configuration::class, $configurationDefinition->getClass());
	}

}
