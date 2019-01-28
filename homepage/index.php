<?php

// Include config.
require_once('config.php');

// Check login details if submitted.
if (isset($_POST['login']))
{
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    
    if (empty($user) || empty($pass)) {
        $_SESSION['error'] = 'Error: Enter both a username and a password when logging in.';
    } else if ($user != USERNAME || md5(md5($pass)) != PASSWORD) {
        $_SESSION['error'] = 'Error: Specified username or password was incorrect.';
    } else {
        $val = md5(USERNAME . PASSWORD);
        setcookie('bct', $val, time() + 7200, '/', '.tomlin.no');
        header('Location: ' . BASE_URL);
        exit;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Log out if specified.
if (isset($_GET['logout']))
{
    setcookie('bct', '', time() - 3600, '/', '.tomlin.no');
    header('Location: ' . BASE_URL);
    exit;
}

// Contact form submitted.
if (isset($_POST['contact_submit'])) {

    // Check fields
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
        $_SESSION['error'] = 'Error: Please fill out all the required fields!';
    // Validate email
    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Error: Invalid email address!';
    // Compose email fields
    } else {
        
        // Specify email subject.
        $subject = "Message from " . SITE_NAME;
        
        // Build email body (message).
        $body = "New contact form message from " . SITE_NAME . "\n\nFrom: {$_POST['name']}\nEmail: {$_POST['email']}\n\n{$_POST['message']}";
    
        // Send email to webmaster.
        sendEmail(WEBMASTER_EMAIL, $_POST['email'], $subject, $body);
        
        // Redirect to this page again.
        header('Location: ' . BASE_URL);
        exit;
    }
}

?>

<!DOCTYPE html>

<html>
<head>

    <title>Ben Christopher Tomlin</title>
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="Home Page of Ben Christopher Tomlin" />
    <meta name="keywords" content="Ben, Christopher, Tomlin, website, home, UiO, IFI,
        Informatikk, Informatics, IT, Course, Courses, Programming, Links, Contact,
        Java, C, PHP, CSS, HTML" />
    <meta name="author" content="Ben Christopher Tomlin" />
    
    <link rel="shortcut icon" href="resources/images/favicon.png" />
    <link rel="stylesheet" type="text/css" href="styles.css" />
    
    <script type="text/javascript" src="resources/js/jquery-1.7.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            // Navigation content selector
            var current = '#home';
            var active = false;
            $('.nav').click(function(){
                if (active) return;
                active = true;
                var content = $(this).attr('title');
                $('.nav').removeClass('active');
                $(this).addClass('active');
                $(current).animate({opacity: 'toggle', height: 'toggle'}, 'slow', function(){
                    $(content).animate({opacity: 'toggle', height: 'toggle'}, 'slow');
                    current = content;
                    active = false;
                });
            });
            // Refresh quotes
            $('.refresh').click(function(){
                $.get('resources/quotes.txt', function(data){
                    var lines = data.split('\n');
                    var line = lines[Math.floor(Math.random()*lines.length)];
                    $('.quote').fadeOut('slow', function(){
                        $('.quote').html(line);
                        $('.quote').fadeIn('slow');
                    });
                });
            });
            // Show full size images on click
            $('.blowup').click(function(){
                var source = $(this).attr('src');
                $('#container').height($(document).height());
                if ($('#container').is(":visible")) {
                    $('#container').fadeOut('slow', function(){
                        $('#image').attr('src', source);
                        $('#container').fadeIn('slow');
                    });
                } else {
                    $('#image').attr('src', source);
                    $('#container').fadeIn('slow');
                }
                $('#image').css('margin-top', (($(window).height() - $('#image').outerHeight()) / 2) + $(window).scrollTop() + 'px');
            });
            // Hide image container on click
            $('#container').click(function(){
                $(this).fadeOut('slow');
            });
            // Show login container on click
            $('#login').click(function(){
                $('#login-container').height($(document).height());
                $('#login-container').fadeIn('slow');
                $('#login-box').css('margin-top', (($(window).height() - $('#login-box').outerHeight()) / 2) + $(window).scrollTop() + 'px');
            });
            // Hide login container on click
            $('#login-close').click(function(){
                $('#login-container').fadeOut('slow');
            });
        });
    </script>
    
</head>
<body>

    <noscript><div class="errorbox">You have either disabled JavaScript or your browser does not support it.<br/>Some features on this site require JavaScript to function properly.</div></noscript>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="errorbox">
        <?php echo $_SESSION['error']; ?>
        <?php unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <div class="standardbox" style="margin-top:40px">
        <div class="navigation">
            <span class="nav active" title="#home" onclick="//window.location.hash='home';">Home</span> | 
            <span class="nav" title="#courses" onclick="//window.location.hash='courses';">Courses</span> | 
            <span class="nav" title="#work" onclick="//window.location.hash='work';">Work</span> | 
            <span class="nav" title="#contact" onclick="//window.location.hash='contact';">Contact</span> |
            <?php if (ADMIN): ?>
            <a class="navg" href="http://files.tomlin.no">Files</a> |
            <a class="navg" href="?logout">Logout</a>
            <?php else: ?>
            <span class="navg" id="login">Login</span>
            <?php endif; ?>
        </div>
        <h2>Ben Christopher Tomlin</h2>
    </div>

    <div class="standardbox">
        <table width="100%">
            <tr>
                <td colspan="2">Master of Science (Informatics) from the University of Oslo.<br/>Thesis written at Simula Research Laboratory.</td>
                <td rowspan="5" class="center"><img class="me blowup" src="resources/images/ben.jpg"></img></td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td>ben&#64;tomlin.no</td>
            </tr>
            <tr>
                <td>Telephone</td>
                <td>Ask via contact form</td>
            </tr>
            <tr>
                <td>Facebook</td>
                <td><a href="http://facebook.com/ben.c.tomlin" target="_blank">http://facebook.com/ben.c.tomlin</a></td>
            </tr>
            <tr>
                <td>LinkedIn</td>
                <td><a href="http://linkedin.com/in/bentomlin" target="_blank">http://linkedin.com/in/bentomlin</a></td>
            </tr>
        </table>
    </div>
    
    <div class="blankbox content" id="home" style="display:block">
        
        <div class="standardbox narrow float-l">
            <div class="clear center">Higher Education</div>
            <div class="icons"><img class="icon" src="resources/images/uio.png" alt="" /></div>
            <div class="text">Master's Degree in Informatics<br/>University of Oslo<br/>August 2010 - November 2013</div>
            <div class="icons"><img class="icon" src="resources/images/uio.png" alt="" /></div>
            <div class="text">Bachelor's Degree in Informatics<br/>University of Oslo<br/>August 2007 - June 2010</div>
            <div class="icons"><img class="icon" src="resources/images/valler.png" alt="" /></div>
            <div class="text">Generell Studiekompetanse (A-levels)<br/>Valler Videreg�ende Skole<br/>August 2004 - June 2007</div>
        </div>
        
        <div class="standardbox narrow float-r">
            <div class="clear center">Work Experience</div>
            <div class="icons"><img class="icon" src="resources/images/bf.png" alt="" /></div>
            <div class="text">Server Administration and Management<br/>Foreningen Battlefield.no<br/>January 2011 - February 2014</div>
            <div class="icons"><img class="icon" src="resources/images/fugro.png" alt="" /></div>
            <div class="text">IT Support Temp<br/>Fugro Norway AS<br/>June 2011 - July 2011</div>
            <div class="icons"><img class="icon" src="resources/images/uio.png" alt="" /></div>
            <div class="text">TA in Computer Communications<br/>University of Oslo<br/>January 2011 - June 2011</div>
        </div>
        
        <div class="clear"></div>
        
        <div class="standardbox" style="margin:5px auto;">
            <div class="clear center">Programming Knowledge</div>
            
            <div class="narrower float-l">
                <img class="narrowicon" src="resources/images/code/coding.png" alt="" />
                <div class="narrowtext">Java and<br/>Swing Applications</div>
            </div>
            
            <div class="narrower float-l">
                <img class="narrowicon" src="resources/images/code/script.png" alt="" />
                <div class="narrowtext">C and<br/>C++</div>
            </div>
            
            <div class="narrower float-r">
                <img class="narrowicon" src="resources/images/code/sql.png" alt="" />
                <div class="narrowtext">Structured<br/>Query<br/>Language</div>
            </div>
            
            <div class="narrower float-r">
                <img class="narrowicon" src="resources/images/code/php.png" alt="" />
                <div class="narrowtext">PHP:<br/>Hypertext<br/>Preprocessor</div>
            </div>
            
            <div class="narrower float-l">
                <img class="narrowicon" src="resources/images/code/html.png" alt="" />
                <div class="narrowtext">HyperText<br/>Markup<br/>Language</div>
            </div>
            
            <div class="narrower float-l">
                <img class="narrowicon" src="resources/images/code/css.png" alt="" />
                <div class="narrowtext">Cascading<br/>Style<br/>Sheets</div>
            </div>
            
            <div class="narrower float-r">
                <img class="narrowicon" src="resources/images/code/shield.png" alt="" />
                <div class="narrowtext">Linux and<br/>Windows</br>Systems</div>
            </div>
            
            <div class="narrower float-r">
                <img class="narrowicon" src="resources/images/code/js.png" alt="" />
                <div class="narrowtext">JavaScript /<br/>jQuery</div>
            </div>
            
            <div class="clear"></div>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <div class="blankbox content" id="courses" style="display:none">
    
        <div class="standardbox narrow float-l" style="margin:0">
            <img src="resources/images/uio/ifi.png" alt="Department of Informatics" /><br/>
            <span class="mono">INF1000</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF1000/" target="_blank">Basic Object Oriented Programming</a><br/>
            <span class="mono">INF1010</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF1010/" target="_blank">Object Oriented Programming</a><br/>
            <span class="mono">INF1040</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF1040/" target="_blank">Digital Representation</a><br/>
            <span class="mono">INF1050</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF1050/" target="_blank">System Development</a><br/>
            <span class="mono">INF1060</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF1060/" target="_blank">Introduction to Operating Systems</a><br/>
            <span class="mono">INF1300</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF1300/" target="_blank">Introduction to Database Systems</a><br/>
            <span class="mono">INF2100</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF2100/" target="_blank">Project Assignment in Programming</a><br/>
            <span class="mono">INF2220</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF2220/" target="_blank">Algorithms and Structures in Java</a><br/>
            <span class="mono">INF2270</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF2270/" target="_blank">Computer Architecture</a><br/>
            <span class="mono">INF3100</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF3100/" target="_blank">Database Systems</a><br/>
            <span class="mono">INF3190</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF3190/" target="_blank">Computer Communications</a><br/>
            <span class="mono">INF3290</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF3290/" target="_blank">Large and Complex Informationsystems</a><br/>
            <span class="mono">INF3510</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF3510/" target="_blank">Information Security</a><br/>
            <span class="mono">INF4151</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF4151/" target="_blank">Operating Systems</a><br/>
            <span class="mono">INF5040</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF5040/" target="_blank">Open Distibuted Processes</a><br/>
            <span class="mono">INF5100</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF5100/" target="_blank">Advanced Database Systems</a><br/>
            <span class="mono">INF5270</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF5270/" target="_blank">Design of Interactive Websites</a><br/>
            <span class="mono">INF5750</span> - <a href="http://www.uio.no/studier/emner/matnat/ifi/INF5750/" target="_blank">Open Source Programming</a>
        </div>
        
        <div class="standardbox narrow float-r">
            <img src="resources/images/uio/math.png" alt="Department of Mathematics" /><br/>
            <span class="mono">STK1000</span> - <a href="http://www.uio.no/studier/emner/matnat/math/STK1000/" target="_blank">Applied Statistics</a><br/>
            <span class="mono">MAT1000</span> - <a href="http://www.uio.no/studier/emner/matnat/math/MAT1000/" target="_blank">Mathematics 1</a><br/>
            <span class="mono">MAT1030</span> - <a href="http://www.uio.no/studier/emner/matnat/math/MAT1030/" target="_blank">Discrete Mathematics</a>
        </div>
        
        <div class="standardbox narrow float-r" style="margin-top:5px;">
            <img src="resources/images/uio/ita.png" alt="Institute of Theoretical Astrophysics" /><br/>
            <span class="mono">AST1010</span> - <a href="http://www.uio.no/studier/emner/matnat/astro/AST1010/" target="_blank">Astronomy - A Cosmic Journey</a>
        </div>
        
        <div class="standardbox narrow float-r" style="margin-top:5px;">
            <img src="resources/images/uio/ifikk.png" alt="Department of Philosophy, Classics, History of Art and Ideas" style="width:100%" /><br/>
            <span class="mono">EXPHIL03</span> - <a href="http://www.uio.no/studier/emner/hf/ifikk/EXPHIL03/" target="_blank">Examen Philosophicum</a>
        </div>
        
        <div class="standardbox narrow float-r" style="margin-top:5px;">
            <i>Courses are ordered by department and course code and not by the order in which completed.</i>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <div class="blankbox content" id="work" style="display:none">
        
        <div class="standardbox work" style="margin-bottom:10px;">
            <div class="clear center">Web Development</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/master.png" alt="" /></div>
            <div class="widetext">Multimedia Assessment Tool<br/><br/>A web application for building my master thesis, written in PHP based on the MVC software architectural pattern. Uses a MySQL database for data persistence and displays a user-friendly GUI using HTML5, CSS3 and jQuery. Alpha version, under development.</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/lompe.png" alt="" /></div>
            <div class="widetext"><a href="http://lompemakeriet.com/" target="_blank">Lompemakeriet.com</a><br/><br/>Complete web portal and server administration application for a Minecraft server.<br/>Developed from scratch with PHP, MySQL, HTML5, CSS3 and jQuery.</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/wow.png" alt="" /></div>
            <div class="widetext"><a href="http://wow-vendetta.eu/" target="_blank">Vendetta (WoW)</a><br/><br/>A World of Warcraft guild's website and forum that I developed and maintained for some time, back in the days. No longer under development or in use.</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/new_hp.png" alt="" /></div>
            <div class="widetext"><a href="http://tomlin.no/">Ben Christopher Tomlin</a><br/><br/>My personal homepage, which I basically use as a simple online CV, showing my current and previous work and experience. Written in PHP and uses a small amount of jQuery for some fancy touches.</div>
            <div class="clear"></div>
        </div>
        
        <div class="standardbox work" style="margin-bottom:10px;">
            <div class="clear center">PHP Scripting</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/fs.png" alt="" /></div>
            <div class="widetext">Simple File System &nbsp; <a class="small" href="syntax.php?f=filesystem" target="_blank">View Code</a><br/><br/>A stand-alone PHP script for uploading, listing and managing files on a web-server, with administrator login. To some extent still under development. Note: Only for private use as there are less than a few security measures implemented.</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/armory.png" alt="" /></div>
            <div class="widetext">Battle.net Data Miner &nbsp; <a class="small" href="syntax.php?f=wowupdate" target="_blank">View Code</a><br/><br/>Mines World of Warcraft Character data from the Batte.net Armory and simply stores it in a specified database. Was used on the Vendetta website mentioned above. Updated in 2012 to use an API made by Blizzard, making it a lot easier and more efficient.</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/work/php.png" alt="" /></div>
            <div class="widetext">Gallery/Image Editing &nbsp; <a class="small" href="syntax.php?f=ezimage" target="_blank">View Code</a><br/><br/>A basic class containing several functions for manipulating images in PHP (resizing, scaling, aspect ratio). A gallery script/template was also developed, employing the editing script.</div>
            <div class="clear"></div>
        </div>
        
        <div class="standardbox work">
            <div class="clear center">Java Development</div>
            <div class="icons wide"><img class="wideicon" src="resources/images/code/coding.png" alt="" /></div>
            <div class="widetext">During my bachelors, I developed a quite a few applications in Java for various use or generally just for practical experience. These were of relatively small size, but often included GUIs in order to make them more user friendly, or indeed usable at all. This included a calculator, guessing game, list sorter, image viewer, instant messenger (IM) program, and a fully fledged Sudoku game with a bruteforce solver, just to mention a few.</div>
             <div class="clear"></div>
        </div>
        
        <div class="clear"></div>
    </div>
    
    <div class="blankbox content" id="contact" style="display:none">
        <form action="<?php echo BASE_URL; ?>" method="post">
            <div class="standardbox contact float-l">
                <label>Name <em>*</em></label><br/>
                <input type="text" name="name" maxlength="64" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" /><br/>
                <label>Email <em>*</em></label><br/>
                <input type="email" name="email" maxlength="128" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" /><br/>
                <div class="center" style="margin-top:10px">
                    <input type="submit" name="contact_submit" value="Send" />
                </div>
            </div>
            
            <div class="standardbox contact float-r">
                <label>Message <em>*</em></label><br/>
                <textarea name="message" cols="" rows="4" maxlength="1024"><?php echo isset($_POST['message']) ? $_POST['message'] : ''; ?></textarea>
            </div>
        </form>
        
        <div class="clear"></div>
    </div>
    
    <div class="standardbox quotes">
        <span class="small quote"><?php 
            $quote = file("resources/quotes.txt");
            $rdm = rand(0, count($quote)-1);
            echo trim($quote[$rdm]);
        ?></span>
        <span class="refresh"></span>
    </div>
    
    <div class="copyright">Copyright &copy; Ben Christopher Tomlin</div>
    
    <div id="container"><img id="image" src="" alt="" /></div>
    
    <div id="login-container">
        <form id="login-box" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="standardbox login">
                <span id="login-close" class="float-r close">[x]</span>
                <h2>&nbsp;&nbsp;&nbsp;Login</h2>
                <input class="login" type="text" name="username" placeholder="username" /><br/>
                <input class="login" type="password" name="password" placeholder="password" /><br/>
                <input type="submit" name="login" value="Enter" />
            </div>
        </form>
    </div>

</body>
</html>