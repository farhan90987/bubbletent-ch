<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function array_map;
use function implode;

class IntersectionTypeNode implements TypeNode
{

	use NodeAttributes;

	/** @var TypeNode[] */
	public $types;

	/**
	 * @param TypeNode[] $types
	 */
	public function __construct(array $types)
	{
		$this->types = $types;
	}


	public function __toString(): string
	{
		return '(' . implode(' & ', array_map(static function (TypeNode $type): string {
			if ($type instanceof NullableTypeNode) {
				return '(' . $type . ')';
			}

			return (string) $type;
		}, $this->types)) . ')';
	}

}
