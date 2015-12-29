<?
include '../../library/html_head.php';
if ($_GET['header'] != 'no') {
        render_page_header("Weathermap");
}

?>
<!-- Start of main div -->
        <div id="main">

<!-- Start of main div -->

<?
include 'bcnet-totals.html';
?>
<!-- End of main div -->
</div>
<?
// close the database connection since
// we no longer need it
include '../../library/html_foot.php';
?>

