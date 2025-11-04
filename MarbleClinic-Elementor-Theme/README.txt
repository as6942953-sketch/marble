=== MarbleClinic Elementor Theme ===

Contributors: Nauman Ellahi
Tags: elementor, business, restoration, marble, services
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Version: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

100% Elementor-compatible clone of marbleclinicrestoration.com

== Description ==

This theme is a complete, pixel-perfect clone of the Marble Clinic Restoration website (https://marbleclinicrestoration.com/), rebuilt as a fully editable WordPress + Elementor theme.

= Features =

* 100% Elementor-compatible
* All 19 pages fully editable in Elementor
* Auto-creates all pages on activation
* Includes all 182 images from live site
* Complete BeTheme styling (be.css, responsive.css, animations.min.css)
* Same header and footer on all pages
* Responsive design (mobile, tablet, desktop)
* Font Awesome icons (local)
* Google Fonts: Roboto + Lora (local fallback)

= Pages Included =

The theme automatically creates 19 pages:

1. Home
2. About Us
3. Services
4. Marble Natural Stone Restoration
5. Marble Repair Restoration
6. Marble Refinishing Care Maintenance
7. Kitchen Island Countertops and Refinishing
8. Floors Counters Walls Maintenance
9. Carpet Installation
10. Laminate Flooring Installation
11. Tile Floor Installation
12. Beverly Hills CA
13. Santa Monica CA
14. Brentwood CA
15. Calabasas CA
16. Studio City CA
17. Contact Us
18. Natural Stones Care Maintenance
19. Gallery

== Installation ==

= STEP 1: Install Elementor =

Before activating this theme, you MUST install Elementor:

1. Go to WordPress Admin → Plugins → Add New
2. Search for "Elementor"
3. Install and Activate "Elementor Website Builder"

= STEP 2: Upload Theme =

1. Download MarbleClinic-Elementor-Theme.zip
2. Go to WordPress Admin → Appearance → Themes
3. Click "Add New" → "Upload Theme"
4. Choose MarbleClinic-Elementor-Theme.zip
5. Click "Install Now"

= STEP 3: Activate Theme =

1. Click "Activate" button
2. Wait 30-60 seconds for automatic setup
3. You'll see a success notice confirming:
   - 19 pages created
   - 182 images copied
   - Navigation menu built

= STEP 4: Flush Permalinks (CRITICAL!) =

1. Go to Settings → Permalinks
2. Click "Save Changes" (don't change anything, just save)
3. This ensures pages load correctly

= STEP 5: Clear Browser Cache =

1. Press Ctrl+Shift+Delete (Windows) or Cmd+Shift+Delete (Mac)
2. Or view site in Incognito/Private mode

= STEP 6: View Your Site =

1. Visit your homepage
2. It should look exactly like marbleclinicrestoration.com

== Editing Pages with Elementor ==

1. Go to Pages → All Pages
2. Click on any page
3. Click "Edit with Elementor" button
4. The page opens in Elementor editor
5. All content is editable!

== Navigation Menu ==

The theme automatically creates a "Main Navigation" menu with this structure:

* Home
* About Us
* Services
  - Marble Natural Stone Restoration
  - Marble Repair Restoration
  - Marble Refinishing Care Maintenance
  - Kitchen Island Countertops and Refinishing
  - Floors Counters Walls Maintenance
  - Carpet Installation
  - Laminate Flooring Installation
  - Tile Floor Installation
* Contact Us
* Gallery

To edit the menu:
1. Go to Appearance → Menus
2. Select "Main Navigation"
3. Add/remove/reorder items
4. Click "Save Menu"

== Customization ==

= Change Logo =

1. Appearance → Customize → Site Identity
2. Click "Select Logo"
3. Upload your logo (recommended: 250x73px)
4. Click "Publish"

= Edit Header/Footer =

Option 1: Using Elementor Theme Builder
1. Go to Templates → Theme Builder
2. Create custom header/footer templates
3. Assign them globally

Option 2: Edit template files
1. Edit header.php and footer.php in theme folder
2. Customize HTML/PHP code

= Change Colors/Typography =

1. Click "Edit with Elementor" on any page
2. Click hamburger menu (≡) → Site Settings
3. Go to Global Colors or Global Fonts
4. Customize as needed

== Technical Requirements ==

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Elementor plugin (free version)
* MySQL 5.6 or higher

== Theme Support ==

* Title Tag
* Post Thumbnails
* Custom Logo
* HTML5 Markup
* Elementor
* Elementor Pro (if installed)

== File Structure ==

MarbleClinic-Elementor-Theme/
├── style.css (theme metadata)
├── functions.php (theme functionality)
├── header.php (site header)
├── footer.php (site footer)
├── index.php (main template)
├── page.php (page template)
├── front-page.php (homepage template)
├── single.php (post template)
├── screenshot.png (theme screenshot - optional)
│
├── assets/
│   ├── css/
│   │   ├── be.css (412KB - main BeTheme styles)
│   │   ├── responsive.css (64KB - responsive design)
│   │   └── animations.min.css (60KB - animations)
│   │
│   ├── fonts/
│   │   ├── fontawesome/ (Font Awesome icons)
│   │   └── google-fonts-local.css (local fonts)
│   │
│   └── images/ (182 images from live site)
│       ├── 2020/10/
│       ├── 2021/02/
│       ├── 2021/03/
│       ├── 2021/09/
│       ├── 2025/02/
│       ├── 2025/03/
│       ├── elementor/
│       └── font-awesome/
│
└── page-sources/ (19 HTML files for content)

== Troubleshooting ==

= Images Not Showing =

→ Check Media → Library (should have 182 images)
→ If empty: Reactivate theme to trigger image copy
→ Check file permissions: wp-content/uploads should be writable (755)

= Pages Not Created =

→ Check Pages → All Pages (should show 19 pages)
→ If missing: Reactivate theme
→ Or: Deactivate and reactivate theme to trigger setup again

= CSS Not Loading =

→ Right-click page → Inspect → Network tab
→ Look for be.css, responsive.css (should be 200 OK)
→ If 404: Re-upload theme
→ Clear WordPress cache if using cache plugin

= Elementor Won't Edit Page =

→ Make sure Elementor plugin is installed and activated
→ Go to Pages → Edit page → Click "Edit with Elementor"
→ If error: Regenerate Elementor CSS (Tools → Regenerate CSS)

= Menu Not Working =

→ Appearance → Menus
→ Check "Main Navigation" exists
→ Assign to "Primary Menu" location
→ Save menu

== Changelog ==

= 1.0.0 =
* Initial release
* Includes all 19 pages from marbleclinicrestoration.com
* Full Elementor compatibility
* Auto-page creation on activation
* Complete asset package (182 images, 3 CSS files, fonts)

== Credits ==

* Source website: https://marbleclinicrestoration.com/
* Built with Elementor compatibility
* BeTheme CSS framework from live site
* Font Awesome icons
* Google Fonts: Roboto and Lora

== License ==

This theme is licensed under the GPL v2 or later.

== Support ==

For issues or questions:
1. Check this README troubleshooting section
2. Verify WordPress and PHP requirements
3. Ensure Elementor plugin is installed
4. Check browser console for errors (F12)

== Additional Notes ==

* This theme is designed to work seamlessly with Elementor
* All pages are pre-populated with content from the live site
* Header and footer match the original site exactly
* CSS styling is identical to the live site
* All images are included and optimized
* Responsive design works on all devices (mobile, tablet, desktop)

Install Elementor → Upload Theme → Activate → Enjoy! ✅
