<?php
/*
This piece of template manipulates the page's content as follows:
- Extract first <h1> element in the page
- Show that heading
- Show the list of translations for that page
- Show the rest of the page's content
*/

$body = $page->dom->getElementsByTagName("body")->item(0);
$heading1 = $page->dom->getElementsByTagName("h1")->item(0);

// Show page heading 1
if ($heading1)
{
  echo $heading1->C14N();
  $heading1->parentNode->removeChild($heading1);
}

// Get page translations
if (isset($page->data["slug"]))
{
  foreach ($pages as $p)
  {
    $lang = $p->data["lang"];
    if (isset($p->data["slug"]) && $page->data["slug"] === $p->data["slug"] && $page->data["lang"] !== $lang)
    {
      echo "<span class=\"langlink-$lang\"><a href=\"".$p->getUrl($page->getOutputFilename())."\">".$langnames[$lang]."</a></span>";
    }
  }
}

// Show rest of page
echo get_inner_html($body);
?>
