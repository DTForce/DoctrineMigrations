<?php

namespace DTForce\DoctrineMigrations\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;


final class ManualUsageTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Application
	 */
	private $consoleApplication;


	protected function setUp()
	{
		$container = (new ContainerFactory)->createWithConfig(__DIR__ . '/config/manualUsage.neon');
		$this->consoleApplication = $container->getByType(Application::class);
	}


	public function testStatus()
	{
		$input = new ArrayInput(['command' => 'migrations:status']);
		$output = new BufferedOutput;

		$this->consoleApplication->setAutoExit(false);
		$status = $this->consoleApplication->run($input, $output);

		$this->assertSame(0, $status);
		$this->assertContains('Configuration', $output->fetch());
	}


	public function testMigrate()
	{
		$input = new ArrayInput(['command' => 'migrations:migrate']);
		$input->setInteractive(FALSE);

		$output = new BufferedOutput;

		$this->consoleApplication->setAutoExit(false);
		$status = $this->consoleApplication->run($input, $output);
		$this->assertSame(0, $status);
	}

}
