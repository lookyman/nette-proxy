<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\DI;

use Lookyman\Nette\Proxy\Console\GenerateProxiesCommand;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\Helpers;
use Nette\DI\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Closure;
use Nette\Utils\Validators;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

class ProxyExtension extends CompilerExtension
{
	const TAG_LAZY = 'lookyman.lazy';

	/**
	 * @var array
	 */
	private $defaults = [
		'proxyDir' => '%appDir%/../temp/proxies',
		'default' => false,
	];

	/**
	 * @var array
	 */
	private $excluded = [
		'Nette\ComponentModel\Component',
		'Nette\Database\Connection',
		'Nette\Http\Request',
		'Nette\Security\User',
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);
		Validators::assertField($config, 'default', 'bool');

		// create proxy dir
		Validators::assertField($config, 'proxyDir', 'string');
		$config['proxyDir'] = Helpers::expand($config['proxyDir'], $builder->parameters);
		if (!@mkdir($config['proxyDir'], 0777, true) && !is_dir($config['proxyDir'])) {
			// @codeCoverageIgnoreStart
			throw new \RuntimeException(sprintf('Cannot create proxy directory %s', $config['proxyDir']));
			// @codeCoverageIgnoreEnd
		}

		// generator strategy
		$builder->addDefinition($this->prefix('generatorStrategy'))
			->setFactory(FileWriterGeneratorStrategy::class, [new Statement(FileLocator::class, [$config['proxyDir']])])
			->setAutowired(false)
			->addTag(self::TAG_LAZY, false);

		// configuration
		$builder->addDefinition($this->prefix('configuration'))
			->setClass(Configuration::class)
			->addSetup('setProxiesTargetDir', [$config['proxyDir']])
			->addSetup('setGeneratorStrategy', [$this->prefix('@generatorStrategy')])
			->setAutowired(false)
			->addTag(self::TAG_LAZY, false);

		// proxy factory
		$builder->addDefinition($this->prefix('lazyLoadingValueHolderFactory'))
			->setFactory(LazyLoadingValueHolderFactory::class, [$this->prefix('@configuration')])
			->setAutowired(false)
			->addTag(self::TAG_LAZY, false);

		// command
		/** @var \Kdyby\Console\DI\ConsoleExtension $extension */
		foreach ($this->compiler->getExtensions('Kdyby\Console\DI\ConsoleExtension') as $extension) {
			$builder->addDefinition($this->prefix('generateProxiesCommand'))
				->setClass(GenerateProxiesCommand::class)
				->addTag($extension::TAG_COMMAND);
			break;
		}
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// do not proxy these services
		// @see https://ocramius.github.io/ProxyManager/docs/lazy-loading-value-holder.html#known-limitations
		foreach ($this->excluded as $type) {
			foreach ($builder->findByType($type) as $def) {
				$def->addTag(self::TAG_LAZY, false);
			}
		}

		// add service type as tag attribute
		foreach (array_keys($config['default'] ? $builder->getDefinitions() : $builder->findByTag(self::TAG_LAZY)) as $name) {
			$def = $builder->getDefinition($name);
			if ($def->getTag(self::TAG_LAZY) === false) {
				$def->setTags(array_diff_key($def->getTags(), [self::TAG_LAZY => null]));
				continue;
			}
			$def->addTag(self::TAG_LAZY, $def->getImplement() ?: $def->getClass());
		}
	}

	/**
	 * @param ClassType $class
	 */
	public function afterCompile(ClassType $class)
	{
		foreach ($this->getContainerBuilder()->findByTag(self::TAG_LAZY) as $name => $type) {
			// modify original method body to return proxy instead
			$method = $class->getMethod(Container::getMethodName($name));
			$method->setBody(sprintf(
				"return \$this->getService('%s')->createProxy(\n\t%s::class,\n\tfunction (&\$wrappedObject, \$proxy, \$method, \$parameters, &\$initializer) {\n\t\t\$wrappedObject = (%s)();\n\t\t\$initializer = null;\n\t\treturn true;\n\t}\n);",
				$this->prefix('lazyLoadingValueHolderFactory'), $type, ltrim(preg_replace('#^#m', "\t\t", (new Closure())->addBody($method->getBody())))
			));
		}

		// register proxy autoloader
		$init = $class->getMethod('initialize');
		$body = $init->getBody();
		$init->setBody("spl_autoload_register(\$this->getService(?)->getProxyAutoloader());\n", [$this->prefix('configuration')])->addBody($body);
	}
}
