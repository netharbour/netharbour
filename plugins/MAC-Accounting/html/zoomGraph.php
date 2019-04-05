<script src="js/cropper/lib/prototype.js" type="text/javascript"></script>
<script src="js/cropper/lib/scriptaculous.js?load=builder,dragdrop" type="text/javascript"></script>
<script src="js/cropper/cropper.js" type="text/javascript"></script>
<script src="js/cropper/smokeping-zoom.js" type="text/javascript"></script>
<img id='zoom' src='<?= $this->graphLink ?>'>
<form method='GET' action='' enctype='multipart/form-data' id='range_form'>
    <input type="hidden" name="epoch_start" value="<?= $this->from ?>" id="epoch_start" />
    <input type="hidden" name="rrdfile" value="<?= $this->rrdFileName ?>" id="rrdfile" />
    <input type="hidden" name="type" value="<?= $this->graphType ?>" id="type" />
    <input type="hidden" name="epoch_end" value="<?= $this->to ?>" id="epoch_end" />
    <input type="hidden" name="width" value="900" id="width" />
    <input type="hidden" name="height" value="150" id="height" />
</form>