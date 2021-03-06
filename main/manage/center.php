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
	
	/* DCRM Debian List */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/autofill.inc.php");
	require_once("include/func.php");
	require_once("include/corepage.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'center';
	
	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
		$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		if (!$con) {
			goto endlabel;
		}
		mysql_query("SET NAMES utf8");
		$select  = mysql_select_db(DCRM_CON_DATABASE);
		if (!$select) {
			$alert = mysql_error();
			goto endlabel;
		}

		require_once("header.php");

					if (!isset($_GET['action'])) {
						if (isset($_GET['search'])) {
?>
				<h2>管理软件包</h2>
				<br />
				<h3 class="navbar"><a href="center.php">所有软件包</a>　搜索软件包</h3>
					<br />
					<form class="form-horizontal" method="GET" action="center.php" >
					<div class="group-control">
						<label class="control-label">搜索内容</label>
						<div class="controls">
							<input type="hidden" name="action" value="search" />
							<input class="input-xlarge" name="contents" required="required" />
						</div>
					</div>
					<br />
					<div class="group-control">
						<label class="control-label">搜索类型</label>
						<div class="controls">
							<select name="type" >
							<option value="1" selected="selected">标识符</option>
							<option value="2">名称</option>
							<option value="3">作者</option>
							<option value="4">描述</option>
							<option value="5">提供者</option>
							<option value="6">保证人</option>
							<option value="7">分类</option>
							</select>
						</div>
					</div>
					<br />
					<div class="form-actions">
						<div class="controls">
							<button type="submit" class="btn btn-success">搜索</button>
						</div>
					</div>
					</form>
				<br />
<?php
						}
						else {
?>
				<h2>管理软件包</h2>
				<br />
				<h3 class="navbar">所有软件包　<a href="center.php?search=yes">搜索软件包</a></h3>
<?php
							if (isset($_GET['page'])) {
									$page = $_GET['page'];
							}
							elseif (isset($_SESSION['page'])) {
								$page = $_SESSION['page'];
							}
							else {
								$page = 1;
							}
							if ($page <= 0 OR $page >= 100) {
								$page = 1;
							}
							unset($_SESSION['contents']);
							unset($_SESSION['type']);
							$_SESSION['page'] = $page;
							$page_a = $page * 10 - 10;
							if ($page == 1) {
								$page_b = $page;
							}
							else {
								$page_b = $page - 1;
							}
							$list_query = mysql_query("SELECT `ID`, `Package`, `Name`, `Version`, `DownloadTimes`, `Stat`, `Size`, `Section` FROM `".DCRM_CON_PREFIX."Packages` ORDER BY `Stat` DESC, `ID` DESC, `Version` DESC, `Name` DESC LIMIT " . (string)$page_a. ",10");
							if ($list_query == FALSE) {
								goto endlabel;
							}
							else {
?>
								<table class="table"><thead><tr>
									<th style="width:13px;"></th>
									<th><ul class="ctl">名称</ul></th>
									<th style="width:20%;"><ul class="ctl">版本</ul></th>
									<th style="width:20%;"><ul class="ctl">尺寸</ul></th>
									<th style="width:10%;"><ul class="ctl">下载次数</ul></th>
									<th style="width:5%;"><ul class="ctl">删除</ul></th>
									<th style="width:5%;"><ul class="ctl">历史</ul></th>
								</tr></thead><tbody>
<?php
								$i = 0;
								while ($list = mysql_fetch_assoc($list_query)) {
									$i++;
?>
								<tr>
									<td height="20"><input type="radio" name="package" value="<?php echo $list['ID']; ?>" onclick="javascript:show(<?php echo $list['Stat']; ?>);" /></td>
<?php
									if (empty($list['Name'])) {
										$list['Name'] = AUTOFILL_NONAME;
									}
									if ($list['Stat'] == 1) {
?>
									<td><a href = "view.php?id=<?php echo $list['ID']; ?>"><ul class="ctl"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
<?php
									} elseif ($list['Stat'] == 2) {
?>
									<td><a href = "view.php?id=<?php echo $list['ID']; ?>"><ul class="ctl" style="color: green;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
<?php
									} else {
?>
									<td><a href = "view.php?id=<?php echo $list['ID']; ?>"><ul class="ctl" style="color: gray;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
<?php
									}
?>
									<td><ul class="ctl"><?php echo htmlspecialchars($list['Version']); ?></ul></td>
									<td><ul class="ctl"><?php echo sizeext($list['Size']); ?></ul></td>
									<td><ul class="ctl"><?php echo $list['DownloadTimes']; ?></ul></td>
									<td><a href="center.php?action=delete_confirm&name=<?php echo $list['Package']; ?>&id=<?php echo $list['ID']; ?>" class="close">&times;</a></td>
									<td><a href="center.php?action=search&contents=<?php echo $list['Package']; ?>&type=1" class="close">&raquo;</a></td>
								</tr>
<?php
								}
								if ($i < 10) {
									$page_c = $page;
								}
								else {
									$page_c = $page + 1;
								}
?>
								</tbody></table>
<?php
								$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages`");
								$info = mysql_fetch_row($q_info);
								$totalnum = (int)$info[0];
								$params = array('total_rows'=>$totalnum, 'method'=>'html', 'parameter' =>'center.php?page=%page', 'now_page'  =>$page, 'list_rows' =>10);
								$page = new Core_Lib_Page($params);
								echo '<div class="page">' . $page->show(2) . '</div>';
							}
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "search" AND !empty($_GET['contents']) AND !empty($_GET['type'])) {
						unset($_SESSION['page']);
						$_SESSION['contents'] = $_GET['contents'];
						$_SESSION['type'] = $_GET['type'];
						if (isset($_GET['page'])) {
								$page = $_GET['page'];
						}
						else {
							$page = 1;
						}
						if ($page <= 0 OR $page >= 100) {
							$page = 1;
						}
						$page_a = $page * 10 - 10;
						if ($page == 1) {
							$page_b = $page;
						}
						else {
							$page_b = $page - 1;
						}
?>
				<h2>管理软件包</h2>
				<br />
				<h3 class="navbar">搜索软件包：<?php echo $_GET['contents']; ?></h3>
<?php
						$search_type = (int)$_GET['type'];
						switch ($search_type) {
							case 1:
								$t = 'Package';
								break;
							case 2:
								$t = 'Name';
								break;
							case 3:
								$t = 'Author';
								break;
							case 4:
								$t = 'Description';
								break;
							case 5:
								$t = 'Maintainer';
								break;
							case 6:
								$t = 'Sponsor';
								break;
							case 7:
								$t = 'Section';
								break;
							default:
								goto endlabel;
						}
						$r_value = mysql_real_escape_string(str_replace('*', '%', str_replace('?', '_', $_GET['contents'])));
						$list_query = mysql_query("SELECT `ID`, `Package`, `Name`, `Version`, `DownloadTimes`, `Stat`, `Size` FROM `".DCRM_CON_PREFIX."Packages` WHERE `" . $t . "` LIKE '%" . $r_value . "%' ORDER BY `Stat` DESC, `ID` DESC LIMIT ".(string)$page_a.",10");
						if ($list_query == FALSE) {
							goto endlabel;
						}
						else {
?>
								<table class="table"><thead><tr>
									<th></th>
									<th><ul class="ctl">名称</ul></th>
									<th><ul class="ctl">版本</ul></th>
									<th><ul class="ctl">尺寸</ul></th>
									<th><ul class="ctl">下载次数</ul></th>
									<th><ul class="ctl">删除</ul></th>
									<th><ul class="ctl">历史</ul></th>
								</tr></thead><tbody>
<?php
								while ($list = mysql_fetch_assoc($list_query)) {
?>
								<tr>
									<td height="20"><input type="radio" name="package" value="<?php echo $list['ID']; ?>" onclick="javascript:show(<?php echo $list['Stat']; ?>);" /></td>
<?php
									if (empty($list['Name'])) {
										$list['Name'] = AUTOFILL_NONAME;
									}
									if ($list['Stat'] == 1) {
?>
									<td><a href = "view.php?id=<?php echo $list['ID']; ?>"><ul class="ctl" style="width:240px;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
<?php
									} elseif ($list['Stat'] == 2) {
?>
									<td><a href = "view.php?id=<?php echo $list['ID']; ?>"><ul class="ctl" style="width:240px; color: green;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
<?php
									} else {
?>
									<td><a href = "view.php?id=<?php echo $list['ID']; ?>"><ul class="ctl" style="width:240px; color: gray;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
<?php
									}
?>
									<td><ul class="ctl" style="width:80px;"><?php echo htmlspecialchars($list['Version']); ?></ul></td>
									<td><ul class="ctl" style="width:80px;"><?php echo sizeext($list['Size']); ?></ul></td>
									<td><ul class="ctl" style="width:50px;"><?php echo $list['DownloadTimes']; ?></ul></td>
									<td><a href="center.php?action=delete_confirm&name=<?php echo $list['Package']; ?>&id=<?php echo $list['ID']; ?>" class="close">&times;</a></td>
									<td><a href="center.php?action=search&contents=<?php echo $list['Package']; ?>&type=1" class="close">&raquo;</a></td>
								</tr>
<?php
								}
?>
								</tbody></table>
<?php
							$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `" . $t . "` LIKE '%" . $r_value . "%'");
							$info = mysql_fetch_row($q_info);
							$totalnum = (int)$info[0];
							$params = array('total_rows'=>$totalnum, 'method'=>'html', 'parameter' =>'center.php?action=search&contents='.$_GET['contents'].'&type='.$_GET['type'].'&page=%page', 'now_page'  =>$page, 'list_rows' =>10);
							$page = new Core_Lib_Page($params);
							echo '<div class="page">' . $page->show(2) . '</div>';
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirm" AND !empty($_GET['name']) AND !empty($_GET['id'])) {
?>
						<h3 class="alert">您确定要执行删除操作：<?php echo htmlspecialchars($_GET['name']); ?>？<br />该操作不可逆，该条目所有相关数据都将被清空！</h3>
						<a class="btn btn-danger" href="center.php?action=delete&id=<?php echo $_GET['id']; ?>">删除</a>　
						<a class="btn btn-warning" href="center.php?action=submit&id=<?php echo $_GET['id']; ?>">隐藏</a>　
<?php
						echo '<a class="btn btn-success" href="center.php?';
						if (!empty($_SESSION['page'])) {
							echo "page=" . $_SESSION['page'];
						}
						elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
							echo "action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type'];
						}
						echo '">取消</a>';
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
						$delete_id = (int)$_GET['id'];
						$f_query = mysql_query("SELECT `Filename` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $delete_id . "'");
						if (!$f_query) {
							goto endlabel;
						}
						else {
							$f_filename = mysql_fetch_assoc($f_query);
							if (!$f_filename) {
								goto endlabel;
							}
							unlink($f_filename['Filename']);
							$d_query = mysql_query("DELETE FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $delete_id . "'");
							$d_query = mysql_query("DELETE FROM `".DCRM_CON_PREFIX."Screenshots` WHERE `PID` = '" . $delete_id . "'");
							$d_query = mysql_query("DELETE FROM `".DCRM_CON_PREFIX."Reports` WHERE `PID` = '" . $delete_id . "'");
						}
						if (!$d_query) {
							goto endlabel;
						}
						elseif (!empty($_SESSION['page'])) {
							header("Location: center.php?page=" . $_SESSION['page']);
							exit();
						}
						elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
							header("Location: center.php?action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
							exit();
						}
						else {
							header("Location: center.php");
							exit();
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "submit" AND !empty($_GET['id'])) {
						$submit_id = (int)$_GET['id'];
						$s_query = mysql_query("SELECT `Package`, `Stat` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $submit_id . "'");
						if (!$s_query) {
							goto endlabel;
						}
						else {
							$s_info = mysql_fetch_assoc($s_query);
							if (!$s_info) {
								goto endlabel;
							}
						}
						if ((int)$s_info['Stat'] != 1) {
							$s_query = mysql_query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `Stat` = '-1' WHERE `Package` = '" . $s_info['Package'] . "'");
							$s_query = mysql_query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `Stat` = '1' WHERE `ID` = '" . $submit_id . "'");
						}
						else {
							$s_query = mysql_query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `Stat` = '-1' WHERE `ID` = '" . $submit_id . "'");
						}
						if (!$s_query) {
							goto endlabel;
						}
						elseif (!empty($_SESSION['page'])) {
							header("Location: center.php?page=" . $_SESSION['page']);
							exit();
						}
						elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
							header("Location: center.php?action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
							exit();
						}
						else {
							header("Location: center.php");
							exit();
						}
					}
					if (!$con) {
						endlabel:
?>
						<h3 class="alert alert-error">数据库出现错误！</h3>
						<code><?php echo mysql_error(); ?></code>
<?php
					}
					else {
						mysql_close($con);
					}
?>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
	function show(stat) {
		sli = document.getElementById('sli');
		
		if (stat == 1) {
			sli.innerHTML = '<a href="javascript:opt(4)">隐藏软件包</a>';
		} else {
			sli.innerHTML = '<a href="javascript:opt(5)">显示软件包</a>';
		}
		document.getElementById('mbar').style.display = "";
	}
	</script>
</body>
</html>
<?php
	}
	else {
		header("Location: login.php");
		exit();
	}
?>