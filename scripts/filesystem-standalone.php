<?php
/*******************************************************************************
* filesystem.php                                                               *
********************************************************************************
* Simple file system in PHP                                                    *
* - list, upload, delete, rename, mkdir, rmdir, move, login, logout, admin     *
* ============================================================================ *
* Version:                    2.1                                              *
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
********************************************************************************
*  NOTE:                                                                       *
* You might have to set these directives in you php.ini file for the script to *
* accept larger files and not to time out on uploading large files:            *
*  - upload_max_filesize        - max_input_time                               *
*  - post_max_size              - max_execution_time                           *
********************************************************************************
*  INSTRUCTIONS/USAGE:                                                         *
* This script is ment to provide a webinterface for a basic filesystem. As     *
* default (and recommended), login is required to access the file system, and  *
* two standard users have been implemented; admin and guest. The administrator *
* can delete files and directories, the guest cannot. The standard passwords   *
* are 'admin' and 'qwerty', respectively. It is recommended that you change    *
* these, using a simple md5 hash.                                              *
*                                                                              *
* When installing this script, create a folder (e.g. 'files)' that you wish to *
* be the root in your new filesystem. Then copy this script into that folder   *
* and rename it index.php. It is important that it is called that, as web      *
* interfaces always start this index file as default. Now open your favourite  *
* browser and go to http://www.example.com/files/ (example.com being your      *
* website and /files/ being the folder you created). Log in as admin or guest, *
* and voila!                                                                   *
*                                                                              *
* Functions available are:                                                     *
* upload (choose a file and press Upload)                                      *
* delete (select files or folders by marking the checkbox and press Delete)    *
* rename (press Rename and type a new name for the file or directory)          *
* new folder (press New Folder, type a name and press Create)                  *
* view or download file (click on the file name links)                         *
* open folder (clicking on a folder opens it and it can be explored)           *
* parent folder (clicking the standard '..' folder opens the parent folder)    *
* move file (select files and press Move, then enter the path to move them to) *
*******************************************************************************/

// Administrator username and password
define('USERNAME', 'admin');
define('PASSWORD', '21232f297a57a5a743894a0e4a801fc3'); // md5('admin')

// Guest username and password
define('GUESTUN', 'guest');
define('GUESTPW', 'd8578edf8458ce06fbc5bb76a58c5ca4'); // md5('qwerty')

// Debug mode on (true) or off (false)
define('DEBUG', true);

// Admin/Guest access only (true) or public (false)
define('LOGIN', true);

// Folder where files are found and will be uploaded (should NOT be changed)
// Default = '.' Do not add leading/trailing slashes
define('FOLDER', '.');

// Run the script
$fs = new FileSystem();

/**
 * The File System Script Class
 */
class FileSystem
{
    /**
     * Indicates if user needs to login or not
     * @var bool
     */
    public $login;
    
    /**
     * The admin context variable
     * @var bool
     */
    public $admin = false;
    
    /**
     * The guest context variable
     * @var bool
     */
    public $guest = false;
    
    /**
     * An array to be filled with files in current folder
     * @var array
     */
    public $files = array();

    /**
     * Initiates variables and calls needed functions
     */
    public function __construct()
    {
        $this->setErrors();
        $this->loadSession();
        $this->needLogin();
        
        if (isset($_POST['login']))
            $this->login();
        
        else if (isset($_POST['logout']))
            $this->logout();
            
        else if (isset($_POST['deleteFiles']))
            $this->delete($_POST['fileId']);
        
        else if (isset($_POST['uploadFiles']))
            $this->upload();
            
        else if (isset($_POST['renameFiles']))
            $this->rename();
            
        else if (isset($_POST['moveFiles']))
            $this->move($_POST['fileId'], $_POST['movepath']);
            
        else if (isset($_POST['createFolder']))
            $this->mkdir($_POST['folder']);
            
        else {
            $this->files = $this->getFiles();
        }
    }
    
    /**
     * Set error displaying based on debug mode
     */
    private function setErrors()
    {
        if (DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
    }

    /**
     * Load session if defined and check admin rights
     */
    private function loadSession()
    {
        if (LOGIN) {
            session_start();
            session_regenerate_id(true);
        
            if (isset($_SESSION['herp'])) {
                $derp = $_SESSION['herp'];
                if ($derp == PASSWORD)
                    $this->admin = true;
                else if ($derp == GUESTPW)
                    $this->guest = true;
                else
                    session_destroy();
            }
        }
    }
    
    /**
     * Checks if user needs to log in or not and sets a variable accordingly
     */
    private function needLogin()
    {
        if (!LOGIN)
            $this->admin = true;
        else if (!$this->admin && !$this->guest)
            $this->login = true;
        else 
            $this->login = false;
    }
    
    /**
     * Call to log in
     */
    private function login()
    {
        $user = trim($_POST['username']);
        $pass = trim($_POST['password']);
        
        if (empty($user) || empty($pass)) {
            $_SESSION['redirect'][] = 'Error: Enter both a username and a password when logging in.';
        } else if (($user != USERNAME || md5($pass) != PASSWORD) &&
                   ($user != GUESTUN  || md5($pass) != GUESTPW)) {
            $_SESSION['redirect'][] = 'Error: Specified username or password was incorrect.';
        } else {
            if ($user == USERNAME)
                $_SESSION['herp'] = PASSWORD;
            else
                $_SESSION['herp'] = GUESTPW;
        }

        // Redirect.
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    /**
     * Call to log out
     */
    private function logout()
    {
        unset($_SESSION['herp']);

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    /**
     * Call to upload one file
     */
    private function upload()
    {
        if ($_FILES["file"]["error"] == UPLOAD_ERR_INI_SIZE)
            $_SESSION['redirect'][] = 'Error: The uploaded file exceeds the upload_max_filesize directive in php.ini.';
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_FORM_SIZE)
            $_SESSION['redirect'][] = 'Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_PARTIAL)
            $_SESSION['redirect'][] = 'Error: The uploaded file was only partially uploaded.';
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_NO_FILE)
            $_SESSION['redirect'][] = 'Error: No file was uploaded.';
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_NO_TMP_DIR)
            $_SESSION['redirect'][] = 'Error: Missing a temporary folder.';
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_CANT_WRITE)
            $_SESSION['redirect'][] = 'Error: Failed to write file to disk.';
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_EXTENSION)
            $_SESSION['redirect'][] = 'Error: A PHP extension stopped the file upload.';
        else if (file_exists(realpath(FOLDER). '/' . $_FILES["file"]["name"]))
            $_SESSION['redirect'][] = 'Error: A file with that name already exists.';
        else {
            move_uploaded_file($_FILES["file"]["tmp_name"], FOLDER . '/' . $_FILES["file"]["name"]);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    /**
     * Call to delete one or more files
     * @param array files  the filenames of the files to delete
     */
    private function delete($files)
    {
        if (!$this->admin) {
            
            $_SESSION['redirect'][] = 'Error: You do not have permission to do this action.';
            
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            
        } else {
        
            if (empty($files))
                $_SESSION['redirect'][] = 'Error: Please select one (or more) files to delete.';
            
            else {
                foreach ($files as $file) {
                    if (is_file($file))
                        if (!unlink($file))
                            $_SESSION['redirect'][] = 'Error: File ' . $file . ' could not be deleted...';
                    if (is_dir($file))
                        $this->rmdir($file);
                }
            }
            
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    /**
     * Call to rename a file
     */
    private function rename()
    {
        $old = trim($_POST['oldname']);
        $new = trim($_POST['newname']);
        
        if (empty($new))
            $_SESSION['redirect'][] = 'Error: The new name cannot be empty.';
        
        else if (!rename(FOLDER . '/' . $old, FOLDER . '/' . $new))
            $_SESSION['redirect'][] = 'Error: ' . $old . ' could not be renamed...';

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    /**
     * Call to move one or more files
     * @param array files  the filenames of the files to move
     * @param string path  the path of the folder to move the files to
     */
    private function move($files, $path)
    {
        if (!$this->admin) {
            
            $_SESSION['redirect'][] = 'Error: You do not have permission to do this action.';
            
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            
        } else {
        
            $path = trim($path, '\/');
        
            if (!file_exists(FOLDER . '/' . $path) || !is_dir(FOLDER . '/' . $path))
                $_SESSION['redirect'][] = 'Error: The specified folder does not exist.';
            
            else if (empty($files))
                $_SESSION['redirect'][] = 'Error: Please select one (or more) files to move.';
            
            else {
                foreach ($files as $file) {
                    if (!rename(FOLDER . '/' . $file, FOLDER . '/' . $path . '/' . $file))
                        $_SESSION['redirect'][] = 'Error: ' . $old . ' could not be moved...';
                }
            }
            
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    /**
     * Call to create a directory
     * Also copies this script to the new folder
     */
    private function mkdir($name)
    {
        if (empty($name))
            $_SESSION['redirect'][] = 'Error: The folder name cannot be empty.';
            
        else if ($name == '.' || $name == '..')
            $_SESSION['redirect'][] = 'Error: The folder cannot be named . or ..';
        
        else if (!mkdir(FOLDER . '/' . $name))
            $_SESSION['redirect'][] = 'Error: Folder ' . $name . ' could not be created...';
        else
            chmod(FOLDER . '/' . $name, 0777);
            copy(basename($_SERVER['PHP_SELF']), FOLDER . '/' . $name . '/' . basename($_SERVER['PHP_SELF']));

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    /**
     * Call to delete a directory
     * Also deletes all sub folders and files (recursive)
     */
    private function rmdir($name)
    {
        if ($handle = opendir($name)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_dir($name . '/' . $file)) {
                        $this->rmdir($name . '/' . $file);
                    } else {
                        if (!unlink($name . '/' . $file))
                            $_SESSION['redirect'][] = 'Error: File ' . $name . '/' . $file . ' could not be deleted...';
                    }
                }
            }
            closedir($handle);
        }
        if (!rmdir($name))
            $_SESSION['redirect'][] = 'Error: Folder ' . $name . ' could not be deleted...';
    }
    
    /**
     * Put together an array of the files in the current folder
     * @return array  a sorted array of files in current folder
     */
    private function getFiles()
    {
        $files = array();
        $dirs = array();
        if ($handle = opendir(FOLDER)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != basename($_SERVER['PHP_SELF']))
                {
                    $temp = array(
                        'name' => $file,
                        'link' => FOLDER . '/' . $file,
                        'size' => $this->getSize(FOLDER . '/' . $file),
                        'perms' => $this->getPerms(FOLDER . '/' . $file),
                        'date' => date("d/m/y", filectime(FOLDER . '/' . $file))
                    );
                    if (is_dir($file))
                        $dirs[] = $temp;
                    else
                        $files[] = $temp;
                }
            }
            closedir($handle);
        }
        
        sort($files);
        sort($dirs);
        
        return array_merge($dirs, $files);
    }

    /**
     * Get filesize in a readable format
     * @param string file  the file we want to check
     * @return string      a formated string of the file's size
     */
    private function getSize($file) {

        $size = filesize($file);

        if ($size < 1024)
            $string = $size . '  b';
        else if ($size < pow(1024, 2))
            $string = number_format($size/1024, 2) . ' kb';
        else if ($size < pow(1024, 3))
            $string = number_format($size/1024/1024, 2) . ' mb';
        else
            $string = number_format($size/1024/1024/1024, 2) . ' gb';

        return str_replace(' ', '&nbsp;', str_pad($string, 9, ' ', STR_PAD_LEFT));
    }

    /**
     * Gets file permissions in a readable format
     * @param string file  the file to get permissions on
     * @return string      a formated permissions string
     */
    private function getPerms($file) {

        $perms = fileperms($file);

        if (($perms & 0xC000) == 0xC000)
            $info = 's'; // Socket
        elseif (($perms & 0xA000) == 0xA000)
            $info = 'l'; // Symbol Link
        elseif (($perms & 0x8000) == 0x8000)
            $info = '-'; // Regular
        elseif (($perms & 0x6000) == 0x6000)
            $info = 'b'; // Block Special
        elseif (($perms & 0x4000) == 0x4000)
            $info = 'd'; // Directory
        elseif (($perms & 0x2000) == 0x2000)
            $info = 'c'; // Character Special
        elseif (($perms & 0x1000) == 0x1000)
            $info = 'p'; // FIFO Pipe
        else
            $info = 'u'; // Unknown

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                 (($perms & 0x0800) ? 's' : 'x' ) :
                 (($perms & 0x0800) ? 'S' : '-'));
        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                 (($perms & 0x0400) ? 's' : 'x' ) :
                 (($perms & 0x0400) ? 'S' : '-'));
        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                 (($perms & 0x0200) ? 't' : 'x' ) :
                 (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>File System</title>
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="Simple uploading of files script written in PHP" />
    <meta name="keywords" content="simple, list, upload, delete, rename, file,
        files, directory, mkdir, rmdir, login, logout, sessions, php" />
    <meta name="author" content="Ben Christopher Tomlin" />
    
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
        width: 680px;
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
    div.login {
        width: 180px;
        margin: 180px auto 10px auto;
        text-align: center;
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
    input[type="checkbox"] {
        width: auto;
        height: auto;
        margin: 0;
        padding: 0;
    }
    th, td {
        padding: 3px;
    }
    table {
        width: 100%;
    }
    a {
        color: #0f1f3f;
        text-decoration: none;
    }
    a:hover {
        color: #0f2f5f;
    }
    h2 {
        font-family: Century Gothic, Verdana, Arial, sans-serif;
        font-weight: normal;
        font-size: 22px;
        text-transform: capitalize;
        margin: 0px;
    }
    .center {
        text-align: center;
    }
    .left {
        text-align: left
    }
    .floatr {
        float: right;
    }
    .mono {
        font-family: monospace;
    }
    .rename {
        float: right;
        color: black;
    }
    .close {
        color: #ccc;
        text-decoration: none;
    }
    </style>
    
</head>
<body>

    <?php if (isset($_SESSION['redirect'])): ?>
    <div class="errorbox">
        <a class="close floatr" href="<?php echo $_SERVER['PHP_SELF']; ?>">[x]</a>
        <?php
        if (is_array($_SESSION['redirect'])) {
            foreach ($_SESSION['redirect'] as $error) {
                echo $error . '<br/>';
            }
        } else {
            echo $_SESSION['redirect'];
        }
        unset($_SESSION['redirect']);
        ?>
    </div>
    <?php endif; ?>
    
    <?php if ($fs->login): ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="standardbox login">
            <h2>Login</h2>
            <input type="text" name="username" /><br/>
            <input type="password" name="password" /><br/>
            <input type="submit" name="login" value="enter" />
        </div>
    </form>
    <?php else: ?>
    
    <?php if (!is_writable(FOLDER)): ?>
    <div class="errorbox">
        <span style="color:red;">The upload folder is not writable! Please check the folder permissions for this directory.</span>
    </div>
    <?php endif; ?>

    <div class="copyright" style="margin-top:30px"><?php echo realpath(FOLDER); ?></div>
    
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <div class="standardbox">
            <input name="file" type="file" style="width:70%;" />
            <input name="uploadFiles" type="submit" value="Upload" style="width: 20%;float:right;" />
        </div>
    </form>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="standardbox center">
            <table>
                <tr><th></th><th class="left">Filename</th><th>Permissions</th><th>Size</th><th>Uploaded</th></tr>
                <?php foreach ($fs->files as $file): ?>
                <tr>
                    <?php if (is_dir($file['link']) && $file['name'] == '..'): ?>
                    <td></td><td class="left"><a href="<?php echo $file['link']; ?>"><b><?php echo $file['name']; ?></b></a></td>
                    <td class="mono"><?php echo $file['perms']; ?></td><td>--</td><td>--</td>
                    <?php elseif (is_dir($file['link'])): ?>
                    <td><?php if ($fs->admin): ?><input type="checkbox" name="fileId[]" value="<?php echo $file['link']; ?>" /><?php endif; ?></td>
                    <td class="left"><a href="<?php echo $file['link']; ?>"><b><i><?php echo $file['name']; ?></b></i></a>
                    <a class="mono rename" href="#rename" onclick="document.getElementById('oldname').value='<?php echo $file['name']; ?>';
                        document.getElementById('newname').value='<?php echo $file['name']; ?>';
                        document.getElementById('renamebox').style.display='block';">(rename)</a></td>
                    <td class="mono"><?php echo $file['perms']; ?></td><td>--</td><td>--</td>
                    <?php else: ?>
                    <td><?php if ($fs->admin): ?><input type="checkbox" name="fileId[]" value="<?php echo $file['link']; ?>" /><?php endif; ?></td>
                    <td class="left"><a href="<?php echo $file['link']; ?>" target="_blank"><?php echo $file['name']; ?></a>
                    <a class="mono rename" href="#rename" onclick="document.getElementById('oldname').value='<?php echo $file['name']; ?>';
                        document.getElementById('newname').value='<?php echo $file['name']; ?>';
                        document.getElementById('renamebox').style.display='block';">(rename)</a></td>
                    <td class="mono"><?php echo $file['perms']; ?></td>
                    <td class="mono"><?php echo $file['size']; ?></td>
                    <td class="mono"><?php echo $file['date']; ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </table>
            <input type="submit" name="newFolder" value="New Folder" onclick="document.getElementById('mkdirbox').style.display='block'; return false;" />
            <?php if ($fs->admin): ?>
            <input type="hidden" id="movepath" name="movepath" value="." />
            <input type="submit" name="moveFiles" value="Move Selected" onclick="return ((document.getElementById('movepath').value=prompt('Enter the relative path of a folder to move these files to:')) != null);" />
            <input type="submit" name="deleteFiles" value="Delete Selected" onclick="return confirm('Are you sure you want to do this? (Cannot be undone)');"/>
            <?php endif; ?>
            <input type="submit" name="logout" value="Logout" />
        </div>
        <div class="standardbox" id="renamebox" style="display:none;"><a name="rename"></a>
            <input type="hidden" id="oldname" name="oldname" value="" />
            <input type="text" id="newname" name="newname" value="" style="width:70%;" />
            <input type="submit" name="renameFiles" value="Rename" style="width: 20%;float:right;" />
        </div>
        <div class="standardbox" id="mkdirbox" style="display:none;">
            <input type="text" id="folder" name="folder" value="" style="width:70%;" />
            <input type="submit" name="createFolder" value="Create" style="width: 20%;float:right;" />
        </div>
    </form>
    <?php endif; ?>
    
    <div class="copyright">Copyright &copy; Ben Christopher Tomlin</div>
    
</body>
</html>