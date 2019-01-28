<?php
/**
 * Syntax highlighter for home page.
 *
 * @author Ben Tomlin (http://tomlin.no)
 * @version 1.1
 */

// Include config
require_once('config.php');

// Check if filename is specified
if (!isset($_GET['f']) || empty($_GET['f'])) {
    $_SESSION['error'] = 'Error: No file specified.';
    header('Location: ' . BASE_URL);
}

// Check if file exists
if (!file_exists('./resources/files/' . $_GET['f'] . '.txt')) {
    $_SESSION['error'] = 'Error: Specified file does not exist.';
    header('Location: ' . BASE_URL);
}

// Set constants
$file = $_GET['f'] . '.txt';

?>

<!DOCTYPE html>

<html>
<head>

    <title>Ben Christopher Tomlin</title>
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="Home Page of Ben Christopher Tomlin" />
    <meta name="keywords" content="Ben, Christopher, Tomlin, website, home, UiO, IFI,
        Informatikk, Informatics, IT, Course, Courses, Programming, Links, Contact,
        Java, C, PHP, CSS, HTML, Spartan, bct, productions" />
    <meta name="author" content="Ben Christopher Tomlin" />
    
    <link rel="shortcut icon" href="resources/images/favicon.png" />
    <link rel="stylesheet" type="text/css" href="styles.css" />
    
    <link href="http://alexgorbatchev.com/pub/sh/2.1.382/styles/shCore.css" rel="stylesheet" type="text/css" />
    <link href="http://alexgorbatchev.com/pub/sh/2.1.382/styles/shThemeEclipse.css" rel="stylesheet" type="text/css" />

    <script src="http://alexgorbatchev.com/pub/sh/2.1.382/scripts/shCore.js" type="text/javascript"></script>
    <script src="http://alexgorbatchev.com/pub/sh/2.1.382/scripts/shBrushXml.js" type="text/javascript"></script>
    <script src="http://alexgorbatchev.com/pub/sh/2.1.382/scripts/shBrushPhp.js" type="text/javascript"></script>

    <script type="text/javascript">//<![CDATA[
    SyntaxHighlighter.all();
    //]]></script>
    
</head>
<body>

    <div class="standardbox">
        <a class="close float-r" href="<?php echo BASE_URL; ?>">[x]</a>
        <h2>Syntax Highlighting&nbsp;&nbsp;<span style="font-size:x-small">(<?php echo $file; ?>)</span></h2>
        <br/>
        <pre class="brush: php; html-script: true;">
            <?php echo htmlspecialchars(stripslashes(file_get_contents('./resources/files/' . $file))); ?>
        </pre>
    </div>
    
    <div class="copyright">Copyright &copy; Ben Christopher Tomlin</div>

</body>
</html>