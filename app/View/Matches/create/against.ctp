
<div id="against">

	<div class="clearfix">
		
		<? foreach($friends as $friend){ ?>
			
			<div class="friend" data-fuid="<? echo $friend; ?>">
				<fb:profile-pic uid="<? echo $friend; ?>" facebook-logo="false" linked="false" size="square"></fb:profile-pic>

				<fb:name uid="<? echo $friend; ?>" linked="false" capitalize="true" />

			</div>

		<? } ?>

	</div>

	<div class="nodisplay">
		<?
			echo $this->Form->create('Match',array('url' => $this->here));

				echo $this->Form->input('Match.friend',array('type' => 'hidden'));

			echo $this->Form->end('Continue');
		?>
	</div>

</div>

<script type="text/javascript">
	
	$(document).ready(function(){

		$('.friend').click(function(){
			var friend_id = $(this).attr('data-fuid');
			$('#MatchFriend').val(friend_id); 
			$('#MatchCreateForm').submit();
		});

	});

</script>