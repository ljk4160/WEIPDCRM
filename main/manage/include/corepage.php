<?php
	/*
		This file is part of WEIPDCRM.
	
	    WEIPDCRM is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.
	
	    WEIPDCRM is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	/* DCRM List Footer Control */
	
	if (!defined("DCRM")) {
		exit();
	}
	
	class Core_Lib_Page
	{
		public $first_row;
		public $list_rows;
		protected $total_pages;
		protected $total_rows;
		protected $now_page;
		protected $method  = 'defalut';
		protected $parameter = '';
		protected $page_name;
		protected $ajax_func_name;
		public $plus = 3;
		protected $url;
		
		/**
		 * 构造函数
		 * @param unknown_type $data
		 */
		public function __construct($data = array())
		{
			$this->total_rows = $data['total_rows'];
	
			$this->parameter = !empty($data['parameter']) ? $data['parameter'] : '';
			$this->list_rows = !empty($data['list_rows']) && $data['list_rows'] <= 100 ? $data['list_rows'] : 15;
			$this->total_pages = ceil($this->total_rows / $this->list_rows);
			$this->page_name = !empty($data['page_name']) ? $data['page_name'] : 'p';
			$this->ajax_func_name = !empty($data['ajax_func_name']) ? $data['ajax_func_name'] : '';
			
			$this->method = !empty($data['method']) ? $data['method'] : '';
			
			/* 当前页面 */
			if(!empty($data['now_page']))
			{
				$this->now_page = intval($data['now_page']);
			}else{
				$this->now_page   = !empty($_GET[$this->page_name]) ? intval($_GET[$this->page_name]):1;
			}
			$this->now_page   = $this->now_page <= 0 ? 1 : $this->now_page;
			
			if(!empty($this->total_pages) && $this->now_page > $this->total_pages)
			{
				$this->now_page = $this->total_pages;
			}
			$this->first_row = $this->list_rows * ($this->now_page - 1);
		}
		
		/**
		 * 得到当前连接
		 * @param $page
		 * @param $text
		 * @return string
		 */
		protected function _get_link($page,$text)
		{
			switch ($this->method) {
				case 'ajax':
					$parameter = '';
					if($this->parameter)
					{
						$parameter = ','.$this->parameter;
					}
					return '<a onclick="' . $this->ajax_func_name . '(\'' . $page . '\''.$parameter.')" href="javascript:void(0)">' . $text . '</a>';
				break;
				
				case 'html':
					$url = str_replace('%page', $page,$this->parameter);
					return '<a href="' .$url . '">' . $text . '</a>';
				break;
				
				default:
					return '<a href="' . $this->_get_url($page) . '">' . $text . '</a>';
				break;
			}
		}
		
		/**
		 * 设置当前页面链接
		 */
		protected function _set_url()
		{
			$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
			$parse = parse_url($url);
			if(isset($parse['query'])) {
				parse_str($parse['query'],$params);
				unset($params[$this->page_name]);
				$url   =  $parse['path'].'?'.http_build_query($params);
			}
			if(!empty($params))
			{
				$url .= '&';
			}
			$this->url = $url;
		}
		
		/**
		 * 得到$page的url
		 * @param $page 页面
		 * @return string
		 */
		protected function _get_url($page)
		{
			if($this->url === NULL)
			{
				$this->_set_url();
			}
	  	//	$lable = strpos('&', $this->url) === FALSE ? '' : '&';
			return $this->url . $this->page_name . '=' . $page;
		}
		
		
		/**
		 * 得到第一页
		 * @return string
		 */
		public function first_page($name = '第一页')
		{
			if($this->now_page > 5)
			{
				return $this->_get_link('1', $name);
			}
			return '';
		}
		
		/**
		 * 最后一页
		 * @param $name
		 * @return string
		 */
		public function last_page($name = '最后一页')
		{
			if($this->now_page < $this->total_pages - 5)
			{
				return $this->_get_link($this->total_pages, $name);
			}
			return '';
		}  
		
		/**
		 * 上一页
		 * @return string
		 */
		public function up_page($name = '上一页')
		{
			if($this->now_page != 1)
			{
				return $this->_get_link($this->now_page - 1, $name);
			}
			return '';
		}
		
		/**
		 * 下一页
		 * @return string
		 */
		public function down_page($name = '下一页')
		{
			if($this->now_page < $this->total_pages)
			{
				return $this->_get_link($this->now_page + 1, $name);
			}
			return '';
		}
	
		/**
		 * 分页样式输出
		 * @param $param
		 * @return string
		 */
		public function show($param = 1)
		{
			if($this->total_rows < 1)
			{
				return '';
			}
			
			$className = 'show_' . $param;
			
			$classNames = get_class_methods($this);
	
			if(in_array($className, $classNames))
			{
				return $this->$className();
			}
			return '';
		}
		
		protected function show_2()
		{
			if($this->total_pages != 1)
			{
				$return = '';
				// $return .= $this->first_page('<<');
				$return .= $this->up_page('<');
				for($i = 1;$i<=$this->total_pages;$i++)
				{
					if($i == $this->now_page)
					{
						$return .= "<a class='now_page'>$i</a>";
					}
					else
					{
						if($this->now_page-$i>=8 && $i != 1)
						{
							$return .="<span class='pageMore'>...</span>";
							$i = $this->now_page-7;
						}
						else
						{
							if($i >= $this->now_page+9 && $i != $this->total_pages)
							{
								$return .="<span>...</span>"; 
								$i = $this->total_pages;
							}
							$return .= $this->_get_link($i, $i);
						}
					}
				}
				$return .= $this->down_page('>');
				// $return .= $this->last_page('>>');
				return $return;
			}
		}
		
		protected function show_1()
		{
			$plus = $this->plus;
			if( $plus + $this->now_page > $this->total_pages)
			{
				$begin = $this->total_pages - $plus * 2;
			}else{
				$begin = $this->now_page - $plus;
			}
			
			$begin = ($begin >= 1) ? $begin : 1;
			$return = '';
			$return .= $this->first_page();
			$return .= $this->up_page();
			for ($i = $begin; $i <= $begin + $plus * 2;$i++)
			{
				if($i>$this->total_pages)
				{
					break;
				}
				if($i == $this->now_page)
				{
					$return .= "<a class='now_page'>$i</a>";
				}
				else
				{
					$return .= $this->_get_link($i, $i);
				}
			}
			$return .= $this->down_page();
			$return .= $this->last_page();
			return $return;
		}
		
		protected function show_3()
		{
			$plus = $this->plus;
			if( $plus + $this->now_page > $this->total_pages)
			{
				$begin = $this->total_pages - $plus * 2;
			}
			else {
				$begin = $this->now_page - $plus;
			}
			$begin = ($begin >= 1) ? $begin : 1;
			$return = '总计 ' .$this->total_rows. ' 个记录分为 ' .$this->total_pages. ' 页, 当前第 ' . $this->now_page . ' 页 ';
			$return .= '，每页 ';
			$return .= '<input type="text" value="'.$this->list_rows.'" id="pageSize" size="3"> ';
			$return .= $this->first_page();
			$return .= $this->up_page(); 
			$return .= $this->down_page();
			$return .= $this->last_page();
			$return .= '<select onchange="'.$this->ajax_func_name.'(this.value)" id="gotoPage">';
		   
			for ($i = $begin;$i<=$begin+10;$i++)
			{
				if($i>$this->total_pages)
				{
					break;
				}
				if($i == $this->now_page)
				{
					$return .= '<option selected="true" value="'.$i.'">'.$i.'</option>';
				}
				else
				{
					$return .= '<option value="' .$i. '">' .$i. '</option>';
				}
			}
			$return .= '</select>';
			return $return;
		}
	}
	
?>