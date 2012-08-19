

<style type="text/css">
	
	.result {
		display: inline-block;
	}

	.result span {

		display: inline-block;

		clear: both;

		margin: 10px;
		padding: 10px;

		cursor: pointer;

		border: 1px solid #ccc;
		background: #EBEBEB;
	}

	.result.win span {
		background: #CC3333;
		color: white;
		font-weight: bold;
	}

	.result.lose span {
		background: #0076A3;
		color: white;
		font-weight: bold;
	}

</style>


<div class="result_buttons">

	<div class="result win" data-result="win">
		 <span>
		 	I won!
		 </span>
	</div>

	<div class="result lose" data-result="lose">
		 <span>
		 	I lost :(
		 </span>
	</div>

	<div class="result tie" data-result="tie">
		 <span>
		 	We Tied
		 </span>
	</div>

</div>

<div class="nodisplay">
	<?
		echo $this->Form->create('Match',array('url' => $this->here));

			echo $this->Form->input('Match.result',array('type' => 'hidden'));

		echo $this->Form->end('Next');
	?>
</div>

<script type="text/javascript">
	
	$(document).ready(function(){

		$('.result').click(function(){
			var result = $(this).attr('data-result');
			$('#MatchResult').val(result); 
			$('#MatchCreateForm').submit();
		});

	});

</script>