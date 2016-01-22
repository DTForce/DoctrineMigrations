<?php

namespace DTForce\DoctrineMigrations\Tests;

use DTForce\DoctrineMigrations\OutputWriter;
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;


class OutputWriterTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var OutputWriter
	 */
	private $outputWriter;


	protected function setUp()
	{
		$this->outputWriter = new OutputWriter;
	}


	public function testGetOutputWriterWhenNeeded()
	{
		$consoleOutput = PHPUnit_Framework_Assert::getObjectAttribute($this->outputWriter, 'consoleOutput');
		$this->assertNull($consoleOutput);

		$this->outputWriter->write('');

		$consoleOutput = PHPUnit_Framework_Assert::getObjectAttribute($this->outputWriter, 'consoleOutput');
		$this->assertInstanceOf(ConsoleOutput::class, $consoleOutput);
	}

}
