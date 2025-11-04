#!/bin/bash
# Process Content_1.zip and organize all images
# This script handles Content_1.zip, content_1.zip, or any content ZIP file

set -e

WORKSPACE="/workspace"
WP_UPLOADS="$WORKSPACE/wp-content/uploads"
TEMP_EXTRACT="$WORKSPACE/temp_content_extract"

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║     CONTENT_1.ZIP IMAGE EXTRACTION & ORGANIZATION             ║"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo ""

# Find the content ZIP file (case-insensitive)
ARCHIVE_FILE=""
for file in "$WORKSPACE/Content_1.zip" "$WORKSPACE/content_1.zip" "$WORKSPACE/CONTENT_1.ZIP" "$WORKSPACE/Content-1.zip" "$WORKSPACE/content.zip"; do
    if [ -f "$file" ]; then
        ARCHIVE_FILE="$file"
        break
    fi
done

if [ -z "$ARCHIVE_FILE" ]; then
    echo "❌ ERROR: Content ZIP file not found!"
    echo ""
    echo "Searched for:"
    echo "  - /workspace/Content_1.zip"
    echo "  - /workspace/content_1.zip"
    echo "  - /workspace/content.zip"
    echo ""
    echo "Please upload Content_1.zip to /workspace/ and run this script again."
    exit 1
fi

echo "✅ Found archive: $(basename "$ARCHIVE_FILE")"
echo "   Size: $(ls -lh "$ARCHIVE_FILE" | awk '{print $5}')"
echo ""

# Create temp extraction directory
echo "[1/6] Creating temporary extraction directory..."
rm -rf "$TEMP_EXTRACT"
mkdir -p "$TEMP_EXTRACT"
echo "      ✓ Created: $TEMP_EXTRACT"
echo ""

# Extract the archive
echo "[2/6] Extracting $(basename "$ARCHIVE_FILE")..."
unzip -q "$ARCHIVE_FILE" -d "$TEMP_EXTRACT" 2>&1 || {
    echo "      Trying alternative extraction method..."
    unzip "$ARCHIVE_FILE" -d "$TEMP_EXTRACT"
}
echo "      ✓ Extraction complete"
echo ""

# Show what was extracted
echo "[3/6] Analyzing extracted content..."
TOTAL_FILES=$(find "$TEMP_EXTRACT" -type f | wc -l)
IMAGE_FILES=$(find "$TEMP_EXTRACT" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" -o -iname "*.webp" -o -iname "*.svg" \) | wc -l)
echo "      Total files extracted: $TOTAL_FILES"
echo "      Image files found: $IMAGE_FILES"
echo ""

# Create upload directories
echo "[4/6] Ensuring WordPress uploads directory structure..."
mkdir -p "$WP_UPLOADS"/{2020/10,2021/02,2021/03,2025/02,elementor/thumbs}
echo "      ✓ Directory structure ready"
echo ""

# Copy all images maintaining any existing structure or flattening if needed
echo "[5/6] Organizing and copying images..."
echo ""

COPIED=0
SKIPPED=0

# Function to find and copy image
find_and_copy() {
    local filename="$1"
    local dest_path="$2"
    
    # Search for the file in extracted content (case-insensitive)
    local found=$(find "$TEMP_EXTRACT" -type f -iname "$filename" | head -1)
    
    if [ -n "$found" ] && [ -f "$found" ]; then
        mkdir -p "$(dirname "$dest_path")"
        cp "$found" "$dest_path"
        echo "  ✓ $filename → $(echo "$dest_path" | sed "s|$WORKSPACE/||")"
        return 0
    fi
    return 1
}

# Process each image from our list
while IFS= read -r img_path; do
    filename=$(basename "$img_path")
    dest="$WP_UPLOADS/$img_path"
    
    if find_and_copy "$filename" "$dest"; then
        ((COPIED++))
    else
        echo "  ⚠ Not found: $filename"
        ((SKIPPED++))
    fi
done < "$WORKSPACE/image_list.txt"

echo ""
echo "      Images copied: $COPIED"
echo "      Images not found: $SKIPPED"
echo ""

# Also copy any images that might be in a wp-content structure in the ZIP
echo "[6/6] Checking for wp-content structure in archive..."
if [ -d "$TEMP_EXTRACT/wp-content/uploads" ]; then
    echo "      ✓ Found wp-content/uploads in archive"
    echo "      Copying additional images..."
    cp -r "$TEMP_EXTRACT/wp-content/uploads/"* "$WP_UPLOADS/" 2>/dev/null || true
    echo "      ✓ Merged with existing structure"
elif [ -d "$TEMP_EXTRACT/uploads" ]; then
    echo "      ✓ Found uploads folder in archive"
    cp -r "$TEMP_EXTRACT/uploads/"* "$WP_UPLOADS/" 2>/dev/null || true
    echo "      ✓ Copied to wp-content/uploads"
else
    # Try to intelligently copy any remaining images
    echo "      Copying any remaining images to appropriate folders..."
    find "$TEMP_EXTRACT" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" \) -exec cp {} "$WP_UPLOADS/2021/02/" \; 2>/dev/null || true
fi
echo ""

# Final count
FINAL_COUNT=$(find "$WP_UPLOADS" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o -iname "*.gif" -o -iname "*.webp" \) | wc -l)

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                    EXTRACTION COMPLETE                        ║"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo ""
echo "📊 Statistics:"
echo "   Archive file: $(basename "$ARCHIVE_FILE")"
echo "   Total images in archive: $IMAGE_FILES"
echo "   Images now in wp-content/uploads: $FINAL_COUNT"
echo "   Successfully organized: $COPIED"
echo ""
echo "📁 Images location: $WP_UPLOADS"
echo ""

# List what's in each directory
echo "📂 Directory breakdown:"
for dir in 2020/10 2021/02 2021/03 2025/02 elementor/thumbs; do
    count=$(find "$WP_UPLOADS/$dir" -type f 2>/dev/null | wc -l)
    if [ $count -gt 0 ]; then
        echo "   $dir: $count files"
    fi
done
echo ""

# Clean up temp directory
echo "🧹 Cleaning up temporary files..."
rm -rf "$TEMP_EXTRACT"
echo "   ✓ Cleanup complete"
echo ""

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                      NEXT STEPS                               ║"
echo "╔═══════════════════════════════════════════════════════════════╗"
echo ""
echo "1. Verify images: ls -R /workspace/wp-content/uploads/"
echo "2. Update theme package with images"
echo "3. Rebuild Marble-Elementor-Theme-fixed.zip"
echo ""
