<?php namespace LeeSherwood\Ejabberd\CommandExecutors;

/**
 * Dummy command executor for ejabberd
 *
 * This class implements the command executor interface and just returns some sane boolean values.
 * You may want to use this class to test your ejabberd external authentication module is set up correctly
 * before you start creating your custom code.
 *
 * @package LeeSherwood\Ejabberd
 * @author Lee Sherwood
 */

use \PDO;

class mailcowCommandExecutor implements CommandExecutorInterface {

    /**
     * Authenticate a user (login)
     *
     * @param string $username
     * @param string $servername
     * @param string $password
     *
     * @return bool
     */

    public static function verify_salted_hash($hash, $password, $algo, $salt_length) {
      // Decode hash
      $dhash = base64_decode($hash);
      // Get first n bytes of binary which equals a SSHA hash
      $ohash = substr($dhash, 0, $salt_length);
      // Remove SSHA hash from decoded hash to get original salt string
      $osalt = str_replace($ohash, '', $dhash);
      // Check single salted SSHA hash against extracted hash
      if (hash_equals(hash($algo, $password . $osalt, true), $ohash)) {
        return true;
      }
      return false;
    }

    public static function verify_hash($hash, $password) {
      if (preg_match('/^{(.+)}(.+)/i', $hash, $hash_array)) {
        $scheme = strtoupper($hash_array[1]);
        $hash = $hash_array[2];
        switch ($scheme) {
          case "ARGON2I":
          case "ARGON2ID":
          case "BLF-CRYPT":
          case "CRYPT":
          case "DES-CRYPT":
          case "MD5-CRYPT":
          case "MD5":
          case "SHA256-CRYPT":
          case "SHA512-CRYPT":
            return password_verify($password, $hash);

          case "CLEAR":
          case "CLEARTEXT":
          case "PLAIN":
            return $password == $hash;

          case "LDAP-MD5":
            $hash = base64_decode($hash);
            return hash_equals(hash('md5', $password, true), $hash);

          case "PBKDF2":
            $components = explode('$', $hash);
            $salt = $components[2];
            $rounds = $components[3];
            $hash = $components[4];
            return hash_equals(hash_pbkdf2('sha1', $password, $salt, $rounds), $hash);

          case "PLAIN-MD4":
            return hash_equals(hash('md4', $password), $hash);

          case "PLAIN-MD5":
            return md5($password) == $hash;

          case "PLAIN-TRUNC":
            $components = explode('-', $hash);
            if (count($components) > 1) {
              $trunc_len = $components[0];
              $trunc_password = $components[1];

              return substr($password, 0, $trunc_len) == $trunc_password;
            } else {
              return $password == $hash;
            }

          case "SHA":
          case "SHA1":
          case "SHA256":
          case "SHA512":
            // SHA is an alias for SHA1
            $scheme = $scheme == "SHA" ? "sha1" : strtolower($scheme);
            $hash = base64_decode($hash);
            return hash_equals(hash($scheme, $password, true), $hash);

          case "SMD5":
            return self::verify_salted_hash($hash, $password, 'md5', 16);

          case "SSHA":
            return self::verify_salted_hash($hash, $password, 'sha1', 20);

          case "SSHA256":
            return self::verify_salted_hash($hash, $password, 'sha256', 32);

          case "SSHA512":
            return self::verify_salted_hash($hash, $password, 'sha512', 64);

          default:
            return false;
        }
      }
      return false;
    }

    public function authenticate($username, $servername, $password)
    {
      $database_type = 'mysql';
      $database_sock = '/var/run/mysqld/mysqld.sock';
      $database_user = '__DBUSER__';
      $database_pass = '__DBPASS__';
      $database_name = '__DBNAME__';

      $dsn = $database_type . ":unix_socket=" . $database_sock . ";dbname=" . $database_name;
      $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
      ];
      try {
        $pdo = new PDO($dsn, $database_user, $database_pass, $opt);
      }
      catch (PDOException $e) {
        return false;
      }
      if (!filter_var($username, FILTER_VALIDATE_EMAIL) && !ctype_alnum(str_replace(array('_', '.', '-'), '', $username))) {
        return false;
      }
      $username = strtolower(trim($username));
      $stmt = $pdo->prepare("SELECT `password` FROM `mailbox`
          INNER JOIN domain on mailbox.domain = domain.domain
          WHERE `kind` NOT REGEXP 'location|thing|group'
            AND `mailbox`.`active`= '1'
            AND `domain`.`active`= '1'
            AND `domain`.`xmpp` = '1'
            AND JSON_UNQUOTE(JSON_VALUE(`mailbox`.`attributes`, '$.xmpp_access')) = '1'
            AND CONCAT(`domain`.`xmpp_prefix`, '.', `domain`.`domain`) = :servername
            AND `username` = CONCAT(:local_part, '@', `domain`.`domain`)");
      $stmt->execute(array(':local_part' => $username, ':servername' => $servername));
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $row) {
        if (self::verify_hash($row['password'], $password) !== false) {
          return true;
        }
      }
      return false;
    }

    /**
     * Check if a user exists
     *
     * @param string $username
     * @param string $servername
     *
     * @return bool
     */
    public function userExists($username, $servername)
    {
        return true;
    }

    /**
     * Set a password for a user
     *
     * @param string $username
     * @param string $servername
     * @param string $password
     *
     * @return bool
     */
    public function setPassword($username, $servername, $password)
    {
        return false;
    }

    /**
     * Register a user
     *
     * @param string $username
     * @param string $servername
     * @param string $password
     *
     * @return bool
     */
    public function register($username, $servername, $password)
    {
        return false;
    }

    /**
     * Delete a user
     *
     * @param string $username
     * @param string $servername
     *
     * @return bool
     */
    public function removeUser($username, $servername)
    {
        return false;
    }

    /**
     * Delete a user with password validation
     *
     * @param string $username
     * @param string $servername
     * @param string $password
     *
     * @return bool
     */
    public function removeUserWithPassword($username, $servername, $password)
    {
        return false;
    }
}