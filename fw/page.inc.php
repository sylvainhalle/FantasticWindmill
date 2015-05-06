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

class Page
{
  public $dom = null;
  private $m_outputFilename = "";
  public $data = array();
  private $m_included_files = array();
  public $is_html5 = true;
  
  /**
   * Return codes for method mustRegenerate
   */
  public static $DONT_REGENERATE = 0;
  public static $FILE_DOES_NOT_EXIST = 1;
  public static $FILE_MODIFIED = 2;
  
  public function parse($html)
  {
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
    //if ($this->is_html5 === true)
    {
      $this->dom = HTML5_Parser::parse($html);
    }
    /*else
    {
      $this->dom = new DOMDocument('1.0', 'UTF-8');
      $this->dom->loadHTML($html);
    }*/
    $this->dom->formatOutput = true;
  }
  
  public function getHtmlContents()
  {
    return $this->dom->saveHTML();
  }
  
  public function getUrl($from = "")
  {
    $from_nofile = trim_filename($from);
    $my_path = trim_filename($this->m_outputFilename);
    $my_filename = get_filename($this->m_outputFilename);
    $relative = relative_path($my_path, $from_nofile).$my_filename;
    return $relative;
  }
  
  public function setOutputFilename($s)
  {
    $this->m_outputFilename = $s;
  }
  
  public function getOutputFilename()
  {
    return $this->m_outputFilename;
  }
  
  public function mergeYaml($s)
  {
    $this->data = array_merge($this->data, $s);
  }
  
  public function addIncludedFile($f)
  {
    $this->m_included_files[] = $f;
  }
  
  public function addIncludedFiles($a)
  {
    $this->m_included_files = array_merge($this->m_included_files, $a);
  }
  
  /**
   * Checks if the target output page is older than any file required
   * to build it. This includes all .php files included when rendering
   * the page, plus the markdown input file used for the rendering, plus
   * any YAML file along the path.
   */
  public function mustRegenerate()
  {
    // If target file does not exist, obviously we must regenerate the page
    if (!file_exists($this->m_outputFilename))
    {
      return Page::$FILE_DOES_NOT_EXIST;
    }
    $target_time = filemtime($this->m_outputFilename);
    foreach ($this->m_included_files as $filename)
    {
      $source_time = filemtime($filename);
      if ($source_time > $target_time)
      {
        return Page::$FILE_MODIFIED;
      }
    }
    return Page::$DONT_REGENERATE;
  }
  
  function recurseYaml($start_dir, $end_dir, $filename)
  {
    if ($start_dir[strlen($start_dir) - 1] !== "/")
      $start_dir .= "/";
    if ($end_dir[strlen($end_dir) - 1] !== "/")
      $end_dir .= "/";
    $end_dir = substr($end_dir, strlen($start_dir), strlen($end_dir) - strlen($start_dir));
    $dir_parts = explode("/", $end_dir);
    $cur_dir = $start_dir;
    $out_yaml = array();
    foreach ($dir_parts as $dir)
    {
      $cur_filename = $cur_dir.$filename;
      if (file_exists($cur_filename))
      {
        $yaml_contents = file_get_contents($cur_filename);
        if ($parsed_yaml = yaml_parse($yaml_contents))
        {
          $out_yaml = array_merge($out_yaml, $parsed_yaml);
        }
        else
          show_error("WARNING: Error parsing YAML in $cur_filename");
        $this->addIncludedFile($cur_filename);
      }
      $cur_dir .= $dir."/";
    }
    $this->mergeYaml($out_yaml);    
  }
}

?>
