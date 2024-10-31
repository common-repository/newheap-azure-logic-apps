=== NewHeap Azure Logic apps ===
Contributors: NewHeap
Donate link: https://newheap.com
Tags: Azure Logic apps, logic apps, azure, MS logic, MS apps, azure apps, automation, Microsoft flow
Requires at least: 5.3.2
Tested up to: 5.3.2
Stable tag: 1.0.0
Minimum PHP: 7.1.1

This plugin makes it possible to setup multiple "http request trigger" (When a HTTP-request is received) integrations to one or more Azure environments.

== Description ==

Supports multiple, easy to configure, events. When an event occurs, a personally configurable notification can be send to your Azure environment. This plugin also allow's you to setup custom events which you can create in your own theme or plugin.

**The following events are supported out of the box:**
* Add custom events
* Filter on post types
* Post created
* Post updated
* Post thrashed
* Post deleted
* Comment created
* Comment status change
* User login successful
* User login failed
* User created
* User updated
* User deleted
* User role changed
* Plugin activated
* Plugin deactivated
* Plugin deleted
* Attachment created
* Attachment updated
* Attachment deleted

**Roadmap**
* WooCommerce support
* Wordfence support
* Many more

== Installation ==

=== From within WordPress ===
1. Visit 'Plugins > Add New'
1. Search for 'Azure Logic apps by NewHeap'
1. Activate 'Azure Logic apps by NewHeap' from your Plugins page.
1. Go to "after activation" below.

=== Manually ===
1. Upload the `newheap-azure-logic-apps` folder to the `/wp-content/plugins/` directory
1. Activate the `Azure Logic apps by NewHeap` plugin through the 'Plugins' menu in WordPress
1. Go to "after activation" below.

=== After activation ===
1. Azure Logic apps by NewHeap should now be available in the main menu
1. Navigate to your Azure portal environment and create a new Logic app (https://docs.microsoft.com/en-us/azure/logic-apps/logic-apps-overview).
1. Within the Logic app, add the 'When a HTTP request is received' (trigger).
1. Save the logic app, the URL will be generated after saving.
1. Copy the URL and navigate back to your Wordpress environment
1. Via the main menu, go to 'Azure Logic apps'
1. Click the 'Add new' button
1. Paste the generated Azure HTTP request trigger URL in the 'Webhook url' field.
1. Complete the rest of the form and make sure to activate the integration.
1. Choose all the events you with to support
1. Save up and ur ready to go and finish that Logic app.

== Screenshots ==
1. Example flow within Azure logic apps
2. Support for multiple customizable integrations
3. Integration setup
4. Integration hook setup

== Changelog ==

= 1.0.0 =
* Initial release.
