<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ConstExprStringNode implements ConstExprNode
{

	use NodeAttributes;

	/** @var string */
	public $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}


	public function __toString(): string
	{
		return $this->value;
	}

}
