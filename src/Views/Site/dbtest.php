<p>
    Ran <strong><?= $reads ?></strong> random reads by code from table. Got <strong><?= count($hits) ?></strong> hits
</p>
<p>
    Ran <strong><?= $inserts ?></strong> random inserts and deletes.
</p>
<p>
    Ran <strong><?= $updates ?></strong> random get+update by PK.
</p>
<h5>
    <?= $summary ?>
</h5>