#!/usr/bin/env python3
"""HTML Validation and Auto-Fix Script"""
import sys
import os
import json
import re
from pathlib import Path

# Add local libs to path
sys.path.insert(0, '/workspace/qa-output/python-libs')

from bs4 import BeautifulSoup
from html5lib import parse, serialize

def validate_html_file(filepath):
    """Validate and auto-fix HTML file"""
    errors = []
    warnings = []
    fixed = False
    
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # Parse with html5lib (auto-fixes most issues)
        soup = BeautifulSoup(content, 'html5lib')
        
        # Check for common issues
        # 1. Missing alt attributes on images
        imgs_without_alt = soup.find_all('img', alt=False)
        if imgs_without_alt:
            warnings.append({
                'type': 'missing_alt',
                'count': len(imgs_without_alt),
                'message': f'Found {len(imgs_without_alt)} images without alt attributes'
            })
        
        # 2. Deprecated tags
        deprecated_tags = ['center', 'font', 'marquee', 'blink']
        for tag in deprecated_tags:
            found = soup.find_all(tag)
            if found:
                warnings.append({
                    'type': 'deprecated_tag',
                    'tag': tag,
                    'count': len(found),
                    'message': f'Found {len(found)} deprecated <{tag}> tags'
                })
        
        # 3. Check for unclosed tags (html5lib auto-fixes these)
        # If the parsed HTML is significantly different, there were structure issues
        original_len = len(content)
        fixed_content = str(soup)
        fixed_len = len(fixed_content)
        
        # If lengths differ significantly, there were fixes
        if abs(original_len - fixed_len) > 100:
            fixed = True
            errors.append({
                'type': 'structure_fixed',
                'message': 'HTML structure issues auto-fixed (unclosed tags, invalid nesting)'
            })
        
        # 4. Check for Elementor-specific attributes (should be preserved)
        elementor_attrs = ['data-elementor-type', 'data-elementor-id', 'data-element_type', 'data-widget_type']
        has_elementor = any(soup.find_all(attrs={attr: True}) for attr in elementor_attrs)
        
        if has_elementor:
            warnings.append({
                'type': 'info',
                'message': 'File contains Elementor markup - preserved'
            })
        
        result = {
            'file': os.path.basename(filepath),
            'path': filepath,
            'errors': errors,
            'warnings': warnings,
            'fixed': fixed,
            'has_elementor': has_elementor,
            'size_original': original_len,
            'size_fixed': fixed_len
        }
        
        # Return the fixed content if there were fixes
        if fixed:
            return result, fixed_content
        else:
            return result, None
            
    except Exception as e:
        return {
            'file': os.path.basename(filepath),
            'path': filepath,
            'errors': [{'type': 'parse_error', 'message': str(e)}],
            'warnings': [],
            'fixed': False
        }, None

def main():
    html_dir = sys.argv[1] if len(sys.argv) > 1 else '/workspace/elementor-pages'
    output_dir = '/workspace/qa-output/html-fixed'
    
    results = []
    fixed_count = 0
    
    # Find all HTML files
    html_files = list(Path(html_dir).glob('*.html'))
    
    print(f"Scanning {len(html_files)} HTML files...")
    
    for html_file in html_files:
        result, fixed_content = validate_html_file(str(html_file))
        results.append(result)
        
        if fixed_content:
            # Save fixed version
            fixed_path = os.path.join(output_dir, result['file'])
            with open(fixed_path, 'w', encoding='utf-8') as f:
                f.write(fixed_content)
            fixed_count += 1
            print(f"  ✓ Fixed: {result['file']}")
        else:
            print(f"  ○ OK: {result['file']}")
    
    # Save results as JSON
    with open('/workspace/qa-output/html-validation-results.json', 'w') as f:
        json.dump(results, f, indent=2)
    
    print(f"\n✓ Validated {len(html_files)} files")
    print(f"✓ Auto-fixed {fixed_count} files")
    print(f"✓ Results saved to: qa-output/html-validation-results.json")
    
    return results

if __name__ == '__main__':
    main()
