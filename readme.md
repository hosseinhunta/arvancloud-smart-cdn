# 🚀 ArvanCloud Smart CDN Replacer for WordPress

[![WordPress Plugin Version](https://img.shields.io/badge/version-2.1.0-blue)](https://github.com/hosseinhunta/arvancloud-smart-cdn)
[![WordPress Tested](https://img.shields.io/badge/wordpress-6.5%20tested-brightgreen)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL%20v2%2B-orange)](https://www.gnu.org/licenses/gpl-2.0.html)

<div dir="ltr">

## 🚀 ArvanCloud Smart CDN Replacer for WordPress

**A smart WordPress plugin that automatically replaces jsDelivr, unpkg, and cdnjs CDN links with the Iranian ArvanCloud CDN.**

---

## 🧠 Why This Plugin?

Given the current internet situation in Iran and unstable connectivity or complete outages that both users and hosting providers face, WordPress sites often fail to load essential JS and CSS files from foreign CDNs like jsDelivr, unpkg, and cdnjs. Fortunately, ArvanCloud has mirrored a large collection of open‑source libraries at [lib.arvancloud.ir](https://lib.arvancloud.ir/libraries/). This plugin automatically redirects your old CDN links to the correct path on ArvanCloud, fixing broken styles, slow loading, and JavaScript errors.

If you encounter a library that is not in our list or does not work correctly after adding it, please report it on [Git](https://gitlab.chabokan.net/Hosseinhunta/arvancloud-smart-cdn/-/issues). We will do our best to fix it.

**Free internet is the right of every human.**

---

## ✨ Features

- 🔄 **Supports multiple CDNs:** Detects and rewrites URLs from `cdn.jsdelivr.net`, `unpkg.com`, and `cdnjs.cloudflare.com`.
- 🧠 **Smart URL parsing:** Extracts package name, version, and file path to build correct ArvanCloud URLs.
- 📚 **Pre-configured library map:** Over 50 popular libraries (Bootstrap, jQuery, Font Awesome, Swiper, Chart.js, etc.) are mapped by default.
- 🔍 **Automatic library search:** Uses ArvanCloud's own search engine (`lib.arvancloud.ir/search`) to find folder names for new libraries and save them automatically.
- 🔧 **Automatic file path correction (NEW in 2.1.0):** Uses lightweight HTTP HEAD requests to automatically fix common path issues (e.g., `lib/` prefix, `.min.js` vs `.js`).
- ⚙️ **Easy manual management:** Add, edit, or delete custom mappings through a clean settings page.
- 📦 **ES Module support:** Works with WordPress 6.5+ script modules.
- ⚡ **Performance caching:** Search results and HEAD request results are cached for 24 hours to keep things fast.

---

## 🛠️ Installation

1. Upload the `arvancloud-smart-cdn` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin panel.
3. Go to **Settings → ArvanCloud CDN** to manage mappings and use the search feature.

---

## 🧩 Usage

The plugin starts working immediately. To get the most out of it:

1. **Auto-Search a Library:** Enter a package name (e.g., `swiper`) in the settings page and click **Search & Save**. The plugin will locate its folder on ArvanCloud.
2. **Add a Manual Mapping:** If a library is missing, enter the npm package name and its ArvanCloud folder name.
3. **No further configuration needed:** All enqueued CSS/JS from supported CDNs will be replaced on the fly. The plugin will automatically fix file paths when needed (e.g., removing `lib/` or `.min`).

---

## ❓ FAQ

**Does this plugin modify my files?**  
No, it uses WordPress filters only – nothing is changed in your theme or plugins.

**What if a library isn't in the default list?**  
Use the automatic search, or add it manually from the settings page.

**How does automatic file path correction work?**  
When a CDN URL is replaced, the plugin checks if the resulting file exists on ArvanCloud by sending a lightweight HEAD request. If it returns 404, it tries alternative paths (removing `lib/`, removing `.min`, using just the filename, etc.) and caches the correct one for 24 hours.

**Is it compatible with all themes?**  
Yes, as long as they use standard `wp_enqueue_script/style` methods.

**Is ArvanCloud CDN free?**  
Yes, `lib.arvancloud.ir` is a free public CDN. Check their terms for details.

---

## 👨‍💻 Contributing

Contributions are welcome! Feel free to open an Issue or submit a Pull Request on GitHub.

---

## 📄 License

This plugin is licensed under the GPL v2 (or later).  
See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for more details.

---

## 🙏 Credits

- Thanks to [ArvanCloud](https://arvancloud.ir) for their excellent Iranian CDN.
- Developed by [Hossein Mohmmadian](https://hosseinhunta.ir)

</div>
