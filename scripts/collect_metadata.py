import json
import re
from pathlib import Path

from bs4 import BeautifulSoup


DATA_ROOT = Path(__file__).resolve().parent.parent
HTML_FILES = [
    "index.html",
    "index.html@p=67.tmp.html",
    "index.html@p=103.tmp.html",
    "index.html@p=118.tmp.html",
    "index.html@p=166.tmp.html",
    "index.html@p=181.tmp.html",
    "index.html@p=600.tmp.html",
    "index.html@p=605.tmp.html",
    "index.html@p=610.tmp.html",
    "index.html@p=620.tmp.html",
    "index.html@p=625.tmp.html",
    "index.html@p=630.tmp.html",
    "index.html@p=635.tmp.html",
    "index.html@p=640.tmp.html",
    "index.html@p=764.tmp.html",
    "index.html@p=769.tmp.html",
]


def normalize_slug(url: str) -> str:
    if not url:
        return ""
    slug = url.split("//", 1)[-1]
    slug = slug.split("/", 1)[-1]
    slug = slug.split("?", 1)[0]
    slug = slug.rstrip("/")
    return slug


def extract_metadata(path: Path):
    html = path.read_text(encoding="utf-8", errors="ignore")
    soup = BeautifulSoup(html, "html.parser")

    title = soup.title.string.strip() if soup.title and soup.title.string else None
    og_url = soup.find("meta", attrs={"property": "og:url"})
    og_url = og_url["content"].strip() if og_url and og_url.get("content") else None

    body = soup.body
    post_id = None
    if body and body.get("class"):
        for cls in body["class"]:
            match = re.match(r"(?:page|post|elementor)-id-(\d+)", cls)
            if match:
                post_id = int(match.group(1))
                break

    elementor_root = soup.find(attrs={"data-elementor-type": re.compile(".*")})
    elementor_id = None
    if elementor_root and elementor_root.get("data-elementor-id"):
        try:
            elementor_id = int(elementor_root["data-elementor-id"])
        except ValueError:
            elementor_id = elementor_root["data-elementor-id"]

    return {
        "file": str(path.relative_to(DATA_ROOT)),
        "title": title,
        "og_url": og_url,
        "slug": normalize_slug(og_url),
        "body_post_id": post_id,
        "elementor_id": elementor_id,
    }


def main():
    records = []
    for rel in HTML_FILES:
        path = DATA_ROOT / rel
        if not path.exists():
            continue
        records.append(extract_metadata(path))

    output_path = DATA_ROOT / "analysis" / "page_metadata.json"
    output_path.parent.mkdir(exist_ok=True)
    output_path.write_text(json.dumps(records, indent=2), encoding="utf-8")
    print(f"Wrote {len(records)} records to {output_path}")


if __name__ == "__main__":
    main()
