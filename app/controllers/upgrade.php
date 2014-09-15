<?php

class Upgrade extends MY_Controller
{

    protected $versions = [
        1.13
    ];
    protected $upgradeResult = true;

    public function __construct()
    {
        parent::__construct();
        $this->protectedArea(UserModel::ROLE_ADMIN);
        echo "<h1>Collabobase Upgrade System</h1>";
    }

    protected function getVersionFile()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "version";
        if (!file_exists($path)) {
            touch($path);
        }
        return $path;
    }

    protected function setVersion($version)
    {
        $path = $this->getVersionFile();
        file_put_contents($path, (float) $version);
    }

    protected function getVersion()
    {
        return (float) file_get_contents($this->getVersionFile());
    }

    protected function checkVersion($version)
    {
        return $this->getVersion() === (float) $version;
    }

    protected function lastVersion()
    {
        return 1.13;
    }

    protected function upgradeMigrationStart()
    {
        $this->upgradeResult = true;
    }

    protected function up_1_13()
    {
//        $this->exec("ALTER TABLE `file` ADD `rights` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '||', ADD INDEX(`rights`);", "Preparing files rights");
        $this->exec("UPDATE `file` SET `rights` = '|| WHERE `rights` IS NULL';", "Modifying old records");
//        $this->exec("ALTER TABLE `post` ADD `rights` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '||', ADD INDEX(`rights`);", "Preparing posts rights");
        $this->exec("UPDATE `post` SET `rights` = '|| WHERE `rights` IS NULL';", "Modifying old records");
    }

    protected function exec($stmt, $title)
    {
        static $pdo;
        $result = true;
        if (!$pdo) {
            $pdo = UserQuery::getInstance()->getPdo();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        echo "$title: ";
        try {
            $pdo->exec($stmt);
            echo "Done.";
        } catch (Exception $e) {
            echo "Failed: {$e->getCode()}: {$e->getMessage()}.";
            $result = $e->getCode() . ": " . $e->getMessage();
            $this->upgradeResult = $this->upgradeResult === true ? "$result" : $this->upgradeResult . "\n<br />$result";
        }
        echo "<br />\n";
        return $result;
    }

    public function up()
    {

        $currentVersion = $this->getVersion();
        $lastVersion = $this->lastVersion();

        echo "Current version: <strong>{$currentVersion}</strong>.<br />";
        echo "Last version: <strong>{$lastVersion}</strong>.<br />";

        if ($currentVersion === $lastVersion) {
            echo "<h2>Congratulations!</h2><p>You already have the last upgrades!</p>";
            return;
        }

        $currentPosition = $currentVersion == 0 ? -1 : array_search($currentVersion, $this->versions);
        $lastPosition = array_search($lastVersion, $this->versions);

        if ($currentPosition === false || $lastPosition === false) {
            user_error("Current or last versions are undefined", E_USER_ERROR);
        }

        for ($i = $currentPosition + 1; $i <= $lastPosition; $i++) {
            $version = $this->versions[$i];
            $textVersion = str_replace(".", "_", "$version");
            $method = "up_$textVersion";
            echo "<h2>Upgrading to version $version</h2>";
            $this->upgradeMigrationStart();
            $this->$method();
            if ($this->upgradeResult === true) {
                $this->setVersion($version);
                echo "<p>Upgrading succeeded.</p>";
            } else {
                echo "<p>Upgrading failed.</p>";
            }
        }

        $currentVersion = $this->getVersion();
        $lastVersion = $this->lastVersion();

        echo "Current version: <strong>{$currentVersion}</strong>.<br />";
        echo "Last version: <strong>{$lastVersion}</strong>.<br />";
    }

}
