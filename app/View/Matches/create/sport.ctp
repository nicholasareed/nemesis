
<style type="text/css">
	
	.sport span {

		display: inline-block;

		clear: both;

		margin: 10px;
		padding: 10px;

		cursor: pointer;

		border: 1px solid #ccc;
		background: #EBEBEB;
	}

</style>



<? foreach($sports as $id => $sport){ ?>
	
	<div class="sport" data-sport-id="<? echo $id; ?>">
		
		<span>
			<? echo $sport; ?>
		</span>

	</div>

<? } ?>

<div class="nodisplay">
	<?
		echo $this->Form->create('Match',array('url' => $this->here));

			echo $this->Form->input('Match.sport',array('type' => 'hidden'));

		echo $this->Form->end('Next');
	?>
</div>

<script type="text/javascript">
	
	$(document).ready(function(){

		$('.sport').click(function(){
			var sport_id = $(this).attr('data-sport-id');
			$('#MatchSport').val(sport_id); 
			$('#MatchCreateForm').submit();
		});

	});

</script>