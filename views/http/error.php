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
                <h1>Error <?= isset($code) ? $code : 500 ?>!</h1>
                <?php
                if (isset($error) && is_string($error)) {
                    echo $error;
                }
                elseif (isset($error) && is_array($error)) {
                    foreach ($error as $msg) echo $msg."<br/>";
                }

                if (isset($messages) && is_array($messages)) {
                    foreach ($messages as $msg) echo $msg."<br/>";
                }
                ?>
            </td>
        </tr>
    </table>
    
</body>
</html>
