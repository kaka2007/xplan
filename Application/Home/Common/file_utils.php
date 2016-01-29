<?php
	function writeOneLine($filename,$content){
		file_put_contents($filename, $content."\n", FILE_APPEND);
	}

?>