<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Tests\Console;

use Kdyby\Console\Application;
use Lookyman\Nette\Proxy\Tests\Helpers;
use Nette\Configurator;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateProxiesCommandTest extends \PHPUnit_Framework_TestCase
{
	public function testCommand()
	{
		$tempDir = __DIR__ . '/../temp/Console';
		Helpers::initTempDir($tempDir);
		Helpers::initTempDir($tempDir . '/../proxies');

		$container = (new Configurator())
			->setTempDirectory($tempDir)
			->setDebugMode(true)
			->addConfig(__DIR__ . '/../config/config.neon')
			->createContainer();
		$container->initialize();

		/** @var Application $application */
		$application = $container->getByType(Application::class);
		$command = $application->find('lookyman:nette-proxy:generate');
		$tester = new CommandTester($command);
		$tester->execute([
			'command' => $command->getName(),
			'--debug' => null,
		]);

		self::assertNotEquals('', $tester->getDisplay());
		self::assertCount(2, new \FilesystemIterator($tempDir . '/../proxies', \FilesystemIterator::SKIP_DOTS));
	}
}
