<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Console;

use Lookyman\Nette\Proxy\DI\ProxyExtension;
use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProxiesCommand extends Command
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
	{
		$this->setName('lookyman:nette-proxy:generate')
			->setDescription('Generate proxies for lazy services')
			->addOption('debug', null, InputOption::VALUE_NONE, 'Print debug info');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$debug = $input->getOption('debug');

		foreach (array_keys($this->container->findByTag(ProxyExtension::TAG_LAZY)) as $name) {
			$this->container->getService($name);
			if ($debug) {
				$output->writeln(sprintf('Proxy for service %s generated', $name));
			}
		}

		return 0;
	}
}
