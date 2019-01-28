<?php
/**
 * Home page controller class.
 *
 * @author Ben Tomlin (http://tomlin.no)
 * @version 2.1
 */
class Controller {

    /**
     * Logged in context variable
     * @var bool
     */
    public $loggedin = false;

    /**
     * Navigation menu items
     * @var array
     */
    public $nav = array();

    /**
     * Current page context
     * @var string
     */
    public $page = PAGE_HOME;

    /**
     * Initiate variables and call needed functions
     */
    public function __construct() {
        $this->session();

        if (isset($_POST['login']))
            $this->login($_POST['username'], $_POST['password']);

        else if (isset($_GET['logout']))
            $this->logout();

        else if (isset($_GET[PAGE_FS]) && $this->loggedin)
            $this->page = PAGE_FS;

        else if (isset($_POST['contact']))
            $this->contact($_POST['name'], $_POST['email'], $_POST['message']);

        $this->navigation($this->page, $this->loggedin);
    }

    /**
     * Load session and check admin rights.
     */
    private function session() {
        session_start();
        session_regenerate_id(true);

        if (isset($_COOKIE[COOKIE_NAME]) && $_COOKIE[COOKIE_NAME] === USERHASH)
            $this->loggedin = true;
    }

    /**
     * Compose navigation menu items based on current page.
     *
     * @param string $page    current page identifier
     * @param bool $loggedin  show some items if true
     */
    private function navigation($page, $loggedin) {
        switch($page) {
            case PAGE_FS:
                $this->nav = array(
                    array(
                        'show' => true,
                        'name' => 'Home',
                        'href' => INDEX,
                    ),
                    array(
                        'show' => true,
                        'name' => 'Files',
                        'href' => '#',
                        'id'   => 'scroll-content'
                    ),
                    array(
                        'show' => true,
                        'name' => 'Upload',
                        'href' => '#',
                        'id'   => 'scroll-subcontent'
                    )
                );
                break;
            default:
                $this->nav = array(
                    array(
                        'show' => $loggedin,
                        'name' => 'Files',
                        'href' => INDEX . '?' . PAGE_FS,
                    ),
                    array(
                        'show' => true,
                        'name' => 'Stuff',
                        'href' => '#',
                        'id'   => 'scroll-content'
                    ),
                    array(
                        'show' => true,
                        'name' => 'Contact',
                        'href' => '#',
                        'id'   => 'scroll-subcontent'
                    )
                );
                break;
        }
        $this->nav[] = array(
            'show' => true,
            'name' => $loggedin ? 'Logout' : 'Login',
            'href' => $loggedin ? '?logout' : '#',
            'id'   => $loggedin ? '' : 'scroll-login'
        );
    }

    /**
     * Log user in.
     *
     * @param string $user  username
     * @param string $pass  password
     */
    private function login($user, $pass) {
        $user = trim($user);
        $pass = trim($pass);

        if (empty($user) || empty($pass)) {
            $_SESSION['error'] = 'Please enter both a username and a password when logging in';
        } else if ($user != USERNAME || md5(md5($pass)) != PASSWORD) {
            $_SESSION['error'] = 'The specified username or password was incorrect';
        } else {
            setcookie(COOKIE_NAME, USERHASH, time() + 86400, COOKIE_PATH, COOKIE_DOMAIN, true, true);
        }
        $this->redirect(INDEX);
    }

    /**
     * Log user out.
     */
    private function logout() {
        setcookie(COOKIE_NAME, "", time() - 3600, COOKIE_PATH, COOKIE_DOMAIN, true, true);
        $this->redirect(BASE_URL);
    }

    /**
     * Compose a contact message from form input.
     *
     * @param string $name     name of the sender
     * @param string $email    valid email of the sender
     * @param string $message  the message itself
     */
    private function contact($name, $email, $message) {
        $name    = trim($name);
        $email   = trim($email);
        $message = trim($message);

        if (empty($name) || empty($email) || empty($email)) {
        $_SESSION['error'] = 'Please fill out all the required fields';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email address specified';
        } else {
            $subject = "Message from " . SITE_NAME;
            $body = "New contact form message from " . SITE_NAME . "\n\nFrom: {$name}\nEmail: {$email}\n\n{$message}";
            $this->email(WEBMASTER_EMAIL, $email, $subject, $body);
            $this->redirect(INDEX);
        }
    }

    /**
     * Send an email using the php mail function.
     *
     * @param string $to       valid email of recipient
     * @param string $from     valid email of sender
     * @param string $subject  email subject field
     * @param string $message  the email content
     */
    private function email($to, $from, $subject, $message) {
        $headers  = "From: {$from}\r\n";
        $headers .= "Reply-To: " . WEBMASTER_EMAIL . "\r\n";
        $headers .= "Return-Path: " . BASE_URL . "\r\n";

        if (!mail($to, $subject, $message, $headers)) {
            $_SESSION['error'] = "Message delivery failed!";
        }
    }

    /**
     * Redirect to given location path.
     *
     * @param string $location  path to redirect to
     */
    private function redirect($location) {
        header('Location: ' . $location);
        exit;
    }

    /**
     * Get a random quote from file.
     *
     * @return string  a random quote
     */
    public function getQuote() {
        $quote = file(RESOURCE . 'quotes.txt');
        return trim($quote[rand(0, count($quote)-1)]);
    }
}