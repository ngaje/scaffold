<?php
namespace Netshine\Scaffold;

interface ICmsUser
{
    /**
    * Return the User ID of the currently logged in user
    */
    public function getCurrentUserId();
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
    /**
    * Check whether the given user is logged in
    */
    public function isUserLoggedIn($user_id);
}
