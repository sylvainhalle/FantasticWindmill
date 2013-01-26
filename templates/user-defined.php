<?php
// User-defined function to display the inner HTML of some DOM node
// as a string
function get_inner_html( $node ) 
{
  $innerHTML= '';
  $children = $node->childNodes;
  foreach ($children as $child)
  {
    $innerHTML .= $child->ownerDocument->saveXML( $child );
  }
  return $innerHTML;
}

// User-defined array that maps language codes to language names
$langnames = array("fr" => "Lire en français", "en" => "Read in English");
?>
