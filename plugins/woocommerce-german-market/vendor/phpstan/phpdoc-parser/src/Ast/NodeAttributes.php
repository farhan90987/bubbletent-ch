<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast;

use function array_key_exists;

trait NodeAttributes
{

	/** @var array<string, mixed> */
	private $attributes = [];

	/**
	 * @param mixed $value
	 */
	public function setAttribute(string $key, $value): void
	{
		$this->attributes[$key] = $value;
	}

	public function hasAttribute(string $key): bool
	{
		return array_key_exists($key, $this->attributes);
	}

	/**
	 * @return mixed
	 */
	public function getAttribute(string $key)
	{
		if ($this->hasAttribute($key)) {
			return $this->attributes[$key];
		}

		return null;
	}

}
