<?php 
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

class MixColor extends AbstractHelper
{

	/**
	 * Mix two hex colors programmatically.
	 *
	 * @param string $color1 The first hex color.
	 * @param string $color2 The second hex color.
	 * @param float $percent The mix percent.
	 * @return string
	 */
	public function __invoke($color1, $color2, $percent)
	{
		$percent = min(max($percent, 0), 100); // Clamp percent value between 0 and 100

		$num1 = base_convert(substr($color1, 1), 16, 10);
		$num2 = base_convert(substr($color2, 1), 16, 10);
		$coef1 = $percent / 100.0;
		$coef2 = 1.0 - $coef1;

		$r = round( (($num1 >> 16) * $coef1) + ( ($num2 >> 16) * $coef2));
		$b = round( (($num1 >> 8 & 0x00ff) * $coef1) + ( ($num2 >> 8 & 0x00ff) * $coef2));
		$g = round( (($num1 & 0x0000ff) * $coef1) + ( ($num2 & 0x0000ff) * $coef2));

		return '#'.substr(base_convert(0x1000000 + ($r<255?$r<1?0:$r:255)*0x10000 + ($b<255?$b<1?0:$b:255)*0x100 + ($g<255?$g<1?0:$g:255), 10, 16), 1);
	}
}
