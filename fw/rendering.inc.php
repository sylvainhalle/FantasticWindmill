<?php
/**************************************************************************
    Fantastic Windmill
    Copyright (C) 2013-2016  Sylvain Hallé
    
    A simple static web site generator for PHP programmers.
    
    Author:  Sylvain Hallé
    
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

class Rendering
{
  private $m_outputDir = "public_html";
  private $m_contentDir = "content";
  private $m_staticDir = "static";
  private $m_templateDir = "templates";
  private $m_yamlEnabled = true;
  
  public function getContentDir()
  {
    return to_slashes($this->m_contentDir);
  }
  
  public function getOutputDir()
  {
    return to_slashes($this->m_outputDir);
  }
  
  public function getStaticDir()
  {
    return to_slashes($this->m_staticDir);
  }
  
  public function getTemplateDir()
  {
    return to_slashes($this->m_templateDir);
  }
  
  public function getYaml()
  {
    return $this->m_yamlEnabled;
  }
  
  public function setOutputDir($s)
  {
    $this->m_outputDir = $s;
  }
  
  public function setContentDir($s)
  {
    $this->m_contentDir = $s;
  }
  
  public function setStaticDir($s)
  {
    $this->m_staticDir = $s;
  }
  
  public function setTemplateDir($s)
  {
    $this->m_templateDir = $s;
  }
  
  public function setYaml($s)
  {
    $this->m_yamlEnabled = $s;
  }

}

?>
