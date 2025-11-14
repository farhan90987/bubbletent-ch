<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class TypeAliasTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	/** @var string */
	public $alias;

	/** @var TypeNode */
	public $type;

	public function __construct(string $alias, TypeNode $type)
	{
		$this->alias = $alias;
		$this->type = $type;
	}


	public function __toString(): string
	{
		return trim("{$this->alias} {$this->type}");
	}

}
