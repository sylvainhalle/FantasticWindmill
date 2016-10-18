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

class Site
{
  private $m_name = "";
  private $m_baseUrl = "";
  private $m_author = "";
  private $m_lang = "";
  private $m_cleanUrls = false;
  private $m_template = "base.php";
  
  public function getName()
  {
    return $this->m_name;
  }
  
  public function setName($s)
  {
    $this->m_name = $s;
  }
  
  public function getAuthor()
  {
    return $this->m_author;
  }
  
  public function setAuthor($s)
  {
    $this->m_author = $s;
  }
  
  public function getBaseUrl()
  {
    return to_slashes($this->m_baseUrl);
  }
  
  public function setBaseUrl($s)
  {
    $this->m_baseUrl = $s;
  }
  
  public function setLanguage($s)
  {
    $this->m_lang = $s;
  }
  
  public function getLanguage()
  {
    return $this->m_lang;
  }
  
  public function setCleanUrls($s)
  {
    $this->m_cleanUrls = $s;
  }
  
  public function getCleanUrls()
  {
    return $this->m_cleanUrls;
  }
  
  public function setTemplate($s)
  {
    $this->m_template = $s;
  }
  
  public function getTemplate()
  {
    return $this->m_template;
  }
  
}

?>
