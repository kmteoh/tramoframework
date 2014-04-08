<html>
    <head>
        <title><?= $title ?> Report</title>
    </head>
    <body>
        <h1>Thanks for using Tramo Test Framework</h1>
        <div class="row">
            <div class="col-md-4">
                <table class="table table-striped">
                    <tr>
                        <th>Total (Case / Unit)</th>
                        <td><?= $totalCases ?> / <?= $totalUnits ?></td>
                    </tr>
                    <tr>
                        <th>Total Passed</th>
                        <td><?= $totalPassed ?></td>
                    </tr>
                    <tr>
                        <th>Total Failed</th>
                        <td><?= $totalFailed ?></td>
                    </tr>
                    <tr>
                        <th>Total Skipped</th>
                        <td><?= $totalSkipped ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?= $totalFailed ? 'Fail' : 'Pass' ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <? if ($totalFailed): ?>
            <h3>Failed</h3>
            <div class="row">
                <div class="col-md-12">
                    <?= arrayToTable($report) ?>
                </div>
            </div>
        <? endif ?>
    </body>
</html>	