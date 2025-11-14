<?php
/**
 * @package dompdf
 * @link    https://github.com/dompdf/dompdf
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */
namespace MarketPress\German_Market\Dompdf\Positioner;

use MarketPress\German_Market\Dompdf\FrameDecorator\AbstractFrameDecorator;

/**
 * Positions block frames
 *
 * @package dompdf
 */
class Block extends AbstractPositioner
{

    function position(AbstractFrameDecorator $frame): void
    {
        $style = $frame->get_style();
        $cb = $frame->get_containing_block();
        $p = $frame->find_block_parent();

        if ($p) {
            $float = $style->float;

            if (!$float || $float === "none") {
                $p->add_line(true);
            }
            $y = $p->get_current_line_box()->y;
        } else {
            $y = $cb["y"];
        }

        $x = $cb["x"];

        $frame->set_position($x, $y);
    }
}
