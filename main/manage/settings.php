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
	
	/* DCRM System Settings */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/autofill.inc.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'settings';
	
	/** 
	 * utf-8 转unicode 
	 * 
	 * @param string $name 
	 * @return string 
	 */  
	function utf8_unicode($name){  
		$name = iconv('UTF-8', 'UCS-2', $name);  
		$len  = strlen($name);  
		$str  = '';  
		for ($i = 0; $i < $len - 1; $i = $i + 2){  
			$c  = $name[$i];  
			$c2 = $name[$i + 1];  
			if (ord($c) > 0){   //两个字节的文字  
				$str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
			} else {  
				$str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);  
			}  
		}  
		return $str;  
	}

	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
		$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		if (!$con) {
			echo(mysql_error());
			exit();
		}
		mysql_query("SET NAMES utf8");
		$select  = mysql_select_db(DCRM_CON_DATABASE);
		if (!$select) {
			echo(mysql_error());
			exit();
		}
		
		require_once("header.php");

					if (!isset($_GET['action'])) {
				?>
				<h2><?php _e( 'Preferences' ); ?></h2>
				<br />
				<form class="form-horizontal" method="POST" action="settings.php?action=set">
					<fieldset>
						<h3><?php _e( 'General' ); ?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e( 'Language' ); if ( substr( $locale, 0, 2 ) != 'en' ) { ?><br /> Language<?php } ?></label>
							<div class="controls">
								<select name="language">
<?php
										$languages = get_available_languages();
										$langtext = '<option value="Detect"';
										if (defined("DCRM_LANG") && DCRM_LANG == 'Detect')
											$langtext .= ' selected="selected"';
										$langtext .= '>'._x( 'Detect', 'language' );
										if ( substr( $locale, 0, 2 ) != 'en' )
											$langtext .= ' - Detect';
										$langtext .= "</option>\n";

										$languages_list = languages_list();
										foreach( $languages as $language ) {
											$langtext .= "<option value=\"$language\"";
											if (defined("DCRM_LANG") && DCRM_LANG == $language)
												$langtext .= ' selected="selected"';
											$langtext .= '>';
											$langtext .= isset($languages_list[$language]) ? $languages_list[$language] : $language;
											$langtext .= " - " . $language . "</option>\n";
										}
										
										if(!in_array('en', $languages) && !in_array('en_US', $languages) && !in_array('en_GB', $languages)){
											$langtext .= "<option value=\"en_US\"";
											if (defined("DCRM_LANG") && DCRM_LANG == 'en_US')
												$langtext .= ' selected="selected"';
											$langtext .= '>'._x('English', 'language')." - en_US</option>\n";
										}
										echo $langtext;
									?>
								</select>
								<p class="help-block"><?php _e('If you want system auto detect users browser language to show pages please select "Detect" option.'); if ( substr( $locale, 0, 2 ) != 'en' ) { ?><br />If you want system auto detect users browser language to show pages please select "Detect" option.<?php } ?></p>
							</div>
						</div>
						<br />
						<h3><?php _e('Login Information');?></h3>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Username');?></label>
							<div class="controls">
								<input type="text" required="required" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('New Password');?></label>
							<div class="controls">
								<input type="password" name="pass1" id="pass1"/>
								<p class="help-block"><?php _e('If you would like to change the password type a new one. Otherwise leave this blank.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Repeat New Password');?></label>
							<div class="controls">
								<input type="password" name="pass2" id="pass2"/>
								<p class="help-block"><?php _e('Type your new password again.');?></p>
								<div id="pass-strength-result" style="display: block;"><?php _e('Strength indicator'); ?></div>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">最大尝试次数</label>
							<div class="controls">
								<input type="text" required="required" name="trials" value="<?php echo htmlspecialchars(DCRM_MAXLOGINFAIL); ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">尝试次数重置时间</label>
							<div class="controls">
								<input type="text" required="required" name="resettime" value="<?php if(defined(DCRM_LOGINFAILRESETTIME)){echo(htmlspecialchars(DCRM_LOGINFAILRESETTIME)/60);}else{echo(10);} ?>"/>
								<p class="help-block">单位：分钟</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;">源地址</label>
							<div class="controls">
								<input type="text" required="required" name="url_repo" style="width: 400px;" value="<?php echo htmlspecialchars(base64_decode(DCRM_REPOURL)); ?>"/>
								<p class="help-block">展示在首页供用户添加</p>
							</div>
						</div>
						<br />
						<h3>电脑版功能</h3>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;">总开关</label>
							<div class="controls">
								<select name="pcindex">
									<?php
										if (DCRM_PCINDEX == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
								<p class="help-block">开启后非苹果用户将自动跳转</p>
							</div>
						</div>
						<br />
						<h3>移动版功能</h3>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;">总开关</label>
							<div class="controls">
								<select name="mobile">
									<?php
										if (DCRM_MOBILE == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">展示最新列表</label>
							<div class="controls">
								<select name="list">
									<?php
										if (DCRM_SHOWLIST == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">最新列表数量</label>
							<div class="controls">
								<input type="text"  name="listnum" value="<?php echo htmlspecialchars(DCRM_SHOW_NUM); ?>"/>
								<p class="help-block">最大不得超过 20 条</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">分类完整列表</label>
							<div class="controls">
								<select name="allowfulllist">
									<?php
										if (DCRM_ALLOW_FULLLIST == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">预览截图</label>
							<div class="controls">
								<select name="screenshots">
									<?php
										if (DCRM_SCREENSHOTS == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">报告问题</label>
							<div class="controls">
								<select name="reporting">
									<?php
										if (DCRM_REPORTING == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">限制报告次数</label>
							<div class="controls">
								<input type="text"  name="reportlimit" value="<?php echo htmlspecialchars(DCRM_REPORT_LIMIT); ?>"/>
								<p class="help-block">最大不得超过 10 次</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">更新日志</label>
							<div class="controls">
								<select name="updatelogs">
									<?php
										if (DCRM_UPDATELOGS == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
								<p class="help-block">显示在历史版本中的更新日志</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">更多信息</label>
							<div class="controls">
								<select name="moreinfo">
									<?php
										if (DCRM_MOREINFO == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">详细描述</label>
							<div class="controls">
								<select name="multiinfo">
									<?php
										if (DCRM_MULTIINFO == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<h3>下载设置</h3>
						<br />
						<div class="group-control">
							<label class="control-label">防盗链</label>
							<div class="controls">
								<select name="directdown">
									<?php
										if (DCRM_DIRECT_DOWN == 2) {
											echo '<option value="2" selected="selected">开启</option>\n<option value="1">关闭</option>';
										}
										else {
											echo '<option value="1" selected="selected">关闭</option>\n<option value="2">开启</option>';
										}
									?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;">最大下载速度</label>
							<div class="controls">
								<input type="text" required="required" name="speedlimit" value="<?php echo htmlspecialchars(DCRM_SPEED_LIMIT); ?>"/>
								<p class="help-block">字节每秒，不限制请填写 0</p>
							</div>
						</div>
						<br />
						<h3>列表设置</h3>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;">Packages 压缩</label>
							<div class="controls">
								<select name="listsmethod">
									<?php
										function getzmethod($opt) {
											switch ($opt) {
												case 0:
													$opt_text = "隐藏列表";
													break;
												case 1:
													$opt_text = "仅文本";
													break;
												case 2:
													$opt_text = "仅 gz";
													break;
												case 3:
													$opt_text = "文本与 gz";
													break;
												case 4:
													$opt_text = "仅 bz2";
													break;
												case 5:
													$opt_text = "文本与 bz2";
													break;
												case 6:
													$opt_text = "gz 与 bz2";
													break;
												case 7:
													$opt_text = "全部";
													break;
												default:
													$opt_text = "";
											}
											return $opt_text;
										}
										for ($opt = 0; $opt <= 7; $opt++) {
											if (DCRM_LISTS_METHOD == $opt) {
												echo '<option value="' . $opt . '" selected="selected">' . htmlspecialchars(getzmethod($opt)) . '</option>\n';
											}
											else {
												echo '<option value="' . $opt . '">' . htmlspecialchars(getzmethod($opt)) . '</option>\n';
											}
										}
									?>
								</select>
								<p class="help-block">若修改后发现刷新列表出错，请更换压缩方式</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" style="color: red;">软件包验证</label>
							<div class="controls">
								<select name="checkmethod">
									<?php
										function getsmethod($opt) {
											switch ($opt) {
												case 0:
													$opt_text = "不验证";
													break;
												case 1:
													$opt_text = "MD5Sum";
													break;
												case 2:
													$opt_text = "MD5Sum & SHA1";
													break;
												case 3:
													$opt_text = "MD5Sum & SHA1 & SHA256";
													break;
												default:
													$opt_text = "";
											}
											return $opt_text;
										}
										for ($opt = 0; $opt <= 3; $opt++) {
											if (DCRM_CHECK_METHOD == $opt) {
												echo '<option value="' . $opt . '" selected="selected">' . htmlspecialchars(getsmethod($opt)) . '</option>\n';
											}
											else {
												echo '<option value="' . $opt . '">' . htmlspecialchars(getsmethod($opt)) . '</option>\n';
											}
										}
									?>
								</select>
								<p class="help-block">仅在写入软件包时生效</p>
							</div>
						</div>
						<br />
						<h3>自动填充</h3>
						<br />
						<div class="group-control">
							<label class="control-label">软件包默认标识符</label>
							<div class="controls">
								<input type="text" name="PRE" style="width: 400px;" value="<?php if(defined("AUTOFILL_PRE")){echo(htmlspecialchars(stripslashes(AUTOFILL_PRE)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">软件包默认名称</label>
							<div class="controls">
								<input type="text" name="NONAME" style="width: 400px;" value="<?php if(defined("AUTOFILL_NONAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_NONAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">软件包默认描述</label>
							<div class="controls">
								<textarea type="text" name="DESCRIPTION" style="height: 40px; width: 400px;"><?php if(defined("AUTOFILL_DESCRIPTION")){echo(htmlspecialchars(stripslashes(AUTOFILL_DESCRIPTION)));} ?></textarea>
							</div>
						</div>
						<br />
						<h3>SEO 优化</h3>
						<br />
						<div class="group-control">
							<label class="control-label">SEO 名称</label>
							<div class="controls">
								<input type="text" name="SEO" value="<?php if(defined("AUTOFILL_SEO")){echo(htmlspecialchars(stripslashes(AUTOFILL_SEO)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">SEO 域名</label>
							<div class="controls">
								<input type="text" name="SITE" style="width: 400px;" value="<?php if(defined("AUTOFILL_SITE")){echo(htmlspecialchars(stripslashes(AUTOFILL_SITE)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">SEO 关键词</label>
							<div class="controls">
								<textarea type="text" name="KEYWORDS" style="height: 40px; width: 400px;"><?php if(defined("AUTOFILL_KEYWORDS")){echo(htmlspecialchars(stripslashes(AUTOFILL_KEYWORDS)));} ?></textarea>
								<p class="help-block">以英文半角逗号分隔</p>
							</div>
						</div>
						<br />
						<h3>管理员信息</h3>
						<br />
						<div class="group-control">
							<label class="control-label">管理员名称</label>
							<div class="controls">
								<input type="text" name="MASTER" value="<?php if(defined("AUTOFILL_MASTER")){echo(htmlspecialchars(stripslashes(AUTOFILL_MASTER)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">管理员全名</label>
							<div class="controls">
								<input type="text" name="FULLNAME" style="width: 400px;" value="<?php if(defined("AUTOFILL_FULLNAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FULLNAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">管理员邮箱</label>
							<div class="controls">
								<input type="text" name="EMAIL" style="width: 400px;" value="<?php if(defined("AUTOFILL_EMAIL")){echo(htmlspecialchars(stripslashes(AUTOFILL_EMAIL)));} ?>"/>
							</div>
						</div>
						<br />
						<h3>底部设置</h3>
						<br />
						<div class="group-control">
							<label class="control-label">版权起始年份</label>
							<div class="controls">
								<input type="number" name="FOOTER_YEAR" style="width: 65px;" value="<?php if(defined("AUTOFILL_FOOTER_YEAR")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_YEAR)));} ?>" />
								<p class="help-block">如输入 2010，最终显示为 © 2010-<?php echo(date("Y")); ?>，留空则只显示当前年份。</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">版权名称</label>
							<div class="controls">
								<input type="text" name="FOOTER_NAME" value="<?php if(defined("AUTOFILL_FOOTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_NAME)));} ?>"/>
								<p class="help-block">显示在网站底部。如不填写则显示源名称。</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">页脚信息</label>
							<div class="controls">
								<textarea cols="50" rows="10" name="FOOTER_CODE" style="height: 80px; width: 400px;"><?php if(defined("AUTOFILL_FOOTER_CODE")){echo(htmlspecialchars(stripslashes(AUTOFILL_FOOTER_CODE)));} ?></textarea>
								<p class="help-block">可填写备案号、网站信息等，请使用 <code>·</code> 作为分隔符。</p>
							</div>
						</div>
						<br />
						<h3>社会化分享</h3>
						<br />
						<div class="group-control">
							<label class="control-label">多说社会化评论框 Key</label>
							<div class="controls">
								<input type="text" name="DUOSHUO_KEY" value="<?php if(defined("AUTOFILL_DUOSHUO_KEY")){echo(htmlspecialchars(stripslashes(AUTOFILL_DUOSHUO_KEY)));} ?>"/>
								<p class="help-block">请前往 <a href="http://duoshuo.com/">http://duoshuo.com</a> 获取 Key（需要注册），留空关闭评论功能。</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">QQ群名称</label>
							<div class="controls">
								<input type="text" name="TENCENT_NAME" value="<?php if(defined("AUTOFILL_TENCENT_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_TENCENT_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">QQ群地址</label>
							<div class="controls">
								<input type="text" name="TENCENT" style="width: 400px;" value="<?php if(defined("AUTOFILL_TENCENT")){echo(htmlspecialchars(stripslashes(AUTOFILL_TENCENT)));} ?>"/>
								<p class="help-block">需要新版手机客户端支持</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">微博名称</label>
							<div class="controls">
								<input type="text" name="WEIBO_NAME" value="<?php if(defined("AUTOFILL_WEIBO_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_WEIBO_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">微博地址</label>
							<div class="controls">
								<input type="text" name="WEIBO" style="width: 400px;" value="<?php if(defined("AUTOFILL_WEIBO")){echo(htmlspecialchars(stripslashes(AUTOFILL_WEIBO)));} ?>"/>
								<p class="help-block">请填写移动版主页</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Twitter 名称</label>
							<div class="controls">
								<input type="text" name="TWITTER_NAME" value="<?php if(defined("AUTOFILL_TWITTER_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_TWITTER_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Twitter 地址</label>
							<div class="controls">
								<input type="text" name="TWITTER" style="width: 400px;" value="<?php if(defined("AUTOFILL_TWITTER")){echo(htmlspecialchars(stripslashes(AUTOFILL_TWITTER)));} ?>"/>
								<p class="help-block">登录后方可查看</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Facebook 名称</label>
							<div class="controls">
								<input type="text" name="FACEBOOK_NAME" value="<?php if(defined("AUTOFILL_FACEBOOK_NAME")){echo(htmlspecialchars(stripslashes(AUTOFILL_FACEBOOK_NAME)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Facebook 地址</label>
							<div class="controls">
								<input type="text" name="FACEBOOK" style="width: 400px;" value="<?php if(defined("AUTOFILL_FACEBOOK")){echo(htmlspecialchars(stripslashes(AUTOFILL_FACEBOOK)));} ?>"/>
								<p class="help-block">登录后方可查看</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">Paypal 捐助地址</label>
							<div class="controls">
								<input type="text" name="PAYPAL" style="width: 400px;" value="<?php if(defined("AUTOFILL_PAYPAL")){echo(htmlspecialchars(stripslashes(AUTOFILL_PAYPAL)));} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">支付宝捐助地址</label>
							<div class="controls">
								<input type="text" name="ALIPAY" style="width: 400px;" value="<?php if(defined("AUTOFILL_ALIPAY")){echo(htmlspecialchars(stripslashes(AUTOFILL_ALIPAY)));} ?>"/>
							</div>
						</div>
						<br />
						<h3>统计与广告</h3>
						<br />
						<div class="group-control">
							<label class="control-label">外部统计代码</label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="STATISTICS" ><?php if(defined("AUTOFILL_STATISTICS")){echo(htmlspecialchars(stripslashes(AUTOFILL_STATISTICS)));} ?></textarea>
								<p class="help-block">不可见的统计代码</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">内部统计代码</label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="STATISTICS_INFO" ><?php if(defined("AUTOFILL_STATISTICS_INFO")){echo(htmlspecialchars(stripslashes(AUTOFILL_STATISTICS_INFO)));} ?></textarea>
								<p class="help-block">查看信息的统计代码</p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">广告支持</label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="ADVERTISEMENT" ><?php if(defined("AUTOFILL_ADVERTISEMENT")){echo(htmlspecialchars(stripslashes(AUTOFILL_ADVERTISEMENT)));} ?></textarea>
							</div>
						</div>
						<br />
						<h3>通知</h3>
						<br />
						<div class="group-control">
							<label class="control-label">紧急通知</label>
							<div class="controls">
								<textarea type="text" style="height: 80px; width: 400px;" name="EMERGENCY" ><?php if(defined("AUTOFILL_EMERGENCY")){echo(htmlspecialchars(stripslashes(AUTOFILL_EMERGENCY)));} ?></textarea>
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
						$error_stat = false;
						$logout = false;
						$error_text = '';
						if (!isset($_POST['username']) OR empty($_POST['username'])) {
							$error_text .= "用户名不得设置为空！\n";
							$error_stat = true;
						}
						if (strlen($_POST['username']) > 20 || strlen($_POST['username']) < 4) {
							$error_text .= "用户名长度必须在 4 - 20 个字符之间！\n";
							$error_stat = true;
						}
						if (!preg_match("/^[0-9a-zA-Z\_]*$/", $_POST['username'])) {
							$error_text .= "用户名只能使用数字、字母、下划线的组合！\n";
							$error_stat = true;
						}
						if ( !empty($_POST['pass1']) && empty($_POST['pass2']) ) {
							$error_text .= __( 'You entered your new password only once.' )."\n";
							$error_stat = true;
						}elseif ( $_POST['pass1'] != $_POST['pass2'] ) {
							$error_text .= __( 'Your passwords do not match. Please try again.' )."\n";
							$error_stat = true;
						}
						if (!isset($_POST['trials']) OR !ctype_digit($_POST['trials'])) {
							$error_text .= "最大尝试次数必须为整数！\n";
							$error_stat = true;
						}
						if (!isset($_POST['resettime']) OR !ctype_digit($_POST['resettime'])) {
							$error_text .= "尝试次数重置时间必须为整数！\n";
							$error_stat = true;
						}
						if (!isset($_POST['speedlimit']) OR !ctype_digit($_POST['speedlimit'])) {
							$error_text .= "下载限速必须为整数！\n";
							$error_stat = true;
						}
						if (!isset($_POST['directdown']) OR !ctype_digit($_POST['directdown'])) {
							$error_text .= "请正确设置防盗链开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['pcindex']) OR !ctype_digit($_POST['pcindex'])) {
							$error_text .= "请正确设置电脑版总开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['mobile']) OR !ctype_digit($_POST['mobile'])) {
							$error_text .= "请正确设置移动版总开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['screenshots']) OR !ctype_digit($_POST['screenshots'])) {
							$error_text .= "请正确设置预览截图开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['reporting']) OR !ctype_digit($_POST['reporting'])) {
							$error_text .= "请正确设置报告问题开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['reportlimit']) OR !ctype_digit($_POST['reportlimit']) OR (int)$_POST['reportlimit'] > 20) {
							$error_text .= "请正确设置报告问题限制次数，最大不得超过 10 次！\n";
							$error_stat = true;
						}
						if (!isset($_POST['updatelogs']) OR !ctype_digit($_POST['updatelogs'])) {
							$error_text .= "请正确设置更新日志开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['moreinfo']) OR !ctype_digit($_POST['moreinfo'])) {
							$error_text .= "请正确设置更多信息开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['multiinfo']) OR !ctype_digit($_POST['multiinfo'])) {
							$error_text .= "请正确设置自定义展示开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['listsmethod']) OR !ctype_digit($_POST['listsmethod']) OR (int)$_POST['listsmethod'] > 7) {
							$error_text .= "请正确设置正确的 Packages 压缩方式！\n";
							$error_stat = true;
						}
						if (!isset($_POST['list']) OR !ctype_digit($_POST['list'])) {
							$error_text .= "请正确设置首页更新列表开关！\n";
							$error_stat = true;
						} else {
							if (!isset($_POST['listnum']) OR !ctype_digit($_POST['listnum']) OR (int)$_POST['listnum'] > 20) {
								$error_text .= "请正确设置首页更新列表数量，最大不得超过 20 条！\n";
								$error_stat = true;
							}
						}
						if (!isset($_POST['allowfulllist']) OR !ctype_digit($_POST['allowfulllist'])) {
							$error_text .= "请正确设置允许查看完整列表开关！\n";
							$error_stat = true;
						}
						if (!isset($_POST['url_repo']) OR empty($_POST['url_repo'])) {
							$error_text .= "源地址不得设置为空！\n";
							$error_stat = true;
						}
						if ($error_stat === false) {
							$result = mysql_query("SELECT `ID` FROM `".DCRM_CON_PREFIX."Users` WHERE (`Username` = '".mysql_real_escape_string($_POST['username'])."' AND `ID` != '".$_SESSION['userid']."')");
							if (!$result OR mysql_affected_rows() != 0) {
								$error_text .= "存在相同的用户名！\n";
								$error_stat = true;
							}
							else {
								$result = mysql_query("UPDATE `".DCRM_CON_PREFIX."Users` SET `Username` = '".mysql_real_escape_string($_POST['username'])."' WHERE `ID` = '".$_SESSION['userid']."'");
								if (!empty($_POST['pass1'])) {
									$logout = true;
									$result = mysql_query("UPDATE `".DCRM_CON_PREFIX."Users` SET `SHA1` = '".sha1($_POST['pass1'])."' WHERE `ID` = '".$_SESSION['userid']."'");
								}
							}
						}
						if ($error_stat == true) {
							echo '<h3 class="alert alert-error">';
							echo $error_text;
							echo '<br /><a href="settings.php" onclick="javascript:history.go(-1);return false;">返回</a></h3>';
						}
						else {
							$config_text = "<?php\n\tif (!defined(\"DCRM\")) {\n\t\texit;\n\t}\n";
							$config_text .= "\tdefine(\"DCRM_LANG\", \"".$_POST['language']."\");\n";
							$config_text .= "\tdefine(\"DCRM_MAXLOGINFAIL\", ".$_POST['trials'].");\n";
							$config_text .= "\tdefine(\"DCRM_SHOWLIST\", ".$_POST['list'].");\n";
							$config_text .= "\tdefine(\"DCRM_SHOW_NUM\", ".$_POST['listnum'].");\n";
							$config_text .= "\tdefine(\"DCRM_ALLOW_FULLLIST\", ".$_POST['allowfulllist'].");\n";
							$config_text .= "\tdefine(\"DCRM_SPEED_LIMIT\", ".$_POST['speedlimit'].");\n";
							$config_text .= "\tdefine(\"DCRM_DIRECT_DOWN\", ".$_POST['directdown'].");\n";
							$config_text .= "\tdefine(\"DCRM_PCINDEX\", ".$_POST['pcindex'].");\n";
							$config_text .= "\tdefine(\"DCRM_MOBILE\", ".$_POST['mobile'].");\n";
							$config_text .= "\tdefine(\"DCRM_SCREENSHOTS\", ".$_POST['screenshots'].");\n";
							$config_text .= "\tdefine(\"DCRM_REPORTING\", ".$_POST['reporting'].");\n";
							$config_text .= "\tdefine(\"DCRM_REPORT_LIMIT\", ".$_POST['reportlimit'].");\n";
							$config_text .= "\tdefine(\"DCRM_UPDATELOGS\", ".$_POST['updatelogs'].");\n";
							$config_text .= "\tdefine(\"DCRM_MOREINFO\", ".$_POST['moreinfo'].");\n";
							$config_text .= "\tdefine(\"DCRM_MULTIINFO\", ".$_POST['multiinfo'].");\n";
							$config_text .= "\tdefine(\"DCRM_LISTS_METHOD\", ".$_POST['listsmethod'].");\n";
							$config_text .= "\tdefine(\"DCRM_CHECK_METHOD\", ".$_POST['checkmethod'].");\n";
							$config_text .= "\tdefine(\"DCRM_REPOURL\", \"".base64_encode($_POST['url_repo'])."\");\n";
							$config_text .= "\tdefine(\"DCRM_LOGINFAILRESETTIME\", ".($_POST['resettime']*60).");\n";
							$config_text .= "?>";
							$autofill_text = "<?php\n\tif (!defined(\"DCRM\")) {\n\t\texit;\n\t}\n";
							$autofill_list = array("EMERGENCY", "PRE", "NONAME", "MASTER", "FULLNAME", "EMAIL", "SITE", "WEIBO", "WEIBO_NAME", "TWITTER", "TWITTER_NAME", "FACEBOOK", "FACEBOOK_NAME", "DESCRIPTION", "SEO", "KEYWORDS", "PAYPAL", "ALIPAY", "STATISTICS", "STATISTICS_INFO", "ADVERTISEMENT", "TENCENT", "TENCENT_NAME", "DUOSHUO_KEY", "FOOTER_YEAR", "FOOTER_CODE", "FOOTER_NAME");
							foreach ($autofill_list as $value) {
								if (!empty($_POST[$value])) {
									$autofill_text .= "\tdefine(\"AUTOFILL_".$value."\", \"".addslashes(str_replace(array("\r","\n"), '',nl2br(htmlspecialchars_decode($_POST[$value]))))."\");\n";
								}
							}
							$autofill_text .= "?>";
							$config_handle = fopen("include/config.inc.php", "w");
							fputs($config_handle,stripslashes($config_text));
							fclose($config_handle);
							$autofill_handle = fopen("include/autofill.inc.php", "w");
							fputs($autofill_handle,$autofill_text);
							fclose($autofill_handle);
							echo '<h3 class="alert alert-success">设置修改成功。<br/><a href="settings.php">返回</a></h3>';
							if ($logout) {
								header("Location: login.php?action=logout");
							}
						}
					}
				?>
			</div>
		</div>
	</div>
	</div>
	<script src="../js/password-strength.min.js" type="text/javascript"></script>
	<script src="../js/zxcvbn-async.min.js" type="text/javascript"></script>
	<script src="../js/zxcvbn.min.js" type="text/javascript"></script>
	<script type='text/javascript'>
	var pwsL10n = {"empty":"<?php echo( utf8_unicode( __( 'Strength indicator' ) ) ); ?>","short":"<?php echo( utf8_unicode( _x( 'Short', 'Password' ) ) ); ?>","bad":"<?php echo( utf8_unicode( _x( 'Bad', 'Password' ) ) ); ?>","good":"<?php echo( _x( 'Good', 'Password' ) ); ?>","strong":"<?php echo( utf8_unicode( _x( 'Strong', 'Password' ) ) ); ?>","mismatch":"<?php echo( utf8_unicode( _x( 'Mismatch', 'Password' ) ) ); ?>"};
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