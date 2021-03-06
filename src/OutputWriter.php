<?php

/**
 * This file is part of DTForce\DoctrineMigrations
 *
 * Copyright (c) 2014 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace DTForce\DoctrineMigrations;

use Doctrine\DBAL\Migrations\OutputWriter as DoctrineOutputWriter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


final class OutputWriter extends DoctrineOutputWriter
{

	/**
	 * @var OutputInterface
	 */
	private $consoleOutput;


	public function setConsoleOutput(OutputInterface $consoleOutput)
	{
		$this->consoleOutput = $consoleOutput;
	}


	/**
	 * {@inheritdoc}
	 */
	public function write($message)
	{
		$this->getConsoleOutput()->writeln($message);
	}


	/**
	 * @return ConsoleOutput
	 */
	private function getConsoleOutput()
	{
		if ($this->consoleOutput === NULL) {
			$this->consoleOutput = new ConsoleOutput;
		}
		return $this->consoleOutput;
	}

}
