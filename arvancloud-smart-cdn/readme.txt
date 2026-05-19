=== ArvanCloud Smart CDN Replacer ===
Contributors: hosseinhunta
Tags: cdn, arvancloud, jsdelivr, unpkg, cdnjs, replace, library, performance, iran
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically replaces jsDelivr, unpkg, and cdnjs CDN links with ArvanCloud's Iranian CDN, plus smart library search and automatic file path detection.

== Description ==
This plugin is designed for Iranian WordPress users who face filtering issues with popular CDN providers like jsDelivr, unpkg, and cdnjs. It automatically scans your site's CSS and JS enqueues, detects any of these CDN links, and replaces them with the equivalent path on **lib.arvancloud.ir** – Iran's reliable CDN.

**Key Features:**

*   **Supports Multiple CDNs:** Detects and rewrites links from `cdn.jsdelivr.net`, `unpkg.com`, and `cdnjs.cloudflare.com`.
*   **Smart Pattern Matching:** Parses package names, versions, and file paths to construct correct ArvanCloud URLs.
*   **Automatic File Path Correction (NEW in 2.1.0):** Uses HTTP HEAD requests to automatically find the correct file path inside the package (fixes issues like `lib/` prefix or `.min.js` vs `.js`).
*   **Built-in Library Map:** Comes with a pre-configured list of over 50 popular libraries (Bootstrap, jQuery, Font Awesome, Swiper, etc.).
*   **Automatic Library Search:** Integrates with ArvanCloud’s own search engine. Enter a package name, and the plugin will find and save the correct folder mapping for you.
*   **Manual Mapping Management:** Add, edit, or delete custom package-to-folder mappings via a clean settings page.
*   **Query String Preservation:** Keeps version query strings (`?ver=`) intact after replacement.
*   **ES Module Support:** Works with WordPress 6.5+ script modules.
*   **Performance Friendly:** Uses transient caching for search results and HEAD request results. Only processes enqueued assets.

No more broken libraries or slow-loading foreign CDNs. Stay fast and secure with Iranian infrastructure.

== Installation ==
1. Upload the `arvancloud-smart-cdn` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings → ArvanCloud CDN** to review default mappings and add new ones.

== Frequently Asked Questions ==
= Does this plugin modify my database or theme files? =
No. It works entirely through WordPress filters and only rewrites URLs on the fly. Your original files and database remain unchanged.

= What if a library is not in the default list? =
You can either use the "Automatic Library Search" feature to let the plugin find the folder on ArvanCloud, or you can add a manual mapping from the settings page.

= How does the automatic file path correction work? =
When a CDN URL is replaced, the plugin checks if the resulting file exists on ArvanCloud by sending a lightweight HEAD request. If it returns 404, the plugin tries alternative paths (removing `lib/`, removing `.min`, using just the filename, etc.) and caches the correct one for 24 hours.

= Is ArvanCloud CDN free to use? =
Yes, `lib.arvancloud.ir` is a free public CDN. Please check their website for terms of service.

= Will it work with any WordPress theme or plugin? =
It should work with any theme or plugin that correctly enqueues scripts and styles using WordPress standards. Inline hardcoded URLs (e.g., directly in template files) cannot be caught by this filter-based approach.

= Can I add support for other CDNs? =
The current version handles jsDelivr, unpkg, and cdnjs. If you need support for another CDN, please suggest it on the plugin's support forum.

== Screenshots ==
1. Settings page with library search and manual mapping table.
2. Automatic library search result successfully saved.
3. List of pre-configured default library mappings.

== Changelog ==
= 2.1.0 =
* **New:** Automatic file path detection using HTTP HEAD requests – resolves issues with `lib/` prefix, `.min.js` vs `.js`, and other path mismatches for packages like CodeMirror and WebFontLoader.
* Added `$path_fixes` manual override array for known problematic libraries.
* Improved caching: HEAD request results are cached for 24 hours to maintain performance.
* Minor code optimizations and better error handling.

= 2.0.0 =
* اضافه شدن قابلیت جستجوی خودکار کتابخانه‌ها از طریق lib.arvancloud.ir.
* بهبود الگوهای بازنویسی URL برای پشتیبانی از پروتکل‌های http و //.
* رفع باگ در حذف نگاشت‌های سفارشی.
* بهبود guess_folder برای پکیج‌های اسکوپ‌دار (مانند @fortawesome/fontawesome-free).
* پشتیبانی از ES Modules در وردپرس ۶.۵ و بالاتر.

= 1.0.0 =
* Initial release.
* Support for jsDelivr, unpkg, and cdnjs.
* Built-in mapping for 50+ popular libraries.
* Manual mapping management interface.

== Upgrade Notice ==
= 2.1.0 =
Upgrade to get automatic file path correction for better compatibility with libraries that have non-standard paths on ArvanCloud CDN.

= 1.0.0 =
First stable version. Enjoy automatic CDN replacement!

== Credits ==
Developed by [Hossein Mohmmadian](https://Hosseinhunta.ir).
Powered by [Astel](https://Astel.ir) services.
