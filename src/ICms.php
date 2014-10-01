<?php
namespace Netshine\Scaffold;

interface ICms
{
    /**
    * Return the User ID of the currently logged in user
    */
    public function getCurrentUserId();
    /**
    * Add a CSS file to the head
    */
    public function addStylesheet($css_file);
    /**
    * Add a Javascript file to the head
    */
    public function addJavascript($js_file);
    /**
    * Add content directly to the head
    */
    public function addHeadContent($content);
    /**
    * Register a new user with the given details
    */
    public function registerUser($username, $password, $first_name, $last_name, $email_address);
    /** Delete the user **/
    public function deleteUser($user_id);
    /**
    * Perform a login with the specified credentials
    * @return boolean Whether or not the login was successful
    */
    public function login($user, $password, $remember, $check_token = false);
    /**
    * Log the current user out of the CMS
    */
    public function logout();
    public function sendEmail($from_address, $from_name, $recipient, $subject, $body, $cc = null, $bcc = null, $attachments = array(), $reply_to = null, $reply_to_name = null);
}
