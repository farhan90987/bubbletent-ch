<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;

class ConstExprArrayNode implements ConstExprNode
{

	use NodeAttributes;

	/** @var ConstExprArrayItemNode[] */
	public $items;

	/**
	 * @param ConstExprArrayItemNode[] $items
	 */
	public function __construct(array $items)
	{
		$this->items = $items;
	}


	public function __toString(): string
	{
		return '[' . implode(', ', $this->items) . ']';
	}

}
