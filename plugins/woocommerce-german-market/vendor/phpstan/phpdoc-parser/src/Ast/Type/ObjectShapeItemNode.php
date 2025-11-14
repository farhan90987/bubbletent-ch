<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;

class ObjectShapeItemNode implements TypeNode
{

	use NodeAttributes;

	/** @var ConstExprStringNode|IdentifierTypeNode */
	public $keyName;

	/** @var bool */
	public $optional;

	/** @var TypeNode */
	public $valueType;

	/**
	 * @param ConstExprStringNode|IdentifierTypeNode $keyName
	 */
	public function __construct($keyName, bool $optional, TypeNode $valueType)
	{
		$this->keyName = $keyName;
		$this->optional = $optional;
		$this->valueType = $valueType;
	}


	public function __toString(): string
	{
		if ($this->keyName !== null) {
			return sprintf(
				'%s%s: %s',
				(string) $this->keyName,
				$this->optional ? '?' : '',
				(string) $this->valueType
			);
		}

		return (string) $this->valueType;
	}

}
