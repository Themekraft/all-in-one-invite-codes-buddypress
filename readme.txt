=== All in One Invite Codes BuddyPress ===
Contributors: svenl77, gfirem, vmarin
Tags: registration, invite only, invite codes,
Requires at least: 4.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.8
License: GPL 3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create a invite only Community

== Description ==

1. Use Invite Codes in BuddyPress Registration
2. Let your members access new invite codes from there profile and Invite there friends

== Changelog ==
= 1.0.8 - 20 Nov 2026 =
* Security: BuddyPress sign-ups now use up the invite code when the signup is saved, so a single use code works once. Activated members keep their invite code, get their new invite codes and appear in the Tree View.
* Security: "Restrict create invite codes" and "Restrict invite friends" now also apply to the requests behind the forms.
* Fixed: the Profile Integration setting now applies. The invite code stays required on BuddyPress registration whatever the BuddyPress Registration setting says; that setting will get a confirmation step in a later version.
* Fixed: Auto Connect and Auto Follow now use the owner of the invite code, and no longer fail when the Friends component or BP Follow is missing.
* Fixed: the invite code field is shown on the BuddyPress registration form when Extended Profiles is off.
* Fixed PHP warnings on the BuddyPress settings tab and a notice logged on every page when BuddyPress is not active.

= 1.0.7 - 18 Nov 2026 =
* All in One Invite Codes is now declared as a required plugin. Without it the add-on shows a notice and stays inactive, instead of causing a fatal error on BuddyPress registration.
* Fixed the Registration setting, which was saved as the profile tab setting.
* Requires PHP 7.4.
* Tested up to WordPress 7.1.

= 1.0.6 - 23 Feb  2023 =
* Added missing translations.
* Added new feature to restrict invites.
* Improved invite codes widget.
* Fixed issue with invite code field missing on registration page.
* Tested up to WordPress 6.1.1

= 1.0.5 - 23 Apr  2021 =
* Feature : AutoFollow Feature.
* Feature : Status Widget Feature.

= 1.0.4 - 28 Jan  2021 =
* Fixed an issue related with deployment process, that were causing crashes on install & update of the plugin.

= 1.0.3 - 28 Jan  2021 =
*  Adding the create invite shortcode.
*  Adding translate format.
*  Adding the localization files.
*  Adding the create invite shortcode
*  Updated the language files.
*  Set the correct tk prefix to the invite code input name.
*  Feature: send multiple invites at once. See a list of all the sent invites.
*  Adding first styles.


