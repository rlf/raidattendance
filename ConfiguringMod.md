

The _Configuration_ of the MOD can be done under the FORUMS > RAIDATTENDANCE > Configuration pane on the ACP.

# Static Configuration #
_Configuration that should hopefully only be done once._

## Guild Configuration ##
In this section it's possible to supply information about the guild hosted by the phpBB.

The information is used when synchronizing the list of raiders with the wowarmory.

  * _Guild Name_ - The name of the guild.
  * _Realm_ - The name of the realm
  * _Armory_ - A link to the appropriate wowarmory (e.g. http://eu.wowarmory.com), for the MOD to work as expected, it must be possible to locate the above guild-name and realm on the supplied armory.

## Forum Configuration ##
In this section it is possible to type in the _name_ of the forum in which the raid attendancy should be shown.

All forums that have a name matching this string will show a table on top of the forum showing raid-attendance.

## Raid Night Configuration ##
This section defines which days are considered raiding days.

In the current release it's only possible to choose named days, e.g. "Monday", "Thursday" etc.

The days chosen will effect the size of the "raid-calendar", which shows only raiding days, but for last-week, this-week and next-week.

## Rank of Raiders ##
In this section it's possible to assign descriptive names to the guild-ranks as well as indicate which ranks are expected to raid.

These settings, together with the settings in the _Guild Configuration_ section defines which guild-members will be added to the raider-list when synchronizing with the armory.

# Administration #
_General administration of raiders in the guild_

## Resyncing the raider-list from the armory ##
Once all the proper configuration have been done with regard to _Guild Configuration_ and _Rank of Raiders_, it is possible to generate/update the list of raiders for the guild.

This is done under the _Raiders_ pane.

**Note:** The resync may time-out, since it is accessing the armory directly, in which case it's safe to simply try again.

Once the resync completes, all raiders in the list, which was not found in the armory is marked - so they can easily be deleted if they are no longer in the guild.

## Deleting raiders from the raid-list ##
Simply check the appropriate row and press the _Delete selected raiders_ button.

## Manually adding a raider to the raid-list ##
If, for some reason, a member of the guild which does not have any of the raid-designated ranks, is expected to raid anyway, the raider can be added manually.

Simply fill in the very last row of the list with _Name_, _Level_, _Rank_ and _Class_ and press the _Save_ button.

**Note:** A member added manually will most likely be marked as **not in armory** on next resync.