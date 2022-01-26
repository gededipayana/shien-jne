<?php

require_once('../../../../wp-config.php');

$curl = curl_init();

$wc_settings = get_option('woocommerce_shein_settings');

curl_setopt_array($curl, array(
    CURLOPT_URL => $wc_settings['endpoint'] . '/tracing/api/list/v1/cnote/' . $_GET['cnote_no'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'username=' . $wc_settings['username'] . '&api_key=' . $wc_settings['apikey'],
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
));

$response = curl_exec($curl);

curl_close($curl);

?>

<div id="myModal" class="modal">

    <div class="modal-content">
        <span class="close">&times;</span>

        <div class="content-left">
            <?php echo $response; ?>
        </div>
    </div>

</div>

<style>
.modal {
    display: block;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0, 0, 0);
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    position: relative;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 0;
    right: 15px;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<script>
jQuery(document).ready(function($) {
    var modal = document.getElementById( 'myModal' );
    var span = document.getElementsByClassName( 'close' )[0];

    span.onclick = function() {
        modal.remove();
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.remove();
        }
    }
});
</script>
