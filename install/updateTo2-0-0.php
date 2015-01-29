<?php
define('IN_SCRIPT',1);
define('HESK_PATH','../');
require(HESK_PATH . 'install/install_functions.inc.php');
require(HESK_PATH . 'hesk_settings.inc.php');

hesk_dbConnect();
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."attachments` DROP COLUMN `note_id`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` DROP COLUMN `edit_date`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."notes` DROP COLUMN `number_of_edits`");
hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."users` DROP COLUMN `default_notify_customer_email`");
hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."settings` SET `Value` = '2.0.0' WHERE `Key` = 'modsForHeskVersion'");

$keyRs = hesk_dbQuery("SHOW KEYS FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE Key_name='statuses'");
if (hesk_dbNumRows($keyRs) == 0)
{
    //-- Add the key
    hesk_dbQuery("ALTER TABLE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` ADD KEY `statuses` (`status`)");
}

//-- Add the new HTML email property to modsForHesk_settings.inc.php
$file = file_get_contents(HESK_PATH . 'modsForHesk_settings.inc.php');

//-- Only add the additional settings if they aren't already there.
if (strpos($file, 'html_emails') !== true)
{
    $file .= '

//-- Set this to 1 to enable HTML-formatted emails.
$modsForHesk_settings[\'html_emails\'] = 0;

//-- Mailgun Settings
$modsForHesk_settings[\'use_mailgun\'] = 0;
$modsForHesk_settings[\'mailgun_api_key\'] = \'API Key\';
$modsForHesk_settings[\'mailgun_domain\'] = \'mail.domain.com\';';
}

if (!file_put_contents(HESK_PATH.'modsForHesk_settings.inc.php', $file))
{
    $updateSuccess = false;
    echo '<h1>Failure!</h1>
    <p>An issue occurred when trying to update the modsForHesk_settings.inc.php file.</p>
    <br>
    <p>Add the following lines to your modsForHesk_settings.inc.php file:</p>
    <br>
    <code>//-- Set this to 1 to enable HTML-formatted emails.
        $modsForHesk_settings[\'html_emails\'] = 0;</code><br><br>
    <code>//-- Mailgun Settings
        $modsForHesk_settings[\'use_mailgun\'] = 0;
        $modsForHesk_settings[\'mailgun_api_key\'] = \'API Key\';
        $modsForHesk_settings[\'mailgun_domain\'] = \'mail.domain.com\';
    </code>
    <p>After editing your modsForHesk_settings.inc.php file, you can follow the instructions below.';

}

$banRS = hesk_dbQuery("SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_emails`
                        UNION ALL SELECT `ID` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."denied_ips`");

$banRows = hesk_dbNumRows($banRS);
if (hesk_dbNumRows($banRS) > 0)
{
    $usersRS = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `active` = '1' ORDER BY `name`");
?>
<h2>Migrating IP / E-mail Bans</h2>
<p>Mods for HESK has detected that you have added IP address and/or email bans using Mods for HESK. As part of the upgrade process,
Mods for HESK will migrate these bans for you to HESK 2.6.0's IP/email ban feature. Select the user below that will be the "creator" of the bans,
then click "Submit".</p>
<form action="migrateBans.php" method="post" role="form">
    <select name="user" id="user">
        <?php
            while ($row = hesk_dbFetchAssoc($usersRS))
            { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
           <?php }
        ?>
    </select>
    <input type="submit">
</form>
<?php } else { ?>
    <h1>Installation / Update complete!</h1>
    <p>Please delete the <b>install</b> folder for security reasons, and then proceed back to the <a href="../">Help Desk</a></p>
<?php } ?>