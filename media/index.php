<?php
/**
 * Parse windows directory and file info.
 *
 * @author Ben Tomlin (http://tomlin.no)
 * @version 1.0
 */

$filesystem = array(
    'e' => array(
        'dir' => 'E:\\',
        'title' => 'TV-Shows',
        'size' => '1.5 TB',
        'contents' => array(),
    ),
    'f' => array(
        'dir' => 'F:\\',
        'title' => 'Movies',
        'size' => '1.0 TB',
        'contents' => array(),
    ),
    'g' => array(
        'dir' => 'G:\\',
        'title' => 'Movies HD',
        'size' => '1.0 TB',
        'contents' => array(),
    ),
);

// Call parser function to parse each drive
parse('e.txt', 'e');
parse('f.txt', 'f');
parse('g.txt', 'g', 2);


/**
 * Parses a file containing disk content data
 * (!!) Get this file by running "dir /S /B /A:-D-H-S" on the desired disk
 */
function parse($filename, $letter, $shift = 1)
{
    global $filesystem;
    
    // Read the file to array
    $file = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Parse each line in the file
    foreach ($file as $line)
    {
        // Split string by file divider (\)
        $exp = explode('\\', $line);
        
        // Remove the first x number of elements (to skip drive letter or folders)
        $temp = $shift;
        while ($temp > 0)
        {
            array_shift($exp);
            $temp--;
        }
        
        // Organize directory and file information in a tree structure
        switch(count($exp))
        {
            case 1:
                $filesystem[$letter]['contents'][] = $exp[0];
                break;
            case 2:
                $filesystem[$letter]['contents'][$exp[0]][] = $exp[1];
                break;
            case 3:
                $filesystem[$letter]['contents'][$exp[0]][$exp[1]][] = $exp[2];
                break;
            case 4:
                $filesystem[$letter]['contents'][$exp[0]][$exp[1]][$exp[2]][] = $exp[3];
                break;
            default:
                // Care...
                break;
        }
    }
}

/**
 * Loops through each element in an array.
 * If element again is array, some html is echoed and the function is recalled.
 * If not, result is echoed with some html.
 */
function traverse($array)
{
	foreach($array as $key => $value)
	{
		if (is_array($value)) {
            
            $keyid = rand();
            
            echo '
            <div class="title" onclick="$(\'#' . $keyid . '\').toggle();"><img src="folder.png" style="width:16px;height:16px;" /> ' . $key . '</div>
            <div class="tab">
                <div id="' . $keyid . '" ' . ($key != 'Movies' ? 'style="display:none"' : '') . '>';
            
			traverse($value);
            
            echo '</div></div>';
            
		} else {
            
            if (strtolower(substr($value, -3)) == 'avi')
                $icon = 'vlc.png';
            else if (strtolower(substr($value, -3)) == 'mkv')
                $icon = 'mkv.png';
            else if (strtolower(substr($value, -3)) == 'mp4')
                $icon = 'mp4.gif';
            else if (strtolower(substr($value, -3)) == 'wmv' || strtolower(substr($value, -3)) == 'mpg')
                $icon = 'wmv.gif';
            else
                $icon = 'unknown.png';
            
			echo '<img class="media" src="' . $icon . '" style="width:16px;height:16px;" /> ' . $value . '<br/>';
		}
	}
}

?>

<!DOCTYPE html>

<html>
<head>

    <title>dir /S /B /A:-D-H-S</title>
    
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="" />
    <meta name="keywords" content="javascript, html5, bct, ben, tomlin" />
    <meta name="author" content="Ben Christopher Tomlin" />
    
    <link rel="shortcut icon" href="disk.png"/>
    
    <script type="text/javascript" src="jquery-1.7.min.js"></script>

    <style type="text/css">
    body {
        margin: 0;
        padding: 0;
        background-color: #eee;
        font-family: Verdana, Arial, sans-serif;
        font-size: 11px;
        color: #0f0f1f;
    }
    div.wrapper {
        margin: 0 auto;
        text-align: center;
    }
    div.box {
        display: inline-block;
        vertical-align: top;
        text-align: left;
        width: 480px;
        margin: 10px auto;
        padding: 10px;
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
        width: 480px;
        margin: 10px auto;
        text-align: center;
        font-size: 10px;
        color: #999;
    }
    img {
        vertical-align: middle;
    }
    a, a:hover {
        text-decoration: none;
    }
    .center {
        text-align: center;
    }
    .header {
        font-size: 18px;
        text-align: center;
        margin-bottom: 5px;
    }
    .tab {
        overflow-x: hidden;
        padding-left: 20px;
    }
    .title {
        cursor: pointer;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .media {
        margin-bottom: 2px;
    }
    </style>

</head>
<body>

    <div class="wrapper">
    <?php foreach($filesystem as $key => $disk): ?>
        
        <?php if ($key == 'errors') continue; ?>
        
        <div class="box">
            <div class="header">
                <img src="disk.png" style="width:24px;height:24px;" /> <?php echo $disk['dir']; ?> <?php echo $disk['title']; ?> <span style="font-size:12px">(<?php echo $disk['size']; ?>)</span>
            </div>
            <?php traverse($disk['contents']); ?>
        </div>
        
    <?php endforeach; ?>
    </div>
    
    <div class="copyright">Copyright &copy; Ben Tomlin</div>
    
</body>
</html>