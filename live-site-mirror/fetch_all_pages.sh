#!/bin/bash
echo "Fetching all pages from live site..."

urls=(
"https://marbleclinicrestoration.com/"
"https://marbleclinicrestoration.com/about-us/"
"https://marbleclinicrestoration.com/services/"
"https://marbleclinicrestoration.com/marble-natural-stone-restoration/"
"https://marbleclinicrestoration.com/marble-repair-restoration/"
"https://marbleclinicrestoration.com/marble-refinishing-care-maintenance/"
"https://marbleclinicrestoration.com/kitchen-island-countertops-and-refinishing/"
"https://marbleclinicrestoration.com/floors-counters-walls-maintenance/"
"https://marbleclinicrestoration.com/beverly-hills-ca/"
"https://marbleclinicrestoration.com/santa-monica-ca/"
"https://marbleclinicrestoration.com/brentwood-ca/"
"https://marbleclinicrestoration.com/calabasas-ca/"
"https://marbleclinicrestoration.com/studio-city-ca/"
"https://marbleclinicrestoration.com/contact-us/"
"https://marbleclinicrestoration.com/natural-stones-care-maintenance/"
"https://marbleclinicrestoration.com/gallery/"
)

for url in "${urls[@]}"; do
    filename=$(echo $url | sed 's|https://marbleclinicrestoration.com/||' | sed 's|/$|/index|' | sed 's|/|_|g')
    if [ -z "$filename" ]; then filename="home"; fi
    echo "Fetching $url -> ${filename}.html"
    curl -L -s -A "Mozilla/5.0" "$url" > "${filename}.html" 2>/dev/null
done

echo "✓ All pages fetched!"
ls -lh *.html | wc -l
