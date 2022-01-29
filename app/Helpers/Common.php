<?php

function htmlInfo($codes, $title, $style='main', $legend='')
{

	$code = is_array($codes) ? $codes[0] : $codes;

	$out = '<div class="' . $style . ' data-switch switch-' . implode(' switch-', config('custom.stats-items.' . $code . '.allowed')) . '" data-item="' . $code . '">
						<label>
							' . $title . '
							' . (isset(config('custom.stats-items')[$code]['description']) ? '<i class="fa fa-question-circle tooltip-switch" title="' . config('custom.stats-items')[$code]['description'] . '"></i>' : '') . '
						</label>';
	if (is_array($codes)) {

		foreach ($codes as $c) {
			$out .= '<div class="num" data-item="' . $c . '"><span></span> <strong></strong></div>';	
		}

	} else {

		$out .= '<div class="num"><span></span> <strong></strong></div>';

	}
	
	$out .= ($legend != '' ? '<span class="legend">' . $legend . '</span>' : '')
					. '</div>';

	return $out;

}