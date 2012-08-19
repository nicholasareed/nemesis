
<style type="text/css">
	
	.people {
		margin-bottom: 20px; 
	}

	.person {
		text-align: center;
		float: left;
		width: 49%;
	}

	.winner,
	.loser {

		width: 100px;
		text-align: center;
		margin: 10px auto;

		display: none;
	}	

	.winner {
		background: #CC3333;
		color: white;
	}

	.is_winner .winner {
		display: block;
	}
	.is_loser .loser {
		display: block;
	}

	.person .pic {

	}

	.person .name {
		margin: 10px 0px;
	}

	.tie {

		text-align: center;

		margin: 0pt auto;
		width: 200px;
		background: #eee;
		border: 1px solid #EBEBEB;
		padding: 10px;
	}

	.details {
		text-align: center;
		margin:20px 0px;
	}

	.game_name {
		color: #3b5998;
	}

</style>


<div class="details">
	On <? echo date('F jS \a\r\o\u\n\d g:ia',strtotime($match['Match']['created'])); ?> a game of <span class="game_name"><? echo h($match['Sport']['name']); ?></span> was played! 
</div>

<div class="people clearfix">

	<? foreach($match['User'] as $user){ ?>

		<div class="person <? echo $user['MatchesUser']['status'] == 'win' ? 'is_winner' : ''; ?> <? echo $user['MatchesUser']['status'] == 'lose' ? 'is_loser' : ''; ?>">

			<div class="winner">
				Winner
			</div>

			<div class="loser">
				Loser
			</div>

			<div class="pic">
				<a href="/matches/user/<? echo $user['id']; ?>">
					<fb:profile-pic uid="<? echo $user['username']; ?>" facebook-logo="false" linked="false" size="square"></fb:profile-pic>
				</a>
			</div>

			<div class="name">
				<a href="/matches/user/<? echo $user['id']; ?>">
					<fb:name uid="<? echo $user['username']; ?>" linked="false" capitalize="true" useyou="false" />
				</a>
			</div>

		</div>

	<? } ?>

</div>

<? if($match['Match']['status'] == 'tie'){ ?>
	<div class="tie">
		Tied
	</div>
<? } ?>


<div class="more_links">
	<? echo $this->Html->link('View History Between Players','/matches/history/'.$match['Match']['id']); ?>
</div>

