<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Node;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;

class DoctrineAnnotation implements Node
{

	use NodeAttributes;

	/** @var string */
	public $name;

	/** @var list<DoctrineArgument> */
	public $arguments;

	/**
	 * @param list<DoctrineArgument> $arguments
	 */
	public function __construct(string $name, array $arguments)
	{
		$this->name = $name;
		$this->arguments = $arguments;
	}

	public function __toString(): string
	{
		$arguments = implode(', ', $this->arguments);
		return $this->name . '(' . $arguments . ')';
	}

}
