<?php include("header.php"); ?>

<?php
$body = $page->dom->getElementsByTagName("body")->item(0);
$heading1 = $page->dom->getElementsByTagName("h1")->item(0);

echo $heading1->C14N();
$heading1->parentNode->removeChild($heading1);

// Get translations of that page
foreach ($pages as $p)
{
  if (isset($p->data["slug"]) && $p->data["slug"] === $page->data["slug"] && $p->data["lang"] !== $page->data["lang"])
  {
    $lang = $p->data["lang"];
    echo "<span class=\"langlink-$lang\"><a href=\"".$p->getUrl($page->getOutputFilename())."\">".$langnames[$p->data["lang"]]."</a></span>\n";
  }
}

// Get author and date
switch($page->data["lang"])
{
case "fr":
  echo "<p>".date("Y-m-d", $page->data["date"])." par ".$page->data["author"]."</p>\n";
  break;
case "en":
default:
  echo "<p>".date("m/d/Y", $page->data["date"])." by ".$page->data["author"]."</p>\n";
  break;
}

echo get_inner_html($body);

include("footer.php");
?>
