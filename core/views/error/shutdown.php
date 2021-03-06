<?view(CORE_ERROR_HEADER)?>

<div>
    <h1><?= $type ?></h1>

    <div class="bodyIcon broken"></div>

    <div class="bodyMessage">
        <?if(config::isDevEnv()):?>
        <? predump("$message \r\n\tin {$model['file']}:{$model['line']}") ?>
        <h5>Params:</h5>
        <? predump($params) ?>
        <h5>Server:</h5>
        <? predump($_SERVER) ?>
        <h5>__FILE__</h5>
        <? predump(__FILE__) ?>
        <?else:?>
        <p><?=$message?></p>
        <?endif?>
    </div>
</div>

<?view(CORE_ERROR_FOOTER)?>