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
	
	/* DCRM APT Information Settings */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'release';
	
	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {

		require_once("header.php");
		
					if (!isset($_GET['action'])) {
						if (file_exists("include/release.save")) {
							$release_file = file("include/release.save");
							$release = array();
							foreach ($release_file as $line) {
								if(preg_match("#^Origin|Label|Version|Codename|Description#", $line)) {
									$release[trim(preg_replace("#^(.+):\\s*(.+)#","$1", $line))] = trim(preg_replace("#^(.+):\\s*(.+)#","$2", $line));
								}
							}
						}
				?>
				<h2>源信息设置</h2>
				<br />
				<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="release.php?action=set">
					<fieldset>
						<div class="group-control">
							<label class="control-label">名称</label>
							<div class="controls">
								<input type="text" required="required" name="origin" value="<?php if (!empty($release['Origin'])) {echo htmlspecialchars($release['Origin']);} ?>"/>
								<p class="help-block">这个名称将显示在 Cydia 的软件源编辑界面</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">代号</label>
							<div class="controls">
								<input type="text" required="required" name="label" value="<?php if (!empty($release['Label'])) {echo htmlspecialchars($release['Label']);} ?>"/>
								<p class="help-block">这个名称将显示在软件包列表顶部</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">标识符</label>
							<div class="controls">
								<input type="text" required="required" name="codename" value="<?php if (!empty($release['Codename'])) {echo htmlspecialchars($release['Codename']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">描述</label>
							<div class="controls">
								<textarea type="text" style="height: 40px;" required="required" name="description" ><?php if (!empty($release['Description'])) {echo $release['Description'];} ?></textarea>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">版本</label>
							<div class="controls">
								<input type="text" required="required" name="version" value="<?php if (!empty($release['Version'])) {echo htmlspecialchars($release['Version']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">源图标</label>
							<div class="controls">
								<input type="file" class="span6" name="icon" accept="image/x-png" />
								<p class="help-block">允许上传的格式：png，保存为根目录 <a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>/CydiaIcon.png">CydiaIcon.png</a></p>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success">保存</button>
							</div>
						</div>
					</fieldset>
				</form>
				<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "set") {
						$release_text = "Origin: ".stripslashes($_POST['origin']);
						$release_text .= "\nLabel: ".$_POST['label'];
						$release_text .= "\nSuite: stable";
						$release_text .= "\nVersion: ".$_POST['version'];
						$release_text .= "\nCodename: ".$_POST['codename'];
						$release_text .= "\nArchitectures: iphoneos-arm";
						$release_text .= "\nComponents: main";
						$release_text .= "\nDescription: ".str_replace("\n","<br />",$_POST['description']);
						$release_text .= "\n";
						$release_handle = fopen("include/release.save","w");
						fputs($release_handle,stripslashes($release_text));
						fclose($release_handle);
						if (pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION) == "png") {
							if (file_exists("../CydiaIcon.png")) {
								$result_1 = unlink("../CydiaIcon.png");
							}
							$result_2 = rename($_FILES['icon']['tmp_name'], "../CydiaIcon.png");
							if (!$result_1 OR !$result_2) {
								echo '<h3 class="alert alert-error">源图标上传失败，请检查文件权限。<br /><a href="release.php">返回</a></h3>';
							}
							else {
								echo '<h3 class="alert alert-success">源图标上传完成。<br />源信息设置修改完成，刷新列表以应用更改。<br /><a href="release.php">返回</a></h3>';
							}
						}
						else {
							echo '<h3 class="alert alert-success">源信息设置修改完成，刷新列表以应用更改。<br /><a href="release.php">返回</a></h3>';
						}
					}
				?>
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