<html>
	<head>
		<title>Welcome!</title>
	</head>
	<body>
        <h1>Thanks for using Tramo Framework</h1>
        <? /*<p>The book named &lt;<?=$book->name?>&gt; written by <?=$book->author?> is owned by <?=$book->user->firstName?> <?=$book->user->lastName?>,
            who is currently an <b><?=$book->user->status?'active':'inactive'?></b> user.</p> */?>
        <?if($category):?>
        <h2>$category</h2>
		<?$category->categories?>
		<?predump($category)?>
        <?endif?>
        <?if($book):?>
        <h2>$book</h2>
		<?predump($book)?>
        <?endif?>
        <?if($city):?>
        <h2>$city</h2>
		<?predump($city)?>
        <?endif?>
        <?if($user):?>
        <h2>$user</h2>
		<?$user->books?>
		<?predump($user)?>
        <?endif?>
        <?view("_moreInfo",$model)?>
	</body>
</html>	