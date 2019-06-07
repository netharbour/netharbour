<h1><?= $this->header ?></h1>
<form id='configForm' method='post' name='edit_devices'>
    <input type='hidden' name='class' value='<?= $this->value ?>'>
    <input type='hidden' name='id' value='<?= $this->id ?>'>
    <?= $this->netharbourTable ?>
    <div style='clear:both;'></div>
    <input type='submit' class='submitBut' name='<?= $this->buttonName ?>' value='<?= $this->buttonValue ?>'/>
</form>
