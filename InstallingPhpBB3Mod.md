# Prerequisites #
Make sure your phpBB3 installation has the AutoMOD installed, otherwise go get it at http://www.phpbb.com/mods/automod/

Your version of PHP need to have enabled the allow\_url\_fopen in the php.ini file, this is usually the default value, but it may differ between different web-hosting services:
```
allow_url_fopen On
```
See http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen for details.

# Downloading the MOD #
  1. Download the MOD from the downloads section http://code.google.com/p/raidattendance/downloads/list
  1. Unzip the zip-file to a local folder
  1. Upload the folder to the phpBB3/store/mods folder

# Installing the MOD #
  1. Use AutoMOD to install the MOD (login to ACP - go to AutoMOD).
  1. Follow the instructions in the DIY section (run the install\_raidattendance.php script).
  1. Follow the instructions of the install-script.
  1. The MOD is installed!

# Configuring #
During the installation (the script) the MOD is partly configured, but some settings still need to be set.
  1. Find the MOD under the Forums section (ACP).
  1. Navigate to the **Configuration** pane
  1. Type in the rank-names and select which ranks are expected to raid.
  1. Navigate to the **Raiders** pane
  1. Choose **Resync with armory** button
  1. A list of raiders as defined on the **Configuration** pane should be shown - and their associated forum-usernames will automatically be updated (if they match).
  1. Assign forum-users to those that have accounts with differing names, and press **Save**
  1. The raider-list is now _up to date_ and the MOD should be available under the relevant forum.

# Using the MOD on the Forum #
The _forum-name_ configured will now show a table on top of the topic list with all raiders and their current attendance.

If the current-user has moderator access, the user can choose to sign ppl on or off specific raid-nights.

If a user is logged in, and the mapping between raider and forum-user is correct, the user can sign off from future raids (not possible to edit past raids).

It's possible to see the past week, this week and the upcoming week.

It's possible to navigate further back or forward in time.