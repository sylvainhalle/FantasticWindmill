<?php
// We first include a couple of user-defined functions that our template
// files will use
include_once("user-defined.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $page->data["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page->data["title"]; ?> - <?php echo $page->data["site"]["name"]; ?></title>
<link rel="stylesheet" type="text/css" media="screen" href="/screen.css" />
<meta name="author" content="<?php echo $page->data["site"]["author"]; ?>" />
</head>

<body>

<div id="maincontainer">

<div id="topsection"><div class="innertube"><?php echo $page->data["site"]["name"]; ?></div></div>

<div id="contentwrapper">
<div id="contentcolumn">
<div class="innertube">
