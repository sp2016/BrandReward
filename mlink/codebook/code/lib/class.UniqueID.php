<?php
class UniqueID {
	function get_uuid($prefix,$more_entropy=false)
	{
		return uniqid($prefix,$more_entropy);
	}
}
?>