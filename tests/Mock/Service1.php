<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Tests\Mock;

class Service1
{
	/**
	 * @return string
	 */
	public function foo(): string
	{
		return 'bar';
	}
}
