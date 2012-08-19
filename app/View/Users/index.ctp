Index

<? 
	if($_FbAuth['li']){ 
		echo "Logged in";
	} else {
		echo $this->Html->link('Login','/users/fb_auth');
	}
?>