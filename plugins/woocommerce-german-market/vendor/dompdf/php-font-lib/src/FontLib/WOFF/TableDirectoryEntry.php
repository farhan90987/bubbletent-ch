<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\FontLib\WOFF;

use MarketPress\German_Market\FontLib\Table\DirectoryEntry;

/**
 * WOFF font file table directory entry.
 *
 * @package php-font-lib
 */
class TableDirectoryEntry extends DirectoryEntry {
  public $origLength;

  function __construct(File $font) {
    parent::__construct($font);
  }

  function parse() {
    parent::parse();

    $font             = $this->font;
    $this->offset     = $font->readUInt32();
    $this->length     = $font->readUInt32();
    $this->origLength = $font->readUInt32();
    $this->checksum   = $font->readUInt32();
  }
}
