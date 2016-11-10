<?php
declare(strict_types=1);

namespace Lookyman\Nette\Proxy\Tests\Mock;

interface IService2Factory
{
	/**
	 * @return Service2
	 */
	public function create(): Service2;
}
