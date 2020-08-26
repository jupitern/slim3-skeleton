<html>
<head>
    <style type="text/css">
        html, body, #wrapper {
            height:90%;
            width: 100%;
            margin: 0;
            padding: 0;
            border: 0;
        }
        #wrapper td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
<table id="wrapper">
    <tr>
        <td>
            <h1>Error <?= isset($error['code']) ? $error['code'] : 500 ?>!</h1>
            <?php
            if (isset($error['error']) && is_string($error['error'])) {
                echo $error['error'];
            }
            elseif (isset($error['error']) && is_array($error['error'])) {
                foreach ($error['error'] as $msg) echo $msg."<br/>";
            }
            ?>
        </td>
    </tr>
</table>
</body>
</html>