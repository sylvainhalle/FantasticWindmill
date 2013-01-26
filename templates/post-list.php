<?php

include("header.php"); 
include("insert-translations.php");

// Classify posts by slug and language
$posts = array();
foreach ($pages as $p)
{
  if (!isset($p->data["template"]) || $p->data["template"] !== "post.php")
    continue;
  $lang = $p->data["lang"];
  $slug = $p->data["slug"];
  $posts[$slug][$lang] = $p;
}

// Produce list and 
$date_posts = array();
foreach ($posts as $slug => $langs)
{
  if (isset($langs[$page->data["lang"]]))
    $p = $langs[$page->data["lang"]];
  elseif (isset($langs[$site->getLanguage()]))
    $p = $langs[$site->getLanguage()];
  else foreach($langs as $p) break;
  $item = "";
  $item .= "<li><a href=\"".$p->getUrl($page->getOutputFilename())."\">".$p->data["title"]."</a>";
  $item .= "\n<br />\n";
  if (isset($p->data["author"]))
    $item .= $p->data["author"]." ";
  $item .= date("Y-m-d H:i:s", $p->data["date"]);
  if (isset($p->data["abstract"]))
    $item .= "<br />\n".$p->data["abstract"];
  $item .= "</li>\n";
  $date_posts[$p->data["date"]][] = $item;
}

// Display in reverse order of date
krsort($date_posts);
echo "<ol id=\"post-list\">\n";
foreach($date_posts as $date => $items)
{
  foreach ($items as $item)
    echo $item;
}
echo "</ol>\n";

include("footer.php");
?>
