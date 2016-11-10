<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Tests;

class Helpers
{
	/**
	 * @param string $path
	 */
	public static function initTempDir(string $path)
	{
		if (!@mkdir($path, 0777, true) && !is_dir($path)) {
			throw new \RuntimeException(sprintf('Cannot create temp directory %s', $path));
		}

		/** @var \SplFileInfo $entry */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
			$entry->isDir() ? rmdir((string) $entry) : unlink((string) $entry);
		}
	}
}
