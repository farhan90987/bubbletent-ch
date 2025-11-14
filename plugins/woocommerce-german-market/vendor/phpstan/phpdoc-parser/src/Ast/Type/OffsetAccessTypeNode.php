<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;

class OffsetAccessTypeNode implements TypeNode
{

	use NodeAttributes;

	/** @var TypeNode */
	public $type;

	/** @var TypeNode */
	public $offset;

	public function __construct(TypeNode $type, TypeNode $offset)
	{
		$this->type = $type;
		$this->offset = $offset;
	}

	public function __toString(): string
	{
		if (
			$this->type instanceof CallableTypeNode
			|| $this->type instanceof NullableTypeNode
		) {
			return '(' . $this->type . ')[' . $this->offset . ']';
		}

		return $this->type . '[' . $this->offset . ']';
	}

}
