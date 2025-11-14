<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use function trim;

class DoctrineTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	/** @var DoctrineAnnotation */
	public $annotation;

	/** @var string (may be empty) */
	public $description;


	public function __construct(
		DoctrineAnnotation $annotation,
		string $description
	)
	{
		$this->annotation = $annotation;
		$this->description = $description;
	}


	public function __toString(): string
	{
		return trim("{$this->annotation} {$this->description}");
	}

}
