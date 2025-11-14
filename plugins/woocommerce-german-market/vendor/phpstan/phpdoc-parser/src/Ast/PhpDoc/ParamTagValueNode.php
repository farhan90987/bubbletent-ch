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

class ParamTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	/** @var TypeNode */
	public $type;

	/** @var bool */
	public $isReference;

	/** @var bool */
	public $isVariadic;

	/** @var string */
	public $parameterName;

	/** @var string (may be empty) */
	public $description;

	public function __construct(TypeNode $type, bool $isVariadic, string $parameterName, string $description, bool $isReference = false)
	{
		$this->type = $type;
		$this->isReference = $isReference;
		$this->isVariadic = $isVariadic;
		$this->parameterName = $parameterName;
		$this->description = $description;
	}


	public function __toString(): string
	{
		$reference = $this->isReference ? '&' : '';
		$variadic = $this->isVariadic ? '...' : '';
		return trim("{$this->type} {$reference}{$variadic}{$this->parameterName} {$this->description}");
	}

}
