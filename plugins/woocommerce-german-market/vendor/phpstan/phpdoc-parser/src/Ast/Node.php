<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast;

interface Node
{

	public function __toString(): string;

	/**
	 * @param mixed $value
	 */
	public function setAttribute(string $key, $value): void;

	public function hasAttribute(string $key): bool;

	/**
	 * @return mixed
	 */
	public function getAttribute(string $key);

}
