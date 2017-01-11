<?php

	echo $code = file_get_contents('Templates/dev-tpl-eval.tpl.php');
	eval($code);

?>