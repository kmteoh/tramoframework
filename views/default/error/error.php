<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Developer Debugger</title>
    <link href="/css/bootstrap.css" rel="stylesheet">

  </head>

  <body>

    <div class="container">

      <div class="masthead" style="border-bottom: 2px solid #ccc;padding-bottom:5px;">
        <h3 class="text-muted"><?=$class?></h3>
      </div>
      
      <div class="row">&nbsp;</div>

      <?predump("$message \r\n\tin $file:$line")?>
      <h5>Stack Trace:</h5>
      <?predump($trace)?>
      <h5>Params:</h5>
      <?predump($params)?>
      <h5>Server:</h5>
      <?predump($_SERVER)?>
      <h5>__FILE__</h5>
      <?predump(__FILE__)?>

      <div class="row">&nbsp;</div>
    </div>

  </body>
</html>
