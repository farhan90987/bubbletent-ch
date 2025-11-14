<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function trim;

class TypelessParamTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	/** @var bool */
	public $isReference;

	/** @var bool */
	public $isVariadic;

	/** @var string */
	public $parameterName;

	/** @var string (may be empty) */
	public $description;

	public function __construct(bool $isVariadic, string $parameterName, string $description, bool $isReference = false)
	{
		$this->isReference = $isReference;
		$this->isVariadic = $isVariadic;
		$this->parameterName = $parameterName;
		$this->description = $description;
	}


	public function __toString(): string
	{
		$reference = $this->isReference ? '&' : '';
		$variadic = $this->isVariadic ? '...' : '';
		return trim("{$reference}{$variadic}{$this->parameterName} {$this->description}");
	}

}
