<?php
/********************************************************************************
 * mcrails.php                                                                  *
 ********************************************************************************
 * Minecraft (1.2.5) rails and minecart resource calculator                     *
 *  + minor fix (1.7.2)                                                         *
 * ============================================================================ *
 * Version:                    1.0.1                                            *
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

$button = array(
    'stone' => 1,
);

$pressure = array(
    'stone' => 2,
);

$rail = array(
    'iron' => (6/16),
    'stick' => (1/16),
);

$powered = array(
    'gold' => 1,
    'stick' => (1/6),
    'redstone' => (1/6),
);

$detector = array(
    'iron' => 1,
    'pressure' => (1/6),
    'redstone' => (1/6),
);

$torch = array(
    'stick' => 1,
    'redstone' => 1,
);

$endstation = array(
    'length' => 3,
    'powered' => 3,
    'button' => 1,
);

$midstation = array(
    'length' => 5,
    'powered' => 4,
    'detector' => 1,
    'button' => 2,
);

if (isset($_POST['calc']))
{

    // Check errors in input
    $error = (!is_numeric($_POST['midstation']) || !is_numeric($_POST['endstation']) || !is_numeric($_POST['powerinterval']));
    $error = ($error || ($_POST['distance'] == 1 && !is_numeric($_POST['length'])));
    $error = ($error || ($_POST['distance'] == 2 && (!is_numeric($_POST['x1']) || !is_numeric($_POST['x2']) || !is_numeric($_POST['z1']) || !is_numeric($_POST['z2']))));

    // Get or calculate length
    if ($_POST['distance'] == 1) {
        $length = $_POST['length'];
    } else {
        $x = abs($_POST['x1'] - $_POST['x2']);
        $z = abs($_POST['z1'] - $_POST['z2']);
        $length = $x + $z;
    }

    // Check for errors in powered rail interval input
    $error = ($error || (isset($_POST['enablepower']) && ($_POST['powerinterval'] == 0 || $_POST['powerinterval'] > $length)));

    // If no error, calculate resources needed
    if (!$error)
    {
        $totalmid = $_POST['midstation'];
        $totalend = $_POST['endstation'];
        $powint = $_POST['powerinterval'];

        if (!isset($_POST['enableend'])) {
            $totalend = 0;
        }
        if (!isset($_POST['enablemid'])) {
            $totalmid = 0;
        }

        $tmprails = $length - ($totalmid * $midstation['length']) - ($totalend * $endstation['length']);

        if (!isset($_POST['enablepower'])) {
            $tmppowered = 0;
        } else {
            $tmppowered = ceil($tmprails / $powint);
        }

        $numRails = $tmprails - $tmppowered;
        $numPowered = $tmppowered + ($totalmid * $midstation['powered']) + ($totalend * $endstation['powered']);
        $numDetector = ($totalmid * $midstation['detector']);
        $numTorch = $tmppowered;
        $numButton = ($totalmid * $midstation['button']) + ($totalend * $endstation['button']);
        $numPressure = ($numDetector * $detector['pressure']);

        $numIron = ($numRails * $rail['iron']) + ($numDetector * $detector['iron']);
        $numGold = ($numPowered * $powered['gold']);
        $numRedstone = ($numPowered * $powered['redstone']) + ($numDetector * $detector['redstone']) + ($numTorch * $torch['redstone']);
        $numSticks = ($numRails * $rail['stick']) + ($numPowered * $powered['stick']) + ($numTorch * $torch['stick']);
        $numStone = ($numButton * $button['stone']) + ($numPressure * $pressure['stone']);
    }
}

?>

<!DOCTYPE html>

<html>
<head>

    <title>Minecraft Rail Calculator</title>

    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="Minecraft Rail Calculator" />
    <meta name="keywords" content="minecraft, 1.2.5, 1.3, 1.4.7, 1.5.2, 1.6.4, 1.7.2, rail, minecart, calculator, powered, detector, redstone, javascript, html5, bct, ben, tomlin" />
    <meta name="author" content="Ben Christopher Tomlin" />

    <link rel="shortcut icon" href="../images/help.png"/>

    <script type="text/javascript" src="jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="tooltip.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $(".distance").change(function(){
                if ($(this).val() == 1) {
                    $(".length").removeAttr('disabled');
                    $(".coord").attr('disabled', 'disabled');
                } else {
                    $(".length").attr('disabled', 'disabled');
                    $(".coord").removeAttr('disabled');
                }
            });
            $("input[@name=distance]:checked").change();
        });
    </script>

    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            padding-top: 100px;
            background-color: #eee;
            background-image: url('../images/creeper_bg.png');
            background-size: 100%;
            font-family: Verdana, Arial, sans-serif;
            font-size: 11px;
            color: #0f0f1f;
        }
        div.errorbox {
            width: 300px;
            opacity:0.99;
            filter:alpha(opacity=99);
            margin: 10px auto;
            padding: 10px;
            border: 1px solid #933;
            background-color: white;
            border-radius: 3px;
            -moz-border-radius: 3px;
            -khtml-border-radius: 3px;
            -webkit-border-radius: 3px;
            box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
            -moz-box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
            -webkit-box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
            -khtml-box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
        }
        div.standardbox {
            width: 820px;
            opacity:0.99;
            filter:alpha(opacity=99);
            margin: 10px auto;
            padding: 16px 10px;
            border: 1px solid #bbb;
            background-color: white;
            border-radius: 3px;
            -moz-border-radius: 3px;
            -khtml-border-radius: 3px;
            -webkit-border-radius: 3px;
            box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
            -moz-box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
            -webkit-box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
            -khtml-box-shadow: rgba(20,20,20,0.7) 0 4px 10px -1px;
        }
        div.copyright {
            margin: 10px auto;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        input, select, textarea {
            width: 40px;
            height: 21px;
            color: #444;
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
            text-align: left;
        }
        hr {
            border: none;
            border-top: 1px solid #ccc;
        }
        .center {
            text-align: center;
        }
        .small {
            font-size: 9px;
            color: #999;
        }
        .large {
            font-size: 17px;
        }
        .help {
            cursor: pointer;
            vertical-align: middle;
            opacity: 0.6;
            margin-left: 8px;
        }
        #tt {
            position: absolute;
            display: block;
            z-index: 99;
            color: #1B1B1B;
            background-color: #f0f0f0;
            font-size: 9px;
            padding: 3px 6px;
            border: 1px solid #b1b1b1;
            border-radius: 3px;
            -moz-border-radius: 3px;
            -webkit-border-radius: 3px;
            -khtml-border-radius: 3px;
            box-shadow: inset 1px 1px 2px rgba(200,200,200,0.2);
            -moz-box-shadow: inset 1px 1px 2px rgba(200,200,200,0.2);
            -webkit-box-shadow: inset 1px 1px 2px rgba(200,200,200,0.2);
            -khtml-box-shadow: inset 1px 1px 2px rgba(200,200,200,0.2);
        }
    </style>

</head>
<body>

<?php if (isset($error) && $error): ?>
    <div class="errorbox center">
        Invalid input. Please enter numeric values.
    </div>
<?php endif; ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="standardbox center">
        <table>
            <tr>
                <td colspan="3"><span class="large">&nbsp;Minecraft Rail Calculator</span>&nbsp;<span class="small">(Minecraft 1.2.5 - 1.7.2)</span><hr/></td>
            </tr>
            <tr>
                <td><input type="radio" name="distance" class="distance" value="1" <?php echo isset($_POST['distance']) && $_POST['distance'] == 2 ? '' : 'checked="checked"'; ?> /></td>
                <td>Enter total track length in blocks ...</td>
                <td><input type="text" name="length" class="length" value="<?php echo isset($_POST['length']) ? $_POST['length'] : ''; ?>" autocomplete="off" /></td>
            </tr>
            <tr>
                <td><input type="radio" name="distance" class="distance" value="2" <?php echo isset($_POST['distance']) && $_POST['distance'] == 2 ? 'checked="checked"' : ''; ?> /></td>
                <td>... or enter starting and ending coordinates</td>
                <td>
                    <input type="text" name="x1" class="coord" value="<?php echo isset($_POST['x1']) ? $_POST['x1'] : ''; ?>" autocomplete="off" /> X &nbsp;
                    <input type="text" name="z1" class="coord" value="<?php echo isset($_POST['z1']) ? $_POST['z1'] : ''; ?>" autocomplete="off" /> Z &nbsp; <span class="small">Start</span><br/>
                    <input type="text" name="x2" class="coord" value="<?php echo isset($_POST['x2']) ? $_POST['x2'] : ''; ?>" autocomplete="off" /> X &nbsp;
                    <input type="text" name="z2" class="coord" value="<?php echo isset($_POST['z2']) ? $_POST['z2'] : ''; ?>" autocomplete="off" /> Z &nbsp; <span class="small">End</span>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" name="enablepower" id="enablepower" value="1" <?php echo (isset($_POST['calc']) && !isset($_POST['enablepower'])) ? '' : 'checked="checked"'; ?> /></td>
                <td>Powered rail once every _ block <span class="small">(Default: 38)</span> &nbsp; </td>
                <td>
                    <input type="text" name="powerinterval" value="<?php echo isset($_POST['powerinterval']) ? $_POST['powerinterval'] : '38'; ?>" autocomplete="off" />
                    <img class="help" src="../images/help.png" alt="?" onmouseover="tooltip.show('Untick to disable powered rails. This number must be lower than the total length (but not zero).<br/><br/>Powered rails are needed at certain intervals to power manned and unmanned minecarts. These are often powered by one redstone torch each, which are also added to the total costs.<br/><br/>One powered rail every 38 blocks is proven to be the most cost and speed effective at the moment. (http://www,minecraftwiki.net)');" onmouseout="tooltip.hide();" />
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" name="enableend" id="enableend" value="1" <?php echo (isset($_POST['calc']) && !isset($_POST['enableend'])) ? '' : 'checked="checked"'; ?> /></td>
                <td>Total number of end stations <span class="small">(Default: 2)</span></td>
                <td>
                    <input type="text" name="endstation" value="<?php echo isset($_POST['endstation']) ? $_POST['endstation'] : '2'; ?>" autocomplete="off" />
                    <img class="help" src="../images/help.png" alt="?" onmouseover="tooltip.show('An end station consists of three powered rails in a row ajacent to a wall, and a button to activate these. Three powered rails are needed to get a manned minecart to full speed (8m/s). <img src=\'../images/endstation.png\' style=\'width:300px;margin-top:5px;\' />');" onmouseout="tooltip.hide();" />
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" name="enablemid" id="enablemid" value="1" <?php echo (isset($_POST['calc']) && !isset($_POST['enablemid'])) ? '' : 'checked="checked"'; ?> /></td>
                <td>Total number of middle stations <span class="small">(Default: 0)</span></td>
                <td>
                    <input type="text" name="midstation" value="<?php echo isset($_POST['midstation']) ? $_POST['midstation'] : '0'; ?>" autocomplete="off" />
                    <img class="help" src="../images/help.png" alt="?" onmouseover="tooltip.show('An middle (stopping) station consists of four powered rails with a detector rail in the middle and two buttons, where two of the powered rails are sloped. This causes the minecart to temporarily stop, and can be started again by pressing the button closest to you on the wall. <img src=\'../images/midstation.png\' style=\'width:300px;margin-top:5px;\' />');" onmouseout="tooltip.hide();" />
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="12"><hr/> &nbsp; <span class="small">Total items needed</span></td>
            </tr>
            <tr>
                <td>Rails</td>
                <td><input type="text" value="<?php echo isset($numRails) ? $numRails : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Powered</td>
                <td><input type="text" value="<?php echo isset($numPowered) ? $numPowered : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Detector</td>
                <td><input type="text" value="<?php echo isset($numDetector) ? $numDetector : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>RS Torches</td>
                <td><input type="text" value="<?php echo isset($numTorch) ? $numTorch : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Buttons</td>
                <td><input type="text" value="<?php echo isset($numButton) ? $numButton : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Pr.Plates</td>
                <td><input type="text" value="<?php echo isset($numPressure) ? $numPressure : 0; ?>" disabled="disabled" /></td>
            </tr>
            <tr>
                <td colspan="12"><hr/> &nbsp; <span class="small">Total resources needed to craft these items</span></td>
            </tr>
            <tr>
                <td>Iron</td>
                <td><input type="text" value="<?php echo isset($numIron) ? $numIron : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Gold</td>
                <td><input type="text" value="<?php echo isset($numGold) ? $numGold : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>RS Dust</td>
                <td><input type="text" value="<?php echo isset($numRedstone) ? $numRedstone : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Sticks</td>
                <td><input type="text" value="<?php echo isset($numSticks) ? $numSticks : 0; ?>" disabled="disabled" /> &nbsp;&nbsp;&nbsp; </td>
                <td>Stone</td>
                <td><input type="text" value="<?php echo isset($numStone) ? $numStone : 0; ?>" disabled="disabled" /></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="12" class="center">
                    <br/><input type="submit" name="calc" value="Calculate" />
                </td>
            </tr>
        </table>
    </div>
</form>

<div class="copyright">Copyright &copy; Ben Christopher Tomlin</div>

</body>
</html>
