<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Tests\DI;

use Lookyman\Nette\Proxy\Tests\Helpers;
use Lookyman\Nette\Proxy\Tests\Mock\IService2Factory;
use Lookyman\Nette\Proxy\Tests\Mock\Service1;
use Lookyman\Nette\Proxy\Tests\Mock\Service2;
use Nette\Configurator;
use ProxyManager\Proxy\ProxyInterface;

class ProxyExtensionTest extends \PHPUnit_Framework_TestCase
{
	public function testExtension()
	{
		$tempDir = __DIR__ . '/../temp/DI';
		Helpers::initTempDir($tempDir);

		$container = (new Configurator())
			->setTempDirectory($tempDir)
			->setDebugMode(true)
			->addConfig(__DIR__ . '/../config/config.neon')
			->createContainer();

		/** @var Service1 $service1 */
		$service1 = $container->getByType(Service1::class);
		self::assertInstanceOf(ProxyInterface::class, $service1);
		self::assertEquals('bar', $service1->foo());

		/** @var IService2Factory $service2Factory */
		$service2Factory = $container->getByType(IService2Factory::class);
		self::assertInstanceOf(ProxyInterface::class, $service2Factory);
		self::assertInstanceOf(Service2::class, $service2Factory->create());
	}
}
