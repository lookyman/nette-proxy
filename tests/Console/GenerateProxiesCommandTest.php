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

		$container = (new Configurator())
			->setTempDirectory($tempDir)
			->setDebugMode(true)
			->addConfig(__DIR__ . '/../config/config.neon')
			->createContainer();
		$container->initialize();

		/** @var Application $application */
		$application = $container->getByType(Application::class);
		$tester = new CommandTester($application->find('lookyman:nette-proxy:generate'));
		$tester->execute(['command' => 'lookyman:nette-proxy:generate']);

		self::assertFileExists(__DIR__ . '/../temp/proxies/ProxyManagerGeneratedProxy__PM__LookymanNetteProxyTestsMockIService2FactoryGeneratedee1449ea0da0fdbf038d28254e9c7b3f.php');
		self::assertFileExists(__DIR__ . '/../temp/proxies/ProxyManagerGeneratedProxy__PM__LookymanNetteProxyTestsMockService1Generateda427b08305d9796d18525e91e7b08e56.php');
	}
}
