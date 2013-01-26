<?php
/**************************************************************************
    Fantastic Windmill
    Copyright (C) 2013  Sylvain Hallé
    
    A simple static web site generator for PHP programmers.
    
    Author:  Sylvain Hallé
    Date:    2013-01-23
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

function spit($message = "", $verbosity = 0)
{
  global $fw_params;
  if ($fw_params["verbosity"] > $verbosity)
    echo $message;
}

function spitln($message = "", $verbosity = 0)
{
  spit($message."\n", $verbosity);
}

function show_help($message = "Wrong arguments.")
{
  $fh = fopen("php://stderr", "w");
  fputs($fh, "\n$message\nUse slidecrunch --help for usage info\n\n");
  fclose($fh);
}

function show_error($message = "Undefined error (?!?)")
{
  $fh = fopen("php://stderr", "w");
  fputs($fh, "\n$message\n\n");
  fclose($fh);
}

function show_help_long()
{
  global $usage_string;
  $fh = fopen("php://stderr", "w");
  fputs($fh, $usage_string);
  fclose($fh);
}

/**
 * Recursively scans a directory to get the list of all files
 * @param $dir The (absolute) path to list
 */
function scandir_recursive($dir)
{
  $out = array();
  $files = scandir($dir);
  foreach ($files as $file)
  {
    if (is_dir($dir.DIRECTORY_SEPARATOR.$file) && $file !== "." && $file !== "..")
    {
      $out_rec = scandir_recursive($dir.DIRECTORY_SEPARATOR.$file);
      foreach ($out_rec as $entry)
        $out[] = $entry;
    }
    else
    {
      if ($file !== "." && $file !== "..")
        $out[] = $dir.DIRECTORY_SEPARATOR.$file;
    }
  }
  return $out;
}

/*Create  Directory Tree if Not Exists
If you are passing a path with a filename on the end, pass true as the second parameter to snip it off */
function make_path($pathname, $is_filename=false)
{
    if($is_filename){
        $pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
    }
    // Check if directory already exists
    if (is_dir($pathname) || empty($pathname)) {
        return true;
    }
    // Ensure a file does not already exist with the same name
    $pathname = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pathname);
    if (is_file($pathname)) {
        trigger_error('mkdirr() File exists', E_USER_WARNING);
        return false;
    }
    // Crawl up the directory tree
    $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
    if (make_path($next_pathname, false)) {
        if (!file_exists($pathname)) {
            return mkdir($pathname);
        }
    }
    return false;
}

/**
 * Computes the relative path from p2 to p1 --that is,
 * what one needs to type in p2 to get to p1.
 * @param $p1, $p2 The two paths, filename excluded
 */
function relative_path($p1, $p2)
{
  if ($p1[strlen($p1) - 1] !== "/")
    $p1 .= "/";
  if ($p2[strlen($p2) - 1] !== "/")
    $p2 .= "/";
  for ($i = 0; $i < min(strlen($p1), strlen($p2)); $i++)
  {
    if ($p1[$i] !== $p2[$i])
      break;
  }
  /*if (strlen($p1) <= strlen($p2))
  {
    $rel_path = substr($p2, $i);
    $rel_path_dest = substr($p1, $i);
    $occurrences = preg_match_all("/\//", $rel_path_dest, $matches);
    $out_path = "";
    for ($i = 0; $i < $occurrences; $i++)
      $out_path .= "../";
    $out_path .= $rel_path;
  }
  else*/
  {
    $rel_path = substr($p1, $i);
    $rel_path_dest = substr($p2, $i);
    $occurrences = preg_match_all("/\//", $rel_path_dest, $matches);
    $out_path = "";
    for ($i = 0; $i < $occurrences; $i++)
      $out_path .= "../";
    $out_path .= $rel_path;
  }
  return $out_path;
}

function get_filename($s)
{
  if ($pos = strrpos($s, "/"))
  {
    return substr($s, $pos + 1);
  }
  else
    return $s;
}

function trim_filename($s)
{
  return substr($s, 0, strlen($s) - strlen(get_filename($s)) - 1);
}

function rebase_urls($nodelist, $attribute, $rendering, $page)
{
  for ($i = 0; $i < $nodelist->length; $i++)
  {
    $element = $nodelist->item($i);
    if (!$element->hasAttributes())
      continue;
    $href = $element->attributes->getNamedItem($attribute);
    if ($href === null)
      continue;
    $url = trim($href->nodeValue);
    if (substr($url, 0, 1) !== "/") // Absolute URL
      continue;
    $url = substr($url, 1);
    $relative_to_root = relative_path($rendering->getOutputDir(), trim_filename($page->getOutputFilename()));
    $element->setAttribute($attribute, $relative_to_root.$url);
  }
}

function clean_urls($nodelist, $attribute)
{
  for ($i = 0; $i < $nodelist->length; $i++)
  {
    $element = $nodelist->item($i);
    if (!$element->hasAttributes())
      continue;
    $href = $element->attributes->getNamedItem($attribute);
    if ($href === null)
      continue;
    $url = trim($href->nodeValue);
    // If clean URLs, remove extension from local HTML links
    if (strlen($url) < 5 || substr($url, 0, 5) !== "http:")
    {
      // Local link: trim extension
      if (substr($url, strlen($url) - 5, 5) === ".html")
      {
        $url = substr($url, 0, strlen($url) - 5);
        $element->setAttribute("href", $url);
      }
    }
  }
}

function turn_blockquote_into_abstract(&$page)
{
  $dom = $page->dom;
  $body = $dom->getElementsByTagName("body")->item(0);
  foreach ($body->childNodes as $childnode)
  {
    if ($childnode->nodeType == XML_ELEMENT_NODE && $childnode->nodeName !== "h1" && $childnode->nodeName !== "blockquote")
      return;
    if ($childnode->nodeName === "blockquote")
    {
      $abstract = $childnode->textContent;
      $page->data["abstract"] = $abstract;
      $body->removeChild($childnode);
      return;
    }
  }
}

?>
