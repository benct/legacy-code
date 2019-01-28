<?php
/*******************************************************************************
* pincode.php                                                                  *
********************************************************************************
* Simple pincode login script                                                  *
* Made just for fun, do not use for any actual login purposes..                *
* ============================================================================ *
* Version:                    1.0                                              *
* Software by:                Ben Tomlin                                       *
* Support, News, Updates at:  http://tomlin.no                                 *
********************************************************************************
* This program is free software; you can redistribute it and/or modify it      *
* under the terms of the GNU General Public License as published by the        *
* Free Software Foundation (version 2 or later).                               *
*                                                                              *
* This program is distributed in the hope that it is and will be useful, but   *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY *
* or FITNESS FOR A PARTICULAR PURPOSE.                                         *
*******************************************************************************/

// The pin code
define('PIN', 1234);

// The url/script to redirect to if correct pin is entered
define('FORWARD', 'http://google.com');

// Form submitted
if (isset($_POST['enter']))
{
    if ($_POST['pin'] == PIN)
        header('Location: ' . FORWARD);
    else
        $error = true;
}

?>

<!DOCTYPE html>

<html>
<head>

    <title>Enter Code</title>
    
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="Login" />
    <meta name="keywords" content="login, javascript, html5, bct, ben, tomlin" />
    <meta name="author" content="Ben Christopher Tomlin" />
    
    <link rel="shortcut icon" href=""/>
    
    <script type="text/javascript" src="jquery-1.7.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $(':submit').not('#enter').click(function(){
                $('#pin').val($('#pin').val() + $(this).val());
                return false;
            });
        });
    </script>

    <style type="text/css">
    body {
        margin: 0;
        padding: 0;
        padding-top: 100px;
        background-color: #eee;
        font-family: Verdana, Arial, sans-serif;
        font-size: 11px;
        color: #0f0f1f;
    }
    div.errorbox {
        width: 150px;
        margin: 10px auto;
        padding: 10px;
        border: 1px solid #933;
        background-color: white;
        border-radius: 3px;
        -moz-border-radius: 3px;
        -khtml-border-radius: 3px;
        -webkit-border-radius: 3px;
        box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
        -moz-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
        -webkit-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
        -khtml-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
    }
    div.standardbox {
        width: 150px;
        margin: 10px auto;
        padding: 16px 10px;
        border: 1px solid #bbb;
        background-color: white;
        border-radius: 3px;
        -moz-border-radius: 3px;
        -khtml-border-radius: 3px;
        -webkit-border-radius: 3px;
        box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
        -moz-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
        -webkit-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
        -khtml-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
    }
    div.copyright {
        margin: 10px auto;
        text-align: center;
        font-size: 10px;
        color: #999;
    }
    input, select, textarea {
        height: 21px;
        color: black;
        text-align: center;
        font: 11px 'Lucida Grande', Verdana, Helvetica, sans-serif;
        background: white;
        margin: 2px auto;
        padding: 2px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 2px;
        -moz-border-radius: 2px;
        -khtml-border-radius: 2px;
        -webkit-border-radius: 2px;
    }
    input:hover, select:hover, textarea:hover {
        border: 1px solid rgba(0, 0, 0, 0.2);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    }
    input[type="submit"] {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 2px;
        -moz-border-radius: 2px;
        -khtml-border-radius: 2px;
        -webkit-border-radius: 2px;
        color: #555555;
        cursor: pointer;
        font-size: 11px;
        font-weight: bold;
        width: auto;
        height: 27px;
        line-height: 26px;
        padding: 0 10px;
        text-align: center;
        vertical-align: baseline;
        background-color: #F5F5F5;
        background: -moz-linear-gradient(top , #F5F5F5, #F1F1F1) repeat scroll 0 0 transparent;
        background: -o-linear-gradient(top , #F5F5F5, #F1F1F1) repeat scroll 0 0 transparent;
        background: -ms-linear-gradient(top , #F5F5F5, #F1F1F1) repeat scroll 0 0 transparent;
        background: -webkit-linear-gradient(top , #F5F5F5, #F1F1F1) repeat scroll 0 0 transparent;
        background-image: -webkit-gradient(linear,left top,left bottom,from(#F5F5F5),to(#F1F1F1));
    }
    input[type="submit"]:hover {
        border: 1px solid rgba(0, 0, 0, 0.2);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        color: #333333;
        text-decoration: none;
        background-color: #F8F8F8;
        background: -moz-linear-gradient(top , #F8F8F8, #F1F1F1) repeat scroll 0 0 transparent;
        background: -o-linear-gradient(top , #F8F8F8, #F1F1F1) repeat scroll 0 0 transparent;
        background: -ms-linear-gradient(top , #F8F8F8, #F1F1F1) repeat scroll 0 0 transparent;
        background: -webkit-linear-gradient(top , #F8F8F8, #F1F1F1) repeat scroll 0 0 transparent;
        background-image: -webkit-gradient(linear,left top,left bottom,from(#F8F8F8),to(#F1F1F1));
    }
    a, a:hover {
        text-decoration: none;
    }
    table {
        margin: 0 auto;
    }
    .center {
        text-align: center;
    }
    </style>

</head>
<body>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <?php if (isset($error)): ?>
        <div class="errorbox center">
            Invalid Code
        </div>
        <?php endif; ?>
        <div class="standardbox center">
            <table>
                <tr>
                    <td colspan="3">
                        <input type="password" name="pin" id="pin" value="" readonly="readonly" style="width:90%" />
                    </td>
                <tr>
                    <td>
                        <input type="submit" name="1" value="1" />
                    </td>
                    <td>
                        <input type="submit" name="2" value="2" />
                    </td>
                    <td>
                        <input type="submit" name="3" value="3" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="4" value="4" />
                    </td>
                    <td>
                        <input type="submit" name="5" value="5" />
                    </td>
                    <td>
                        <input type="submit" name="6" value="6" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="7" value="7" />
                    </td>
                    <td>
                        <input type="submit" name="8" value="8" />
                    </td>
                    <td>
                        <input type="submit" name="9" value="9" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="0" value="0" />
                    </td>
                    <td colspan="2">
                        <input type="submit" name="enter" id="enter" value="ENTER" style="width:90%" />
                    </td>
                </tr>
            </table>
        </div>
    </form>
    
    <div class="copyright">Copyright &copy; Ben Christopher Tomlin</div>
    
</body>
</html>