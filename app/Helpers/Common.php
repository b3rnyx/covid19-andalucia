<?php

function htmlInfo($code, $title, $style='main', $legend='')
{

	return '<div class="' . $style . ' data-switch switch-' . implode(' switch-', config('custom.stats-items.' . $code . '.allowed')) . '" data-item="' . $code . '">
						<label>' . $title . '</label>
						<i></i> <strong></strong>
						' . ($legend != '' ? '<span>' . $legend . '</span>' : '') . '
					</div>';

}