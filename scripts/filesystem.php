<?php
/**
 * File system controller class.
 *
 * @author Ben Tomlin (http://tomlin.no)
 * @version 3.1
 */
class FileSystem {

    /**
     * Admin context variable
     * @var bool
     */
    public $admin = false;
    
    /**
     * An array to be filled with files data
     * @var array
     */
    public $files = array();
    
    /**
     * The current directory tree and paths
     * @var array
     */
    public $path = array();

    /**
     * Initiates variables and calls needed functions
     */
    public function __construct()
    {
        $this->admin = true;
        $this->setPaths();
        $this->validatePaths();

        if (isset($_POST['up']))
            $this->up();

        else if (isset($_GET['dl']))
            $this->download($_GET['dl']);

        else if (isset($_POST['delete']))
            $this->delete();
        
        else if (isset($_POST['upload']))
            $this->upload();
            
        else if (isset($_POST['rename']))
            $this->rename($_POST['oldname'], $_POST['newname']);
            
        else if (isset($_POST['move']))
            $this->move($_POST['path']);
            
        else if (isset($_POST['newdir']))
            $this->mkdir($_POST['folder']);

        $this->setFiles($this->path['real']);
    }
    
    /**
     * Set current directory and paths accordingly
     */
    private function setPaths()
    {
        $query = trim($_GET['fs']);
        $this->path['query']  = $query;
        $this->path['web']    = FS_BASE_WEB . (empty($query) ? "" : "={$query}");
        $this->path['real']   = realpath(FS_BASE_DIR . '/' . $query) . '/';
        $this->path['parent'] = dirname($this->path['real']) . '/';
    }

    /**
     * Validates the currently set paths
     */
    private function validatePaths()
    {
        if (in_array('..', explode('/', $this->path['query']))) {
            $this->error('Parent directory references are not permitted');
            $this->redirect(FS_BASE_WEB);
        }
        if (in_array('.', explode('/', $this->path['query']))) {
            $this->error('Relative directory references are not permitted');
            $this->redirect(FS_BASE_WEB);
        }
        if (!file_exists($this->path['real'])) {
            $this->error('The specified path does not exist');
            $this->redirect(FS_BASE_WEB);
        }
        if (!is_dir($this->path['real'])) {
            $this->error('The specified path is not a valid directory');
            $this->redirect(FS_BASE_WEB);
        }
    }

    /**
     * Adds an error message to the session errors
     * @param string $message  error message
     */
    private function error($message)
    {
        $_SESSION['errors'][] = $message;
    }

    /**
     * Redirect to given location path
     * @param string $location  path to redirect to
     */
    private function redirect($location)
    {
        header('Location: ' . $location);
        exit;
    }

    /**
     * Set current directory to parent directory
     */
    private function up()
    {
        $exploded = explode('/', $this->path['query']);
        array_pop($exploded);
        $this->redirect(FS_BASE_WEB . (empty($exploded) ? '' : '=' . implode('/', $exploded)));
    }

    /**
     * Call to upload one file
     */
    private function upload()
    {
        if ($_FILES["file"]["error"] == UPLOAD_ERR_INI_SIZE)
            $this->error('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_FORM_SIZE)
            $this->error('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_PARTIAL)
            $this->error('The uploaded file was only partially uploaded.');
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_NO_FILE)
            $this->error('No file was uploaded.');
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_NO_TMP_DIR)
            $this->error('Missing a temporary folder.');
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_CANT_WRITE)
            $this->error('Failed to write file to disk.');
        else if ($_FILES["file"]["error"] == UPLOAD_ERR_EXTENSION)
            $this->error('A PHP extension stopped the file upload.');
        else if (file_exists($this->path['real'] . $_FILES["file"]["name"]))
            $this->error('A file with that name already exists.');
        else {
            move_uploaded_file($_FILES["file"]["tmp_name"], $this->path['real'] . $_FILES["file"]["name"]);
        }
        $this->redirect($this->path['web']);
    }
    
    /**
     * Call to delete one or more files
     */
    private function delete()
    {
        if (!$this->admin) {
            $this->error('You do not have permission to do this.');
            $this->redirect($this->path['web']);
        } else {
            $files = isset($_POST['fileId']) ? $_POST['fileId'] : array();
            if (empty($files))
                $this->error('Please select one (or more) files to delete.');
            else {
                foreach ($files as $file) {
                    if (is_file($file))
                        if (!unlink($file))
                            $this->error('The file ' . $file . ' could not be deleted.');
                    if (is_dir($file))
                        $this->rmdir($file);
                }
            }
            $this->redirect($this->path['web']);
        }
    }

    /**
     * Call to rename a file
     * @param string $old  file path
     * @param string $new  file path
     */
    private function rename($old, $new)
    {
        if (empty($new))
            $this->error('The new name cannot be blank.');
        else {
            $old = $this->path['real'] . $old;
            $new = $this->path['real'] . $new;

            if (!file_exists($old))
                $this->error('The file \'' . $old . '\' does not exist.');

            else if (file_exists($new))
                $this->error('A file with the name \'' . $new . '\' already exists.');

            else if (!rename($old, $new))
                $this->error('The file \'' . $old . '\' could not be renamed.');
        }
        $this->redirect($this->path['web']);
    }
    
    /**
     * Call to move one or more files
     * @param string $path  the path of the folder to move the files to
     */
    private function move($path)
    {
        if (!$this->admin) {
            $this->error('You do not have permission to do this action.');
            $this->redirect($this->path['web']);
        } else {
            $path = realpath($this->path['real'] . trim($path, '\/'));

            $files = isset($_POST['fileId']) ? $_POST['fileId'] : array();
        
            if (!file_exists($path) || !is_dir($path))
                $this->error('The specified folder does not exist.');
            
            else if (empty($files))
                $this->error('Please select one (or more) files to move.');

            else if (substr($path, 0, strlen(FS_BASE_DIR)) !== FS_BASE_DIR)
                $this->error('Cannot move files out of bounds.');
            
            else {
                foreach ($files as $file) {
                    if (!rename($file, $path . '/' . substr($file, strrpos($file, '/'))))
                        $this->error('The file \'' . $file . '\' could not be moved.');
                }
            }
            $this->redirect($this->path['web']);
        }
    }

    /**
     * Force the browser to download the specified file
     *
     * @param string $name  name of the file in current directory
     */
    private function download($name)
    {
        $path = realpath($this->path['real'] . trim($name));

        if (!file_exists($path) || is_dir($path))
            $this->error('The specified file does not exist.');

        else if (substr($path, 0, strlen(FS_BASE_DIR)) !== FS_BASE_DIR)
            $this->error('Cannot download out of bound files.');

        else {
            header("Content-Type: application/save");
            header("Content-Length: " . filesize($path));
            header("Content-Disposition: attachment; filename=\"$name\"");
            header("Content-Transfer-Encoding: binary");
            if ($handle = fopen($path, 'r')) {
                fpassthru($handle);
                fclose($handle);
                exit;
            } else {
                $this->error("Could not open or read file '$name''");
            }
        }
        $this->redirect($this->path['web']);
    }

    /**
     * Call to create a directory
     * @param string $name  new directory name
     */
    private function mkdir($name)
    {
        if (empty($name))
            $this->error('The folder name cannot be empty.');

        else if (file_exists($this->path['real'] . $name))
            $this->error('A folder with the name \'' . $name . '\' already exists.');
        
        else if (!mkdir($this->path['real'] . $name))
            $this->error('The folder \'' . $name . '\' could not be created.');
        else
            chmod($this->path['real'] . $name, 0755);

        $this->redirect($this->path['web']);
    }

    /**
     * Call to delete a directory
     * Also deletes all sub folders and files (recursive)
     * @param string $path  folder path name
     */
    private function rmdir($path)
    {
        if ($handle = opendir($path)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_dir($path . '/' . $file)) {
                        $this->rmdir($path . '/' . $file);
                    } else {
                        if (!unlink($path . '/' . $file))
                            $this->error('The file \'' . $path . '/' . $file . '\' could not be deleted.');
                    }
                }
            }
            closedir($handle);
        }
        if (!rmdir($path))
            $this->error('The folder \'' . $path . '\' could not be deleted.');
    }

    /**
     * Put together an array of files and directories in the specified folder
     * @param string $path  path to directory to read
     */
    private function setFiles($path)
    {
        $files = array();
        $dirs = array();
        if ($handle = opendir($path)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != "..") {
                    $is_dir = is_dir($path . $file);
                    $temp = array(
                        'name'  => $file,
                        'short' => strlen($file) > 36 ? (substr($file, 0, 26) . '...' . substr($file, -10)) : $file,
                        'size'  => $this->getSize($path . $file),
                        'perms' => $this->getPerms($path . $file),
                        'date'  => date("d/m/y", filectime($path . $file)),
                        'dir'   => $is_dir,
                        'path'  => $path . $file,
                        'type'  => $is_dir ? 'dir' : strtolower(substr($file, strrpos($file, '.') + 1)),
                        'link'  => ($is_dir ? FS_BASE_WEB . '=' : FS_BASE_HREF) . (empty($this->path['query']) ? '' : $this->path['query'] . '/') . $file,
                        'download' => $is_dir ? false : $this->path['web'] . '&dl=' . $file,
                    );
                    $temp['icon'] = ICON . (file_exists(ICON . $temp['type'] . '.png') ? $temp['type'] : 'unknown') . '.png';
                    $temp['view'] = stripos(PREVIEW, $temp['type']) !== false;

                    if ($is_dir)
                        $dirs[] = $temp;
                    else
                        $files[] = $temp;
                }
            }
            closedir($handle);
        }
        sort($files);
        sort($dirs);
        
        $this->files = array_merge($dirs, $files);
    }

    /**
     * Get filesize in a readable format
     * @param string $file  the file we want to check
     * @return string       a formatted string of the file's size
     */
    private function getSize($file)
    {
        if (is_dir($file)) {
            $size = $this->dirSize($file);
        } else {
            $size = filesize($file);
        }

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
     * Get size of directory and all underlying files
     * @param string $path  the directory to read
     * @return int          the size of the directory
     */
    private function dirSize($path)
    {
        $size = 0;
        if ($handle = opendir($path)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != "..") {
                    if (is_dir($path . '/' . $file)) {
                        $size += $this->dirSize($path . '/' . $file);
                    } else {
                        $size += filesize($path . '/' . $file);
                    }
                }
            }
            closedir($handle);
        }
        return $size;
    }

    /**
     * Gets file permissions in a readable format
     * @param string $file  the file to get permissions on
     * @return string       a formatted permissions string
     */
    private function getPerms($file)
    {
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
