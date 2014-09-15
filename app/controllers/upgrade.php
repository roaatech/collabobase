<?php

class Upgrade extends MY_Controller
{

    protected $versions = [
        "1.0.1",
        "1.1.0",
        "1.1.1"
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
            if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "downloaded")) {
                copy(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "version", $path);
            } else {
                touch($path);
            }
        }
        return $path;
    }

    protected function setVersion($version)
    {
        $path = $this->getVersionFile();
        file_put_contents($path, (string) $version);
    }

    protected function getVersion()
    {
        return (string) file_get_contents($this->getVersionFile());
    }

    protected function checkVersion($version)
    {
        return $this->getVersion() === (string) $version;
    }

    protected function lastVersion()
    {
        return $this->versions[count($this->versions) - 1];
    }

    protected function upgradeMigrationStart()
    {
        $this->upgradeResult = true;
    }

    protected function readUpgrade($version)
    {
        $dir = __DIR__;
        $ds = DIRECTORY_SEPARATOR;
        $file = "{$dir}{$ds}..{$ds}upgrades{$ds}{$version}.sql";
        if (!file_exists($file)) {
            user_error("Can not find upgrade file for {$version} in app/upgrades", E_USER_ERROR);
        }
        $commands = explode("---", file_get_contents($file));
        array_shift($commands);
        foreach ($commands as $command) {
            @list($title, $command) = @explode("\n", $command, 2);
            $command = trim($command);
            if ($command) {
                $this->exec($command, $title);
            }
        }
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
            echo "<h2>Upgrading to version $version</h2>";
            $this->upgradeMigrationStart();
            $this->readUpgrade($version);
            if ($this->upgradeResult === true) {
                $this->setVersion($version);
                echo "<p>Upgrading succeeded.</p>";
            } else {
                echo "<p>Upgrading failed.</p>";
            }
        }

        echo "Current version: <strong>{$this->getVersion()}</strong>.<br />";
        echo "Last version: <strong>{$this->lastVersion()}</strong>.<br />";
    }

}
