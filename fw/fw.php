<?php
/**************************************************************************
    Fantastic Windmill
    Copyright (C) 2013  Sylvain Hallé
    
    A simple static web site generator for PHP programmers.
    
    Author:  Sylvain Hallé
    Date:    2013-01-26
    
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

// Version string (used for version tracking)
define("VERSION_STRING", "1.0.3");
$HELLO_MSG = "Fantastic Windmill v".
  VERSION_STRING." - A static web site generator for PHP programmers\n".
  "(C) 2013 Sylvain Hallé, Université du Québec à Chicoutimi";

$usage_string = <<<EOD

$HELLO_MSG
Usage: php fw.php [--help] [options]

Options:
  --clean-urls    Removes html extension in local links
  --verbosity x   Sets verbosity level to x
  --incremental   Don't save pages whose input files have not been modified

EOD;
/*----------------------*/

// Includes
require_once("fw/markdown.php");
require_once("fw/rendering.inc.php");
require_once("fw/site.inc.php");
require_once("fw/page.inc.php");
require_once("fw/utils.php");
require_once("fw/html5lib/Parser.php");

// Option defaults {{{
$fw_params = array();
$fw_params["verbosity"] = 1;
$fw_params["clean-urls"] = 0;
$fw_params["incremental"] = false;
// }}}

// Parse command line options {{{
$to_set = "";
if (count($argv) < 1)
{
  show_help();
  exit(1);
}
if (count($argv) > 1)
{
  for ($i = 1, $value = $argv[$i]; $i < count($argv) && $value = $argv[$i]; $i++)
  {
    if ($value === "--help" || $value === "-h")
    {
      show_help_long();
      exit(1);
    }
    elseif ($value === "--clean-urls")
    {
      $fw_params["clean-urls"] = true;
    }
    elseif ($value === "--incremental")
    {
      $fw_params["incremental"] = true;
    }
    elseif ($value === "--verbosity")
    {
      $to_set = "verbosity";
    }
    else
    {
      $fw_params[$to_set] = $value;
      $to_set = "";
    }
  }
}
// }}}

// Initialization of the instances
$rendering = new Rendering();
$site = new Site();
$pages = array();

// Retrieving of the configuration file. This must be done after the
// class definitions, as the config file uses those classes.
if (file_exists("config.inc.php"))
{
  include("config.inc.php");
}

/* Main loop */

// Show credits ;-)
spitln("\n$HELLO_MSG\n", -1);

// Check if YAML module is installed; otherwise deactivate YAML processing
if (!function_exists("yaml_parse"))
{
  $rendering->setYaml(false);
  show_error("WARNING: the YAML PHP module is not installed. ".
    "Support for YAML is disabled.");
}

// List all pages
$file_list = scandir_recursive($rendering->getContentDir());

// First pass: read each page and create page objects
foreach ($file_list as $file)
{
  // Is it a Markdown file?
  $ext = pathinfo($file, PATHINFO_EXTENSION);
  if ($ext != "md" && $ext != "rst")
    continue;
  spitln("Processing $file", 1);
  $page = new Page();
  $page->addIncludedFile($file);
  $contents = file_get_contents($file);
  
  // Recursively read _.yaml in all directories from the root of the
  // content directory to the directory of the page to process (if they
  // exist)
  $path_parts = pathinfo($file);
  $current_directory = $path_parts["dirname"];
  if ($rendering->getYaml())
  {
    $page->recurseYaml($rendering->getContentDir(), 
      $current_directory, "_.yaml");
  }
  
  
  // Read a YAML file with same name as page to process (if exists)
  $yaml_filename = substr($file, 0, strlen($file) - strlen($ext))."yaml";
  if (file_exists($yaml_filename) && $rendering->getYaml())
  {
    $yaml_contents = file_get_contents($yaml_filename);
    if ($parsed_yaml = yaml_parse($yaml_contents))
      $page->mergeYaml($parsed_yaml);
    else
      show_error("WARNING: Error parsing YAML in $yaml_filename");
    $page->addIncludedFile($yaml_filename);
  }
  
  // Is there a YAML declaration at the end of the file?
  preg_match("/^---\n.*$/ms", $contents, $matches, PREG_OFFSET_CAPTURE);
  if (count($matches) > 0)
  {
    $yaml_contents = $matches[0][0];
    if ($parsed_yaml = yaml_parse($yaml_contents))
      $page->mergeYaml($parsed_yaml);
    else
      show_error("WARNING: Error parsing YAML at the end of $file");
    $contents = substr($contents, 0, $matches[0][1]); // Trim YAML from file before rendering
  }
  
  // Get basic metadata from file contents
  if ($ext == "md")
    $html_contents = Markdown($contents);
  else {
    $tmp_rst_file = "tmp_rst_file.rst";
    file_put_contents($tmp_rst_file, $contents);
    $html_contents = shell_exec("rst2html.py --syntax-highlight=short $tmp_rst_file");
    unlink("tmp_rst_file.rst");
  }
  @$page->parse($html_contents);
  $nodelist = $page->dom->getElementsByTagName("h1");
  $heading1 = $nodelist->item(0);
  if (!isset($page->data["title"]))
    $page->data["title"] = $heading1->nodeValue;
  if (!isset($page->data["abstract"]))
    turn_blockquote_into_abstract($page);
  if (!isset($page->data["date"]))
    $page->data["date"] = filemtime($file);
  
  // Process date if not given as a Unix timestamp
  if (isset($page->data["date"]) && !is_numeric($page->data["date"]))
  {
    $page->data["date"] = strtotime($page->data["date"]);
  }
  
  // Target path mirrors relative location in content folder
  $base_path = substr($file, strlen($rendering->getContentDir()), 
    strlen($file) - strlen($rendering->getContentDir()));
  // Target filename is html instead of md
  $base_path = substr($base_path, 0, strlen($base_path) - strlen($ext))."html";
  $output_filename = $rendering->getOutputDir().$base_path;
  $page->setOutputFilename($output_filename);
  
  // Add the page to the array of pages
  $pages[] = $page;
}

// Get the list of files included by Fantastic Windmill by default
$base_included_files = get_included_files();

// Second pass: render each page
foreach ($pages as $page)
{
  // Select proper template
  $template_file = $rendering->getTemplateDir().
    DIRECTORY_SEPARATOR;
  if (isset($page->data["template"]))
    $template_file .= $page->data["template"];
  else
    $template_file .= "base.php";
  
  // Render the template with the variables populated above
  ob_start();
  include($template_file);
  $html_page = ob_get_clean();
  // Get the list of files included after rendering that page
  $page_included_files = get_included_files();
  $page_included_files = array_diff($page_included_files, $base_included_files);
  $page->addIncludedFiles($page_included_files);
  file_put_contents("/tmp/sample-page.html", $html_page);
  @$page->parse($html_page);
  
  // Rebase all absolute page URLs with respect to the document root
  $nodelist = $page->dom->getElementsByTagName("a");
  rebase_urls($nodelist, "href", $rendering, $page);
  $nodelist = $page->dom->getElementsByTagName("img");
  rebase_urls($nodelist, "src", $rendering, $page);
  $nodelist = $page->dom->getElementsByTagName("link");
  rebase_urls($nodelist, "href", $rendering, $page);
}

// Third pass: clean URLs
if ($fw_params["clean-urls"] == true)
{
  spitln("Cleaning URLs");
  foreach ($pages as $page)
  {
    $nodelist = $page->dom->getElementsByTagName("a");
    clean_urls($nodelist, "href");
  }
}

// Fourth pass: write files
foreach ($pages as $page)
{
  $contents = "";
  $output_filename = $page->getOutputFilename();
  $regenerate = $page->mustRegenerate();
  if ($fw_params["incremental"] == true && $regenerate === Page::$DONT_REGENERATE)
  {
    // Don't save files that have not been modified. This will keep
    // the target file's current timestamp and allow tools like lftp and
    // rsync to skip uploading/copying them
    spitln("Skip writing to $output_filename: page not modified", 1);
    continue;
  }
  else
  {
    spit("Writing to $output_filename:", 1);
    if ($regenerate === Page::$FILE_MODIFIED)
      spitln(" input file more recent than target file", 1);
    else
      spitln(" target file does not exist", 1);
  }
  make_path($output_filename, true);
  $contents = $page->dom->saveHTML();
  if (!$page->is_html5)
  {
    // TODO: this is a hack. For some reason, the DOM Document writes the
    // xmlns header twice; this removes the HTML validation error.
    
    $contents = str_replace("xmlns=\"http://www.w3.org/1999/xhtml\" xmlns=\"http://www.w3.org/1999/xhtml\"", "xmlns=\"http://www.w3.org/1999/xhtml\"", $contents);
    
    // TODO: another hack. DOMDocument puts all JavaScript inside a CDATA
    // that deactivates it. We manually remove the CDATA from the final page.
    $contents = preg_replace("/<script ([^>]*?)>[\n\s]*?<!\[CDATA\[(.*?)\]\]>[\n\s]*?<\/script>/ms", "<script $1>$2</script>", $contents);
  }
  else
  {
    // If the page was parsed as HTML5, other hacks are needed
    // The DOMDocument does not output the DOCTYPE
    $contents = "<!DOCTYPE HTML>\n".$contents;
    // html5lib escapes HTML inside <noscript>; we have to unescape it back
    if (preg_match("/<noscript>(.*)<\\/noscript>/ms", $contents, $matches))
    {
      $all = $matches[0];
      $inside = $matches[1];
      $inside = str_replace(array("&lt;", "&gt;"), array("<", ">"), $inside);
      $contents = str_replace($all, "<noscript>$inside</noscript>", $contents);
    }
  }
  file_put_contents($output_filename, $contents);
}

spitln("Done!\n");
exit(0);

/* :wrap=none:folding=explicit:maxLineLen=76: */
?>
