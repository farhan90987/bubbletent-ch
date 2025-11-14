<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */ declare(strict_types = 1);

namespace MarketPress\German_Market\PHPStan\PhpDocParser\Ast\PhpDoc;

use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\NodeAttributes;
use MarketPress\German_Market\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use function trim;

class TypeAliasImportTagValueNode implements PhpDocTagValueNode
{

	use NodeAttributes;

	/** @var string */
	public $importedAlias;

	/** @var IdentifierTypeNode */
	public $importedFrom;

	/** @var string|null */
	public $importedAs;

	public function __construct(string $importedAlias, IdentifierTypeNode $importedFrom, ?string $importedAs)
	{
		$this->importedAlias = $importedAlias;
		$this->importedFrom = $importedFrom;
		$this->importedAs = $importedAs;
	}

	public function __toString(): string
	{
		return trim(
			"{$this->importedAlias} from {$this->importedFrom}"
			. ($this->importedAs !== null ? " as {$this->importedAs}" : '')
		);
	}

}
