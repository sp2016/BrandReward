<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat clickarea for tracking modifier plugin
 *
 * Type:     modifier<br>
 * Name:     cat_clickarea<br>
 * Purpose:  urlencode url 
 * @link 
 * @author   JavionZheng
 * @param string
 * @param blockName
 * @param rank
 * @param clickarea
 * @return string
 */
function smarty_modifier_publish_tax($num,$tax=0)
{
    if($tax > 0 && $tax < 100){
        $num = $num * (100 - $tax) / 100 ;
        $num = number_format($num,2,'.','');
    }
    return $num;
}

?>
