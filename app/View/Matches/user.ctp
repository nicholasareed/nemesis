
<style type="text/css">
	
	.quick_card {
		margin: 20px 0px;
	}

	.record {
		margin: 20px 0px;
	}

	.record > div {
		float: left; 
		width: 150px;

		text-align: center;
	}

	.record span {
		font-size: 34px;
		line-height: 42px;
	}

</style>



<div class="quick_card">

	<fb:profile-pic uid="<? echo $user['User']['username']; ?>" facebook-logo="false" linked="false" size="square"></fb:profile-pic>

	<fb:name uid="<? echo $user['User']['username']; ?>" useyou="false" linked="false" capitalize="true" />

</div>


<div class="record clearfix">

	<div class="win">
		<span>
			<? echo $record['win']; ?>
		</span>
		<br />
		Wins
	</div>
	<div class="lose">
		<span>
			<? echo $record['lose']; ?>
		</span>
		<br />
		Losses
	</div>
	<div class="tie">
		<span>
			<? echo $record['tie']; ?>
		</span>
		<br />
		Ties
	</div>

</div>


<table class="user_history">

	<thead>
		<tr>
			<th>
				Date
			</th>
			<th>
				Sport
			</th>
			<th>
				W/L/T
			</th>
		</tr>
	</thead>

	<tbody>

		<? foreach($user['Match'] as $match){ ?>

			<tr>

				<td>
					<? echo date('F jS, Y, g:i a',strtotime($match['created'])); ?>
				</td>

				<td>
					<? echo h($match['Sport']['name']); ?>
				</td>

				<td>
					<a href="/matches/view/<? echo $match['id']; ?>">
						<?
							switch($match['MatchesUser']['status']){
								case 'win':
									echo "Won";
									break;
								case 'lose':
									echo "Lost";
									break;
								case 'tie':
									echo "Tied";
									break;
							}
						?>
					</a>
				</td>

			</tr>

		<? } ?>

	</tbody>

</table>

