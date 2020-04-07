<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Tests\Console;

use Lookyman\Nette\Proxy\Console\GenerateProxiesCommand;
use Lookyman\Nette\Proxy\Tests\Helpers;
use Nette\Configurator;
use Symfony\Component\Console\Application;
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

		$command = $container->getByType(GenerateProxiesCommand::class);

		$application = new Application();
		$application->add($command);

		$tester = new CommandTester($command);
		$tester->execute([
			'command' => $command->getName(),
			'--debug' => null,
		]);

		self::assertNotEquals('', $tester->getDisplay());
		self::assertCount(2, new \FilesystemIterator($tempDir . '/../proxies', \FilesystemIterator::SKIP_DOTS));
	}
}
