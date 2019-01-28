<?php
/*******************************************************************************
* gallery.php                                                                  *
********************************************************************************
* Simple image gallery script                                                  *
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

// Folder containing images (with trailing slash(!))
$folder =  './images/';

// Folder containing thumbnails (with trailing slash(!))
define('THUMBS', 'thumbs/');

// Include SimpleImage resizing script
require_once('ezimage.php');

// Check if custom folder is specified
if (isset($_GET['folder']) && !empty($_GET['folder']))
    $folder = $_GET['folder'] .'/';

// Initialize the SimpleImage class
$image = new EzImage();

// The file array
$files = array();

// Fill an array with file information of contents of $folder
if ($handle = opendir($folder)) {

    while (false !== ($file = readdir($handle))) {
    
        if ($file != "index.html" && $file != "index.php" && !is_dir($folder . $file)) {
            
            if (!file_exists(THUMBS . $file))
                resize($file);
            
            $files[$file] = array(
                'fullname' => $folder . $file,
                'link' => $folder . $file,
                'thumb' => THUMBS . $file,
                'name' => $file,
                'size' => filesize($folder . $file),
                'created' => date("d/m/y", filectime($folder . $file))
            );
        }
    }
    closedir($handle);
}

// Sort array by key (filename)
ksort($files);

// Function for resizing an image and saving it in THUMBS folder
function resize($file)
{
    global $image, $folder;
    
    $image->load($folder . $file);
    if ($image->getHeight() > $image->getWidth()) {
        $image->resizeToHeight(160);
        $image->save(THUMBS . $file);
    } else {
        $image->resizeToWidth(160);
        $image->save(THUMBS . $file);
    }
}

?>

<!DOCTYPE html>

<html>
<head>

    <title>Gallery</title>
    
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="simple javascript gallery" />
    <meta name="keywords" content="gallery, javascript, html5, bct, ben, tomlin" />
    <meta name="author" content="Ben Christopher Tomlin" />
    
    <link rel="shortcut icon" href=""/>
    
    <script type="text/javascript" src="lightbox/js/prototype.js"></script>
    <script type="text/javascript" src="lightbox/js/scriptaculous.js?load=effects,builder"></script>
    <script type="text/javascript" src="lightbox/js/lightbox.js"></script>
    <link rel="stylesheet" href="lightbox/css/lightbox.css" type="text/css" media="screen" />

    <style type="text/css">
    body {
        margin: 0;
        padding: 0;
        background-color: #eee;
        font-family: Verdana, Arial, sans-serif;
        font-size: 11px;
        color: #0f0f1f;
    }
    div.errorbox {
        width: 680px;
        margin: 10px auto;
        padding: 10px;
        border: 1px dotted #900;
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
        width: 80%;
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
    input {
        width: 160px;
        height: 24px;
        margin: 5px auto;
        padding: 0px;
        text-align: center;
        border: 1px solid #ccc;
        background-color: #fafafa;
    }
    input:hover {
        border: 1px solid #a6abff;
    }
    input[type="submit"] {
        font-size: 1em;
    }
    input[type="text"],
    input[type="file"] {
        height: 22px;
    }
    div.container {
        display: inline-block;
        background-color: #fafafa;
        border: 1px solid #eee;
        width: 160px;
        height: 160px;
        padding: 5px;
        margin: 3px 1px;
        text-align: center;
        vertical-align: middle;
    }
    div.inner {
        display: table-cell;
        width: 160px;
        height: 160px;
        vertical-align: middle;
    }
    img.gallery {
        margin: auto;
    }
    a, a:hover {
        text-decoration: none;
    }
    .center {
        text-align: center;
    }
    </style>

</head>
<body>

    <div class="standardbox center">
        <?php foreach($files as $file): ?>
        <div class="container">
            <div class="inner">
                <a href="<?php echo $file['link']; ?>" rel="lightbox[group]" title="<?php echo $file['name']; ?>">
                    <img class="gallery" src="<?php echo $file['thumb']; ?>" alt="<?php echo $file['name']; ?>" />
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="copyright">Copyright &copy; Ben Christopher Tomlin</div>
    
</body>
</html>