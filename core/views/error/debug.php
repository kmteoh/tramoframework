<?php view(CORE_ERROR_HEADER)?>

<div>
    <h1><?= $class ?></h1>

    <div class="bodyIcon broken"></div>

    <div class="bodyMessage">
        <?php if(config::isDevEnv()):?>
        <?php predump("$message \r\n\tin {$model['file']}:{$model['line']}") ?>
        <h5>Params:</h5>
        <?php predump($params) ?>
        <h5>Server:</h5>
        <?php predump($_SERVER) ?>
        <h5>__FILE__</h5>
        <?php predump(__FILE__) ?>
        <?php else:?>
        <p>$message</p>
        <?php endif?>
    </div>
</div>

<?php view(CORE_ERROR_FOOTER)?>