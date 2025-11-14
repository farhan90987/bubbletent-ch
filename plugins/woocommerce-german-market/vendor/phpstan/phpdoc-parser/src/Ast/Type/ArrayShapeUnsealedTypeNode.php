<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;

class ArrayShapeUnsealedTypeNode implements Node
{

	use NodeAttributes;

	/** @var TypeNode */
	public $valueType;

	/** @var TypeNode|null */
	public $keyType;

	public function __construct(TypeNode $valueType, ?TypeNode $keyType)
	{
		$this->valueType = $valueType;
		$this->keyType = $keyType;
	}

	public function __toString(): string
	{
		if ($this->keyType !== null) {
			return sprintf('<%s, %s>', $this->keyType, $this->valueType);
		}
		return sprintf('<%s>', $this->valueType);
	}

}
