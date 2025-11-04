#!/usr/bin/env python3
"""CSS Validation and Font Check Script"""
import sys
import os
import json
import re
from pathlib import Path

def check_css_file(filepath):
    """Check CSS file for issues"""
    issues = []
    external_fonts = []
    missing_assets = []
    
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # Check for external font imports
        google_fonts = re.findall(r'@import\s+url\(["\']?(https?://fonts\.googleapis\.com[^"\')\s]+)', content)
        fontawesome_cdn = re.findall(r'@import\s+url\(["\']?(https?://[^"\']*fontawesome[^"\')\s]+)', content)
        
        if google_fonts:
            external_fonts.extend([{'type': 'google_fonts', 'url': url} for url in google_fonts])
        if fontawesome_cdn:
            external_fonts.extend([{'type': 'fontawesome', 'url': url} for url in fontawesome_cdn])
        
        # Check url() references for local files
        url_refs = re.findall(r'url\(["\']?([^"\')\s]+)["\']?\)', content)
        
        for url_ref in url_refs:
            # Skip data URIs and external URLs
            if url_ref.startswith('data:') or url_ref.startswith('http'):
                continue
            
            # Check if file exists
            # Resolve relative to CSS file location
            css_dir = os.path.dirname(filepath)
            resolved_path = os.path.normpath(os.path.join(css_dir, url_ref.split('?')[0].split('#')[0]))
            
            if not os.path.exists(resolved_path):
                # Also try from workspace root
                alt_path = os.path.normpath(os.path.join('/workspace', url_ref.lstrip('../')))
                if not os.path.exists(alt_path):
                    missing_assets.append({
                        'url': url_ref,
                        'resolved': resolved_path,
                        'exists': False
                    })
        
        # Basic syntax check - look for obvious errors
        unclosed_braces = content.count('{') - content.count('}')
        if unclosed_braces != 0:
            issues.append({
                'type': 'syntax_error',
                'message': f'Unclosed braces: {unclosed_braces}'
            })
        
        return {
            'file': os.path.basename(filepath),
            'path': filepath,
            'issues': issues,
            'external_fonts': external_fonts,
            'missing_assets': missing_assets,
            'size': os.path.getsize(filepath)
        }
        
    except Exception as e:
        return {
            'file': os.path.basename(filepath),
            'path': filepath,
            'issues': [{'type': 'error', 'message': str(e)}],
            'external_fonts': [],
            'missing_assets': []
        }

def main():
    css_dir = sys.argv[1] if len(sys.argv) > 1 else '/workspace/assets/css'
    
    results = []
    
    # Find all CSS files
    css_files = list(Path(css_dir).glob('*.css'))
    
    # Also check theme CSS
    theme_css = Path('/workspace/marble-elementor-theme-fixed/assets/css')
    if theme_css.exists():
        css_files.extend(theme_css.glob('*.css'))
    
    print(f"Scanning {len(css_files)} CSS files...")
    
    total_external_fonts = 0
    total_missing = 0
    
    for css_file in css_files:
        result = check_css_file(str(css_file))
        results.append(result)
        
        if result['external_fonts']:
            print(f"  ⚠ {result['file']}: {len(result['external_fonts'])} external font references")
            total_external_fonts += len(result['external_fonts'])
        
        if result['missing_assets']:
            print(f"  ⚠ {result['file']}: {len(result['missing_assets'])} missing assets")
            total_missing += len(result['missing_assets'])
        
        if result['issues']:
            print(f"  ✗ {result['file']}: {len(result['issues'])} issues")
        elif not result['external_fonts'] and not result['missing_assets']:
            print(f"  ✓ {result['file']}: OK")
    
    # Save results
    with open('/workspace/qa-output/css-validation-results.json', 'w') as f:
        json.dump(results, f, indent=2)
    
    print(f"\n✓ Scanned {len(css_files)} CSS files")
    print(f"  External fonts: {total_external_fonts}")
    print(f"  Missing assets: {total_missing}")
    print(f"✓ Results saved to: qa-output/css-validation-results.json")

if __name__ == '__main__':
    main()
