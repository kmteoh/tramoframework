<?php if (!$params->isAjax || !headers_sent()): ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Something is not right...</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <meta name="description" content="Website is not configured."/>
        <link rel="stylesheet" href="/css/error.css"/>
    </head>
    <body>
        <div class="bodyWrapper">
            <div class="bodyHeader">
                <img alt="Tramoframework Core">
            </div>
<?php endif ?>