Marble Replica Theme – Installation Guide
=========================================

Requirements
------------
- WordPress 6.0 or later with PHP 7.4+
- Elementor (free) plugin activated
- Recommended: Elementor Pro, Slider Revolution, Elementskit Lite, Contact Form 7, Image Hover Effects Add-on (restores drag-and-drop control for the embedded components)

Included Artifacts
------------------
1. `MarbleReplica_Theme.zip` – Elementor-ready WordPress theme that auto-imports all captured pages/templates on activation.
2. `uploads/imported/` – Media library files mirrored from the original site (preserve directory tree when copying).
3. `migration_report.txt` – Summary of automated rebuild and manual follow-ups.

Installation Steps
------------------
1. Upload media assets:
   - Extract the contents of `uploads/imported/` into the destination server’s `wp-content/uploads/` directory. Overwrite existing files if prompted to ensure paths match the imported Elementor markup.

2. Install required plugins:
   - Activate **Elementor** (required).
   - Activate **Elementor Pro** to assign global header/footer templates (optional but recommended).
   - Install other helper plugins (Slider Revolution, Elementskit Lite, etc.) if you plan to edit those legacy sections with their native widgets.

3. Install the theme:
   - In WordPress admin go to **Appearance → Themes → Add New → Upload Theme**.
   - Upload `MarbleReplica_Theme.zip`, install, then activate it.

4. Automatic import runs once on activation:
   - Pages (IDs 11, 67, 103, 118, 166, 181, 600, 605, 610, 620, 625, 630, 635, 640, 764, 769) are recreated with Elementor data and set to **Publish**.
   - Elementor templates (Header IDs 1177 & 1213, Footer ID 1160) are imported into **Templates → Saved Templates**.
   - The home page (ID 11) is assigned as the static front page.
   - Elementor caches are cleared and permalinks flushed.

5. Post-activation checks:
   - Navigate to **Elementor → Tools → Regenerate CSS & Data** if styles appear outdated.
   - Inside **Elementor → Theme Builder**, assign the imported header/footer templates to their respective site locations (requires Elementor Pro). Until assigned, the theme serves a minimal fallback header/footer.
   - Open each page with “Edit with Elementor” to verify layout fidelity. Content is stored as HTML widgets that retain the original markup and styling classes.

Re-running the importer
-----------------------
- The importer runs only once. To trigger it again, delete the `marble_replica_import_completed` option (via WP-CLI or the Options table) and reactivate the theme.
- Adding or modifying Elementor JSON can be done by updating `wp-content/themes/marblereplica/data/import_payload.json` and repeating the activation.

Limitations & Notes
-------------------
- HTML widget fallback preserves visual fidelity but editing granular widget settings requires working directly inside each HTML block.
- Media items are not programmatically registered; WordPress will serve them directly from `/wp-content/uploads/…`. If needed, use a media import plugin (e.g., “Add From Server”) to register attachments.
- Slider Revolution markup is static. Install the plugin and import a slider manually if you need to regain the visual editor or dynamic animation controls.
- All internal links were remapped to WordPress-friendly permalinks (e.g., `/about-us/`). Update permalinks if you change slugs post-import.

Support & Customisation
-----------------------
- The `scripts/` directory contains Python utilities used to derive Elementor JSON from the HTTrack snapshot should further regeneration be required.
- Custom CSS/JS can be enqueued through `functions.php` using standard WordPress hooks.
- For advanced Elementor editing, consider rebuilding critical sections with native widgets using the imported markup as a visual guide.
