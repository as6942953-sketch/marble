╔═══════════════════════════════════════════════════════════╗
║     MARBLE ELEMENTOR THEME - INSTALLATION GUIDE          ║
╔═══════════════════════════════════════════════════════════╗

VERSION: 1.0
THEME NAME: Marble Elementor Theme
AUTHOR: Nauman Ellahi
CONVERTED FROM: marbleclinicrestoration.com HTTrack mirror

══════════════════════════════════════════════════════════
QUICK START INSTALLATION
══════════════════════════════════════════════════════════

1. UPLOAD THEME:
   - Go to: WordPress Admin → Appearance → Themes
   - Click: "Add New" → "Upload Theme"
   - Choose: Marble-Elementor-Theme-fixed.zip
   - Click: "Install Now" → "Activate"

2. AUTOMATIC SETUP (On Activation):
   ✓ 16 pages automatically created from HTML files
   ✓ "Main Navigation" menu created and assigned
   ✓ Home page set as front page
   ✓ Permalinks structure updated
   ✓ All pages ready for Elementor editing

3. INSTALL REQUIRED PLUGINS:
   Go to: Plugins → Add New
   
   REQUIRED:
   - Elementor (or Elementor Pro)
   
   RECOMMENDED:
   - Contact Form 7 (for contact page)
   - Yoast SEO or Rank Math (for SEO)

4. CONFIGURE PERMALINKS:
   - Go to: Settings → Permalinks
   - Select: "Post name"
   - Click: "Save Changes"

5. VERIFY PAGES:
   - Go to: Pages → All Pages
   - You should see 16 auto-created pages
   - Open any page with Elementor to edit

══════════════════════════════════════════════════════════
PAGES AUTO-CREATED
══════════════════════════════════════════════════════════

MAIN PAGES:
→ Home (set as front page)
→ About Us
→ Services (with sub-pages)
→ Gallery
→ Contact Us

SERVICE PAGES (under Services menu):
→ Marble Natural Stone Restoration
→ Marble Repair Restoration
→ Marble Refinishing Care & Maintenance
→ Natural Stones Care & Maintenance
→ Kitchen Island Countertops & Refinishing
→ Floors Counters Walls Maintenance

LOCATION PAGES:
→ Beverly Hills, CA
→ Santa Monica, CA
→ Brentwood, CA
→ Calabasas, CA
→ Studio City, CA

══════════════════════════════════════════════════════════
UPLOADING IMAGES
══════════════════════════════════════════════════════════

The HTML pages reference images in these paths:
- wp-content/uploads/2021/02/
- wp-content/uploads/2020/10/
- wp-content/uploads/elementor/thumbs/

OPTION 1 - Upload via Media Library:
1. Go to: Media → Add New
2. Upload all images from your HTTrack mirror
3. Note: Theme will auto-convert paths on page load

OPTION 2 - Direct Upload (Advanced):
1. FTP/SSH to your WordPress installation
2. Copy images to: /wp-content/uploads/
3. Match the directory structure from HTML files

══════════════════════════════════════════════════════════
NAVIGATION MENU
══════════════════════════════════════════════════════════

AUTO-CREATED MENU: "Main Navigation"
ASSIGNED TO: Primary location

Structure:
- Home
- About Us
- Services
  ├─ Marble Natural Stone Restoration
  ├─ Marble Repair Restoration
  ├─ Marble Refinishing Care & Maintenance
  ├─ Natural Stones Care & Maintenance
  ├─ Kitchen Island Countertops & Refinishing
  └─ Floors Counters Walls Maintenance
- Gallery
- Contact Us

To customize:
→ Appearance → Menus → Main Navigation

══════════════════════════════════════════════════════════
ELEMENTOR EDITING
══════════════════════════════════════════════════════════

1. Go to: Pages → All Pages
2. Hover over any page
3. Click: "Edit with Elementor"
4. Make changes in visual editor
5. Click: "Update" to save

FIRST TIME EDITING:
- Elementor will convert HTML to editable widgets
- Original design preserved
- All sections, columns, and widgets retained

══════════════════════════════════════════════════════════
CUSTOMIZATION
══════════════════════════════════════════════════════════

SITE IDENTITY:
→ Appearance → Customize → Site Identity
- Upload logo
- Set site title and tagline
- Add site icon (favicon)

COLORS & FONTS:
→ Edit with Elementor → Global Settings
- Set global colors
- Choose typography
- Define button styles

WIDGETS:
→ Appearance → Widgets
- Add widgets to sidebar
- Configure footer widgets

══════════════════════════════════════════════════════════
THEME FEATURES
══════════════════════════════════════════════════════════

✓ Full Elementor compatibility
✓ Responsive design (mobile, tablet, desktop)
✓ Font Awesome icons included locally
✓ Google Fonts with local fallback
✓ SEO-friendly markup
✓ Translation-ready
✓ WordPress coding standards compliant
✓ Secure and sanitized output
✓ Fast-loading optimized assets

══════════════════════════════════════════════════════════
TROUBLESHOOTING
══════════════════════════════════════════════════════════

PAGES NOT SHOWING?
→ Go to Settings → Permalinks → Save Changes

MENU NOT DISPLAYING?
→ Go to Appearance → Menus
→ Check "Primary Menu" is assigned to "Primary" location

IMAGES NOT LOADING?
→ Upload images to WordPress Media Library
→ Or copy to wp-content/uploads/ via FTP

ELEMENTOR NOT WORKING?
→ Install Elementor plugin
→ Clear browser cache
→ Edit page with Elementor and click Update

STYLING ISSUES?
→ Clear all caches (browser, plugin, CDN)
→ Regenerate CSS in Elementor settings
→ Check that all CSS files loaded properly

══════════════════════════════════════════════════════════
DISABLE AUTO-PAGE CREATION
══════════════════════════════════════════════════════════

If you switch themes and re-activate:
- Pages won't be duplicated (checks for existing pages)
- Menu won't be duplicated (checks for existing menu)

To fully disable auto-creation:
1. Open: wp-content/themes/marble-elementor-theme/functions.php
2. Find line ~433: add_action( 'after_switch_theme', 'marble_elementor_theme_create_pages_on_activation' );
3. Comment it out: // add_action( ...

══════════════════════════════════════════════════════════
SUPPORT & DOCUMENTATION
══════════════════════════════════════════════════════════

THEME FILES:
- See: migration_log.txt (detailed changelog)
- Check: AUTO_PAGE_CREATION_GUIDE.txt
- Review: CODE_REVIEW_FIXES.txt

WORDPRESS RESOURCES:
- WordPress.org Documentation: https://wordpress.org/documentation/
- Elementor Documentation: https://elementor.com/help/
- Theme Support Forum: WordPress.org

══════════════════════════════════════════════════════════
TECHNICAL REQUIREMENTS
══════════════════════════════════════════════════════════

MINIMUM REQUIREMENTS:
- WordPress: 5.0 or higher
- PHP: 7.4 or higher
- MySQL: 5.6 or higher
- HTTPS: Recommended

RECOMMENDED REQUIREMENTS:
- WordPress: 6.0+
- PHP: 8.0+
- Memory: 128MB+ (256MB recommended)
- Elementor: Latest version

══════════════════════════════════════════════════════════
CREDITS
══════════════════════════════════════════════════════════

Original Website: marbleclinicrestoration.com
Theme Developer: Nauman Ellahi
Conversion Method: HTTrack → WordPress Theme
Framework: WordPress + Elementor
Version: 1.0
License: GPL v2 or later

Font Awesome Free 5.15.1
License: https://fontawesome.com/license/free
Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT

══════════════════════════════════════════════════════════

Thank you for using Marble Elementor Theme!

For questions or issues, refer to migration_log.txt for 
detailed technical information.

══════════════════════════════════════════════════════════
