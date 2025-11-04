Marble Replica Theme Installation
=================================

Overview
--------
This package rebuilds the Marble Clinic Restoration website as an Elementor-editable WordPress experience. The `MarbleReplica_Theme` theme installs the layout, pages, header, footer, and menus from the HTTrack source. Media, fonts, and Elementor-generated CSS are delivered inside `uploads/imported/`.

Requirements
------------
- WordPress 6.0 or newer
- PHP 7.4 or newer
- Elementor (free) 3.32.x
- Elementor Pro (required for Call To Action, Counter, Testimonial Carousel widgets)
- Contact Form 7 (required to render the contact form shortcode)

File Summary
------------
- `MarbleReplica_Theme.zip` – Installable WordPress theme containing the importer and Elementor templates.
- `uploads/imported/` – Media library clone, Elementor CSS (`elementor/css/post-*.css`), and supporting assets.
- `migration_report.txt` – Validation log of generated pages, templates, and menus.

Installation Steps
------------------
1. Extract this repository package locally.
2. Upload `MarbleReplica_Theme.zip` through **Appearance ▸ Themes ▸ Add New ▸ Upload Theme** and activate it.
3. Upload (via SFTP or hosting file manager) the entire `uploads/imported/` directory into your site’s `wp-content/uploads/` directory. The final path must be `wp-content/uploads/imported/...`.
4. Visit **Plugins ▸ Add New** and install/activate the required plugins listed above.
5. Upon theme activation the automatic importer runs once:
   - 16 Elementor pages are created and populated.
   - Elementor templates for Header and Footer are inserted and linked.
   - Navigation menus are generated and assigned to their locations.
   - CSS manifest is stored so the theme can enqueue all Elementor-generated styles from `uploads/imported/elementor/css/`.

Post-Activation Verification
----------------------------
- Check **Appearance ▸ Menus** to confirm “Main Menu”, “Secondary Menu”, and “Mobile Menu” exist. Editing these menus automatically updates the header navigation rendered by the `marble_menu` shortcode.
- Open a few pages with “Edit with Elementor” to confirm the structure matches the original layout. All widgets should be editable; hero slider layers are delivered as static HTML and may require Slider Revolution if you prefer full animation.
- Contact form blocks rely on Contact Form 7. Replace shortcode IDs with forms created on the new site if needed.

Re-running the Importer (optional)
----------------------------------
The importer is guarded by the `marble_replica_import_complete` option. To rerun it after manual changes, delete that option (via `wp option delete marble_replica_import_complete` or using a database tool) and reactivate the theme.

Support & Notes
---------------
- Slider Revolution markup is preserved as static HTML; without the plugin the first slide remains visible. Install Slider Revolution if you need original animations.
- Custom shortcodes provided by the theme power navigation rendering. Keep the `marble_menu` shortcode in place unless you tailor the header template.
- Elementor CSS is served from `uploads/imported/elementor/css`. Do not move or rename those files; otherwise update the manifest in `wp_options.marble_replica_manifest`.
