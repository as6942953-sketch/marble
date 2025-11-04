import json
from pathlib import Path

from build_elementor_data import extract_documents, ROOT


OUTPUT_PATH = ROOT / "build" / "elementor" / "import_payload.json"


def load_metadata() -> list:
    metadata_path = ROOT / "analysis" / "page_metadata.json"
    if not metadata_path.exists():
        raise FileNotFoundError("Run collect_metadata.py before building import payload")
    return json.loads(metadata_path.read_text(encoding="utf-8"))


def build_replacements(metadata: list) -> dict:
    replacements = {}
    for entry in metadata:
        slug = entry.get("slug", "") or ""
        if slug and not slug.startswith("/"):
            replacement = f"/{slug}/"
        else:
            replacement = "/"

        source_file = entry.get("file")
        if source_file:
            replacements[source_file] = replacement

        og_url = entry.get("og_url")
        if og_url:
            replacements[og_url] = replacement

        # also replace canonical variations without .tmp suffix
        if source_file and "@" in source_file:
            base = source_file.split("@", 1)[0]
            replacements.setdefault(base, replacement)

    # ensure longer keys replace first
    return dict(sorted(replacements.items(), key=lambda item: len(item[0]), reverse=True))


def apply_replacements(elements, replacements):
    for element in elements:
        settings = element.get("settings", {})
        html_value = settings.get("html")
        if html_value:
            for src, dest in replacements.items():
                if src in html_value:
                    html_value = html_value.replace(src, dest)
            settings["html"] = html_value
        if element.get("elements"):
            apply_replacements(element["elements"], replacements)


def main():
    metadata = load_metadata()
    replacements = build_replacements(metadata)
    pages = {}
    templates = {}

    for entry in metadata:
        html_file = ROOT / entry["file"]
        docs = extract_documents(html_file)

        page_key = f"page_{entry['body_post_id']}"
        page_doc = docs.get(page_key)
        if page_doc:
            # apply replacements for internal links
            apply_replacements(page_doc["elements"], replacements)
            pages[str(entry["body_post_id"])] = {
                "file": entry["file"],
                "title": entry["title"],
                "slug": entry["slug"],
                "og_url": entry["og_url"],
                "elements": page_doc["elements"],
                "document_type": page_doc["type"],
            }

        for doc_key, doc_val in docs.items():
            if doc_key.startswith("header_") or doc_key.startswith("footer_"):
                tpl_id = str(doc_val.get("id"))
                if tpl_id not in templates:
                    apply_replacements(doc_val["elements"], replacements)
                    templates[tpl_id] = {
                        "document_type": doc_val["type"],
                        "elements": doc_val["elements"],
                    }

    payload = {
        "pages": pages,
        "templates": templates,
    }

    OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_PATH.write_text(json.dumps(payload, indent=2), encoding="utf-8")
    print(f"Saved import payload to {OUTPUT_PATH}")


if __name__ == "__main__":
    main()
