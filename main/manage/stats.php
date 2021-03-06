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
	
	/* DCRM Statistics */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/autofill.inc.php");
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'stats';

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
				<h2>运行状态</h2>
				<br />
				<div class="wrapper">
					<ul class="breadcrumb" onclick="return false;"><i class="icon" id="triangle_mysql" onclick="wrapper('triangle_mysql','item_mysql'); return false;">▼</i>&nbsp;数据库状态</ul>
					<div class="item" style="display:block;" id="item_mysql">
						<?php echo nl2br(htmlspecialchars(str_replace("  ","\n",mysql_stat()))); ?>
					</div>
				</div>
				<div class="wrapper">
					<ul class="breadcrumb" onclick="return false;"><i class="icon" id="triangle_server" onclick="wrapper('triangle_server','item_server'); return false;">▼</i>&nbsp;服务信息</ul>
					<div class="item" style="display:block;" id="item_server">
						<?php
							if(function_exists("gd_info")){                  
								$gd_info = gd_info();
								$gd_version = $gd_info['GD Version'];
							}else {
								$gd_version = "Unknown";
							}
							$max_upload = ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled";
							date_default_timezone_set("Etc/GMT-8");
							$system_time = date("Y-m-d H:i:s",time());
							$content = '服务器信息：' . $_SERVER["SERVER_SOFTWARE"] . '<br />数据库版本：' . htmlspecialchars(mysql_get_server_info()) . '<br />GD 库版本：' . htmlspecialchars($gd_version) . '<br />最大上传限制：' . htmlspecialchars($max_upload) . '<br />最大执行时间：' . htmlspecialchars(ini_get("max_execution_time")).' 秒<br />服务器时间：' . htmlspecialchars($system_time);
							echo $content;
						?>
					</div>
				</div>
				<div class="wrapper">
					<ul class="breadcrumb" onclick="return false;"><i class="icon" id="triangle_manage" onclick="wrapper('triangle_manage','item_manage'); return false;">▼</i>&nbsp;管理统计</ul>
					<div class="item" style="display:block;" id="item_manage">
						<?php
							$q_info = mysql_query("SELECT sum(`DownloadTimes`) FROM `".DCRM_CON_PREFIX."Packages`");
							$info = mysql_fetch_row($q_info);
							$totalDownloads = (int)$info[0];
							$q_info = mysql_query("SELECT sum(`Size`) FROM `".DCRM_CON_PREFIX."Packages`");
							$info = mysql_fetch_row($q_info);
							$poolSize = (int)$info[0];
							$poolSize_withext = sizeext($poolSize);
							$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages`");
							$info = mysql_fetch_row($q_info);
							$num[0] = (int)$info[0];
							$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` != '-1'");
							$info = mysql_fetch_row($q_info);
							$num[1] = (int)$info[0];
							$q_info = mysql_query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
							$info = mysql_fetch_row($q_info);
							$num[2] = (int)$info[0];
							$tmpSize = dirsize("../tmp");
							$tmpSize_withext = sizeext($tmpSize);
							$content = '总下载次数：' . $totalDownloads . '<br />软件包数量：' . $num[0] . '<br />未隐藏软件包数量：' . $num[1] . '<br />分类数量：' . $num[2] . '<br />下载池尺寸：' . $poolSize_withext . '<br />缓存池尺寸：' . $tmpSize_withext . '　<a href="stats.php?action=clean">清理缓存</a>';
							echo $content;
						?>
						<br />
						<!-- Statistics Start -->
						<?php 
						if (defined("AUTOFILL_STATISTICS_INFO")) {
							echo AUTOFILL_STATISTICS_INFO;
						}
						?>
						<!-- Statistics End -->
					</div>
				</div>
			<?php
				}
				elseif (!empty($_GET['action']) AND $_GET['action'] == "clean") {
					deldir("../tmp");
					mkdir("../tmp");
					echo '<h2>清理缓存</h2><br />';
					echo '<h3 class="alert alert-success">缓存清理完成！<br />';
					echo '<a href="center.php">返回</a></h3>';
				}
				else {
					endlabel:
					echo $alert;
					mysql_close($con);
				}
			?>
			</div>
		</div>
	</div>
	</div>
	<script src="js/misc.js" type="text/javascript"></script>
</body>
</html>
<?php
	}
	else {
		header("Location: login.php");
		exit();
	}
?>