<?php

function htmlInfo($code, $title, $style='main', $legend='')
{

	return '<div class="' . $style . ' data-switch switch-' . implode(' switch-', config('custom.stats-items.' . $code . '.allowed')) . '" data-item="' . $code . '">
						<label>
							' . $title . '
							' . (isset(config('custom.stats-items')[$code]['description']) ? '<i class="fa fa-question-circle" title="' . config('custom.stats-items')[$code]['description'] . '"></i>' : '') . '
						</label>
						<div class="num">
							<span></span> <strong></strong>
						</div>
						' . ($legend != '' ? '<span class="legend">' . $legend . '</span>' : '') . '
					</div>';

}