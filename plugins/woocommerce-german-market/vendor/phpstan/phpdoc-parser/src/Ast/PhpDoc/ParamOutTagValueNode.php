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

class ParamOutTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	/** @var TypeNode */
	public $type;

	/** @var string */
	public $parameterName;

	/** @var string (may be empty) */
	public $description;

	public function __construct(TypeNode $type, string $parameterName, string $description)
	{
		$this->type = $type;
		$this->parameterName = $parameterName;
		$this->description = $description;
	}

	public function __toString(): string
	{
		return trim("{$this->type} {$this->parameterName} {$this->description}");
	}

}
