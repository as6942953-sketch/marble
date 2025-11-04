#!/bin/bash
# Automated Image Extraction and Organization Script
# This script extracts content.7z and organizes all images according to the URLs in HTML files

set -e

WORKSPACE="/workspace"
ARCHIVE_FILE="$WORKSPACE/content.7z"
WP_UPLOADS="$WORKSPACE/wp-content/uploads"
TEMP_EXTRACT="$WORKSPACE/temp_extract"

echo "=========================================="
echo "Image Extraction and Organization Script"
echo "=========================================="
echo ""

# Check if content.7z exists
if [ ! -f "$ARCHIVE_FILE" ]; then
    echo "ERROR: content.7z not found in $WORKSPACE"
    echo "Please upload content.7z to the workspace directory"
    echo ""
    echo "Expected location: $ARCHIVE_FILE"
    exit 1
fi

echo "✓ Found content.7z"
echo "  Size: $(ls -lh "$ARCHIVE_FILE" | awk '{print $5}')"
echo ""

# Create temp extraction directory
echo "Creating temporary extraction directory..."
mkdir -p "$TEMP_EXTRACT"

# Extract the archive
echo "Extracting content.7z..."
7z x "$ARCHIVE_FILE" -o"$TEMP_EXTRACT" -y

echo "✓ Extraction complete"
echo ""

# Create upload directories
echo "Creating WordPress uploads directory structure..."
mkdir -p "$WP_UPLOADS"/{2020/10,2021/02,2021/03,2025/02,elementor/thumbs}

echo "✓ Directory structure created"
echo ""

# Find all image files in extracted content
echo "Finding all images in extracted content..."
IMAGES_FOUND=$(find "$TEMP_EXTRACT" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" -o -iname "*.webp" -o -iname "*.svg" \) | wc -l)
echo "✓ Found $IMAGES_FOUND image files"
echo ""

# Copy images to correct locations
echo "Organizing images into wp-content/uploads structure..."
echo ""

# Function to copy image if source exists
copy_image() {
    local source_pattern="$1"
    local dest_path="$2"
    
    # Search for the file in extracted content
    local found_files=$(find "$TEMP_EXTRACT" -type f -iname "$source_pattern" 2>/dev/null)
    
    if [ -n "$found_files" ]; then
        while IFS= read -r source_file; do
            if [ -f "$source_file" ]; then
                cp "$source_file" "$dest_path"
                echo "  ✓ Copied: $(basename "$source_file") → $dest_path"
                return 0
            fi
        done <<< "$found_files"
    fi
    
    echo "  ⚠ Not found: $source_pattern"
    return 1
}

# Process each image from the list
COPIED=0
MISSING=0

while IFS= read -r img_path; do
    # Extract just the filename
    filename=$(basename "$img_path")
    # Extract the directory path
    dir_path=$(dirname "$img_path")
    
    # Create destination directory
    mkdir -p "$WP_UPLOADS/$dir_path"
    
    # Try to copy the image
    if copy_image "$filename" "$WP_UPLOADS/$img_path"; then
        ((COPIED++))
    else
        ((MISSING++))
    fi
    
done < "$WORKSPACE/image_list.txt"

echo ""
echo "=========================================="
echo "ORGANIZATION COMPLETE"
echo "=========================================="
echo ""
echo "Statistics:"
echo "  Images in archive: $IMAGES_FOUND"
echo "  Images needed: 68"
echo "  Successfully copied: $COPIED"
echo "  Not found: $MISSING"
echo ""
echo "Images organized in: $WP_UPLOADS"
echo ""

# Clean up temp directory
echo "Cleaning up temporary files..."
rm -rf "$TEMP_EXTRACT"
echo "✓ Cleanup complete"
echo ""

echo "=========================================="
echo "NEXT STEPS:"
echo "=========================================="
echo "1. Verify images in: wp-content/uploads/"
echo "2. Update theme package with images"
echo "3. Test all pages to ensure images load correctly"
echo ""
