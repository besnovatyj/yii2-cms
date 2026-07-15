<style>
    html {
        background: #fff;
        margin:0;
        padding:0;
    }
    body {
        color: #233452;
        height: 100%;
        font-family: Verdana, Helvetica, sans-serif;
        margin: 20px 0;
        padding: 0;
    }

    table {
        width: 730px;
        border: 1px solid #ccc;
        background: #fff;
        padding: 1px;
    }

    td, th {
        border: 1px solid #FFF;
        font-family: Verdana, sans-serif;
        font-size: 12px;
        padding:4px 8px;
    }

    h1 {
        font-family: "Trebuchet MS", Helvetica, sans-serif;
        font-size: 24px;
        margin: 10px;
    }

    h2 {
        font-family: "Trebuchet MS", Helvetica, sans-serif;
        font-size: 22px;
        color: #0B5FB4;
        text-align: left;
        margin: 25px auto 5px auto;
        width: 724px;
    }

    hr {
        background-color: #A9A9A9;
        color: #A9A9A9;
    }

    .e, .v, .vr {
        color: #333;
        font-family: Verdana, Helvetica, sans-serif;
        font-size: 11px;
    }
    .e {
        background-color: #eee;
    }
    .h {
        background-color: #0B5FB4;
        color: #fff;
    }
    .v {
        background-color: #F1F1F1;
        -ms-word-break: break-all;
        word-break: break-all;
        word-break: break-word;
        -webkit-hyphens: auto;
        -moz-hyphens: auto;
        hyphens: auto;
    }
    img {
        display:none;
    }
</style>
<?php
ob_start();
phpinfo();
$pInfo = ob_get_contents();
ob_end_clean();

$pInfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pInfo);
echo $pInfo;
?>
