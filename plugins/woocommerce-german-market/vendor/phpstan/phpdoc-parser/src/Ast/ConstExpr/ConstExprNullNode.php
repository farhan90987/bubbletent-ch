<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class ConstExprNullNode implements ConstExprNode
{

	use NodeAttributes;

	public function __toString(): string
	{
		return 'null';
	}

}
