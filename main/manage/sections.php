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
	
	/* DCRM Section Manage */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/autofill.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/func.php");
	require_once("include/tar.php");
	require_once("include/corepage.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'sections';
	
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
?>
				<h2>分类管理</h2>
				<br />
				<h3 class="navbar">分类列表　<a href="sections.php?action=add">添加分类</a>　<a href="sections.php?action=create">生成图标包</a></h3>
<?php
						if (isset($_GET['page'])) {
								$page = $_GET['page'];
						}
						else {
							$page = 1;
						}
						if ($page <= 0 OR $page >= 6) {
							$page = 1;
						}
						$page_a = $page * 10 - 10;
						if ($page == 1) {
							$page_b = $page;
						}
						else {
							$page_b = $page - 1;
						}
						$list_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Sections` ORDER BY `ID` DESC LIMIT ".(string)$page_a.",10",$con);
						if ($list_query == FALSE) {
							goto endlabel;
						}
						else {
?>
					<table class="table"><thead><tr>
					<th><ul class="ctl">编辑</ul></th>
					<th><ul class="ctl">名称</ul></th>
					<th><ul class="ctl">图标</ul></th>
					<th><ul class="ctl">最后修改</ul></th>
					</tr></thead><tbody>
<?php
							while ($list = mysql_fetch_assoc($list_query)) {
?>
					<tr>
					<td><a href="sections.php?action=delete_confirmation&id=<?php echo($list['ID']); ?>&name=<?php echo($list['Name']); ?>" class="close" style="line-height: 12px;">&times</a></td>
					<td><ul class="ctl" style="width:400px;"><a href="center.php?action=search&contents=<?php echo(urlencode($list['Name'])); ?>&type=7"><?php echo(htmlspecialchars($list['Name'])); ?></a></ul></td>
<?php
								if ($list['Icon'] != "") {
?>
					<td><ul class="ctl" style="width:150px;"><a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>/icons/<?php echo($list['Icon']); ?>"><?php echo($list['Icon']); ?></a></ul></td>
<?php
								}
								else {
?>
					<td><ul class="ctl" style="width:150px;">无图标</ul></td>
<?php
								}
?>
					<td><ul class="ctl" style="width:150px;"><?php echo($list['TimeStamp']); ?></ul></td>
					</tr>
<?php
							}
?>
					</tbody></table>
<?php
							$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
							$info = mysql_fetch_row($q_info);
							$totalnum = (int)$info[0];
							$params = array('total_rows'=>$totalnum, 'method'=>'html', 'parameter' =>'sections.php?page=%page', 'now_page'  =>$page, 'list_rows' =>10);
							$page = new Core_Lib_Page($params);
							echo '<div class="page">' . $page->show(2) . '</div>';
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "add") {
?>
						<h2>分类管理</h2>
						<br />
						<h3 class="navbar"><a href="sections.php">分类列表</a>　添加分类　<a href="sections.php?action=create">生成图标包</a></h3>
						<br />
						<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="sections.php?action=add_now" >
						<div class="group-control">
							<label class="control-label">分类名称</label>
							<div class="controls">
								<input class="input-xlarge" name="contents" required="required" />
								<input type="hidden" name="action" value="add_now" />
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">分类图标</label>
							<div class="controls">
								<input type="file" class="span6" name="icon" accept="image/x-png" />
								<p class="help-block">允许上传的格式：png，保存到根目录的 icons 目录下</p>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success">提交</button>
							</div>
						</div>
						</form>
<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "add_now" AND !empty($_POST['contents'])) {
						$new_name = mysql_real_escape_string($_POST['contents']);
						$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
						if (!$q_info) {
							goto endlabel;
						}
						$info = mysql_fetch_row($q_info);
						$num = (int)$info[0];
						if ($num <= 50) {
							if (pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION) == "png") {
								if (file_exists("../icons/" . $_FILES['icon']['name'])) {
									unlink("../icons/" . $_FILES['icon']['name']);
								}
								$move = rename($_FILES['icon']['tmp_name'],"../icons/" . $_FILES['icon']['name']);
								if (!$move) {
									$alert = "图标上传失败，请检查文件权限。";
									goto endlabel;
								}
								else {
									$n_query = mysql_query("INSERT INTO `".DCRM_CON_PREFIX."Sections`(`Name`, `Icon`) VALUES('" . $new_name . "', '" . $_FILES['icon']['name'] . "')");
								}
							}
							else {
								$n_query = mysql_query("INSERT INTO `".DCRM_CON_PREFIX."Sections`(`Name`) VALUES('" . $new_name . "')");
							}
						}
						else {
							$alert = "您最多只能添加 50 个分类！";
							goto endlabel;
						}
						if (!$n_query) {
							goto endlabel;
						}
						else {
							header("Location: sections.php");
							exit();
						}
					} elseif (!empty($_GET['action']) AND $_GET['action'] == "create") {
						if (defined("AUTOFILL_SEO") && defined("AUTOFILL_PRE")) {
							$alert = '您确定要生成 '.AUTOFILL_SEO.' 图标包 吗？<br /><a href="sections.php?action=createnow">立即生成</a>';
						} else {
							$alert = '您尚未填写站点SEO以及自动填充信息，无法使用该功能！';
						}
						goto endlabel;
					} elseif (!empty($_GET['action']) AND $_GET['action'] == "createnow") {
						$new_name = mysql_real_escape_string($_POST['contents']);
						$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections` WHERE `Icon` != ''");
						if (!$q_info) {
							goto endlabel;
						}
						$info = mysql_fetch_row($q_info);
						$num = (int)$info[0];
						if ($num < 1) {
							$alert = "找不到存在图标的分类条目，请先添加一个图标，再执行生成。";
							goto endlabel;
						}
						if (file_exists("include/empty_icon.deb")) {
							$r_id = randstr(40);
							if (!is_dir("../tmp/")) {
								$result = mkdir("../tmp/");
							}
							if (!is_dir("../tmp/" . $r_id)) {
								$result = mkdir("../tmp/" . $r_id);
								if (!$result) {
									$alert = "临时目录创建失败，请检查文件权限！";
									goto endlabel;
								}
							}
							$deb_path = "../tmp/" . $r_id . "/icon_" . time() . ".deb";
							$result = copy("include/empty_icon.deb", $deb_path);
							if (!$result) {
								$alert = "图标包模板复制失败，请检查文件权限！";
								goto endlabel;
							}
							$raw_data = new phpAr($deb_path);
							$new_tar = new Tar();
							$new_path = "../tmp/" . $r_id . "/data.tar.gz";
							$icon_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Sections`");
							while ($icon_assoc = mysql_fetch_assoc($icon_query)) {
								mkdir("../tmp/" . $r_id . "/Applications");
								mkdir("../tmp/" . $r_id . "/Applications/Cydia.app");
								mkdir("../tmp/" . $r_id . "/Applications/Cydia.app/Sections");
								if ($icon_assoc['Icon'] != "") {
									$new_filename = str_replace("[", "", str_replace("]", "", str_replace(" ", "_", $icon_assoc['Name']))) . ".png";
									$new_filepath = "../tmp/" . $r_id . "/Applications/Cydia.app/Sections/" . $new_filename;
									copy("../icons/" . $icon_assoc['Icon'], $new_filepath);
									$new_tar -> add_file("/Applications/Cydia.app/Sections/" . $new_filename, "", file_get_contents($new_filepath));
								}
							}
							$new_tar -> save($new_path);
							$result = $raw_data -> replace("data.tar.gz", $new_path);
							if (!$result) {
								$alert = "图标包模板改写失败！";
								goto endlabel;
							}
							else {
								$result = rename($deb_path, "../upload/" . AUTOFILL_PRE."sourceicon_" . time() . ".deb");
								if (!$result) {
									$alert = "图标包重定位失败！";
									goto endlabel;
								}
								header("Location: manage.php");
								exit();
							}
						}
						else {
							$alert = "图标包模板丢失，请重新安装 DCRM 专业版！";
							goto endlabel;
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirmation" AND !empty($_GET['id']) AND !empty($_GET['name'])) {
?>
						<h3 class="alert">您确定要删除：<?php echo(htmlspecialchars($_GET['name'])); ?>？</h3>
						<a class="btn btn-warning" href="sections.php?action=delete&id=<?php echo($_GET['id']); ?>">确定</a>　
						<a class="btn btn-success" href="sections.php">取消</a>
<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
						$delete_id = (int)$_GET['id'];
						$d_query = mysql_query("DELETE FROM `".DCRM_CON_PREFIX."Sections` WHERE `ID` = '" . $delete_id . "'",$con);
						if (!$d_query) {
							goto endlabel;
						}
						header("Location: sections.php");
						exit();
					}
					if (!$con) {
						endlabel:
						echo '<h3 class="alert alert-error">';
						if (isset($alert)) {
							echo $alert . '<br />';
						}
						echo '<a href="sections.php">返回</a></h3>';
					}
					else {
						mysql_close($con);
					}
?>
			</div>
		</div>
	</div>
	</div>
</body>
</html>
<?php
	}
	else {
		header("Location: login.php");
		exit();
	}
?>