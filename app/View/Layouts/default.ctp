
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>

	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css(array('fb_base',
									'extra',
									));
	?>

	<!-- Javascript -->
	<?
		echo $this->Html->script(array('jquery-1.8.0.min.js',
										));
	?>

</head>
<body>
	<!-- Facebook -->
	<? echo $this->element('fb_default_layout'); ?>

	<div id="container">
		<div id="header">
			<h1>
				<a href="/">Nemesis</a>
				<small class="header_links">
					<? 
						echo $this->Html->link('api','/pages/api');
						echo "&nbsp;&nbsp;&nbsp;";

						if($_FbAuth['li']){
							echo $this->Html->link('me','/matches/user');
							echo "&nbsp;&nbsp;&nbsp;";
							echo $this->Html->link('new match','/matches/create');
							echo "&nbsp;&nbsp;";
							echo '('.$this->Html->link('logout','/users/logout').')';
						} else {
							echo '('.$this->Html->link('login','/users/fb_auth').')';
						}
					?>
				</small>
			</h1>
		</div>
		<div id="content">

			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer">
			<!--Just footing around-->
		</div>
	</div>
	<?php //echo $this->element('sql_dump'); ?>
</body>
</html>
