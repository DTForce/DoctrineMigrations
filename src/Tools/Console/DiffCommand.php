<?php

namespace DTForce\DoctrineMigrations\Tools\Console;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand as OverriddenDiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\MigrationDirectoryHelper;
use Symfony\Component\Console\Input\InputInterface;


class DiffCommand extends OverriddenDiffCommand
{

	const INDENT_LENGTH = 2 * 8;
	const MAX_LINE_LENGTH = 120;
	const IDEAL_LENGTH = 80;
	const INDENTATION = "\t\t";
	const INDENT_BREAK = "\t";


	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		static $template = NULL;
		if ($template === NULL) {
			$template = file_get_contents(__DIR__ . '/migration-template.tpl_php');
		}
		return $template;
	}


	/**
	 * @param string $version
	 * @param string $up
	 * @param string $down
	 * @return string
	 */
	protected function generateMigration(
		Configuration $configuration,
		InputInterface $input,
		$version,
		$up = NULL,
		$down = NULL
	) {
		$placeHolders = [
			'<namespace>',
			'<version>',
			'<up>',
			'<down>',
		];
		$driverName = $configuration->getConnection()
						->getDatabasePlatform()
						->getName();
		$replacements = [
			$configuration->getMigrationsNamespace(),
			$version,
			$up ? $this->processLines($up, $driverName) : NULL,
			$down ?  $this->processLines($down, $driverName) : NULL
		];
		$code = str_replace($placeHolders, $replacements, $this->getTemplate());
		$code = preg_replace('/^ +$/m', '', $code);
		$migrationDirectoryHelper = new MigrationDirectoryHelper($configuration);
		$dir = $migrationDirectoryHelper->getMigrationDirectory();
		$path = $dir . '/Version' . $version . '.php';

		file_put_contents($path, $code);

		if ($editorCmd = $input->getOption('editor-cmd')) {
			proc_open($editorCmd . ' ' . escapeshellarg($path), [], $pipes);
		}

		return $path;
	}


	/**
	 * @param string $up
	 * @return string
	 */
	protected function processLines($up, $driveName)
	{
		$rows = explode("\n", $up);
		unset($rows[0]);
		$checkLine = "\$this->abortIf(\n" . self::INDENTATION .
			"\t\$this->connection->getDatabasePlatform()->getName() !== " .
			"'$driveName',\n" . self::INDENTATION .
			"\t'Migration can only be executed safely on \\'$driveName\\'.'\n" .
			self::INDENTATION . ");";

		$newRows = [$checkLine];
		foreach ($rows as $row) {
			if (strlen($row) > (self::MAX_LINE_LENGTH - self::INDENT_LENGTH)) {
				$newRows = $this->splitLine($row, $newRows);

			} else {
				$newRows[] = $row;
			}
		}

		$out = '';
		foreach ($newRows as $newRow) {
			$newRow = rtrim($newRow);
			if (strlen($newRow) > 0) {
				$out .= self::INDENTATION;
				$out .= $newRow;
			}
			$out .= "\n";
		}
		return $out;
	}


	/**
	 * @param $row
	 * @param $newRows
	 * @return array
	 */
	protected function splitLine($row, $newRows)
	{
		$parts = explode(" ", $row);
		$lengthAsFar = (self::INDENT_LENGTH) - 1;
		$newRow = [];
		$firstLine = TRUE;
		foreach ($parts as $part) {
			$lengthAsFar += strlen($part) + 1;
			if ($lengthAsFar > self::IDEAL_LENGTH && count($newRow)) {
				$indent = $firstLine ? "" : self::INDENT_BREAK;
				$newRows[] = $indent . implode(' ', $newRow);
				$lengthAsFar = (self::INDENT_LENGTH) - 1;
				$firstLine = FALSE;
				$newRow = [];
			}
			$newRow[] = $part;
		}
		if (count($newRow)) {
			$indent = $firstLine ? "" : self::INDENT_BREAK;
			$newRows[] = $indent . implode(' ', $newRow);
		}
		return $newRows;
	}

}
