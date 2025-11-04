import json
import html
import re
from pathlib import Path
from typing import Dict, List, Optional

from bs4 import BeautifulSoup, NavigableString, Tag


ROOT = Path(__file__).resolve().parent.parent
OUTPUT_DIR = ROOT / "build" / "elementor"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)


def load_html(path: Path) -> BeautifulSoup:
    return BeautifulSoup(path.read_text(encoding="utf-8", errors="ignore"), "html.parser")


def parse_data_settings(raw: Optional[str]) -> Dict:
    if not raw:
        return {}
    raw = html.unescape(raw)
    try:
        return json.loads(raw)
    except json.JSONDecodeError:
        return {}


def collect_extra_classes(node: Tag, element_id: Optional[str], remove: Optional[List[str]] = None) -> str:
    classes = node.get("class", [])
    if not classes:
        return ""
    remove_set = {"elementor-element"}
    if element_id:
        remove_set.add(f"elementor-element-{element_id}")
    if remove:
        remove_set.update(remove)
    extras = [cls for cls in classes if cls not in remove_set]
    # preserve order and uniqueness
    seen = set()
    ordered = []
    for cls in extras:
        if cls not in seen:
            ordered.append(cls)
            seen.add(cls)
    return " ".join(ordered)


def parse_widget(node: Tag) -> Dict:
    element_id = node.get("data-id")
    data_settings = parse_data_settings(node.get("data-settings"))
    widget_type = node.get("data-widget_type", "html.default")
    widget_base = widget_type.split(".")[0]

    container = node.find(class_="elementor-widget-container")
    if container is None:
        html_content = ""
    else:
        html_content = container.decode_contents()

    extra_remove = ["elementor-widget", f"elementor-widget-{widget_base}"]
    custom_classes = collect_extra_classes(node, element_id, extra_remove)
    classes = [f"elementor-widget-{widget_base}"]
    if custom_classes:
        classes.append(custom_classes)

    data_settings.update(
        {
            "html": html_content,
            "_css_classes": " ".join(cls for cls in classes if cls),
        }
    )

    return {
        "id": element_id,
        "elType": "widget",
        "settings": data_settings,
        "elements": [],
        "widgetType": "html",
    }


def iter_direct_children(node: Tag) -> List[Tag]:
    children = []
    for child in node.children:
        if isinstance(child, Tag):
            children.append(child)
    return children


def parse_column(node: Tag) -> Dict:
    element_id = node.get("data-id")
    data_settings = parse_data_settings(node.get("data-settings"))
    if "_column_size" not in data_settings:
        for cls in node.get("class", []):
            m = re.match(r"elementor-col-(\d+)", cls)
            if m:
                data_settings["_column_size"] = int(m.group(1))
                break

    custom_classes = collect_extra_classes(node, element_id)
    if custom_classes:
        data_settings.setdefault("_css_classes", custom_classes)

    elements = []
    wrap = node.find(class_="elementor-widget-wrap")
    if wrap:
        for child in iter_direct_children(wrap):
            if child.has_attr("data-element_type"):
                el_type = child.get("data-element_type")
                if el_type == "widget":
                    elements.append(parse_widget(child))
                elif el_type == "section":
                    elements.append(parse_section(child))

    return {
        "id": element_id,
        "elType": "column",
        "settings": data_settings,
        "elements": elements,
    }


def parse_section(node: Tag) -> Dict:
    element_id = node.get("data-id")
    data_settings = parse_data_settings(node.get("data-settings"))

    custom_classes = collect_extra_classes(node, element_id)
    if custom_classes:
        data_settings.setdefault("_css_classes", custom_classes)

    elements = []
    container = node.find(class_="elementor-container")
    if container:
        for col in iter_direct_children(container):
            if col.has_attr("data-element_type") and col.get("data-element_type") == "column":
                elements.append(parse_column(col))

    is_inner = "elementor-inner-section" in (node.get("class") or [])

    return {
        "id": element_id,
        "elType": "section",
        "settings": data_settings,
        "elements": elements,
        "isInner": is_inner,
    }


def parse_document(soup: BeautifulSoup, doc_type: str, doc_id: Optional[int]) -> Dict:
    root = soup.find(attrs={"data-elementor-type": doc_type})
    if not root:
        return {}
    sections = []
    for child in iter_direct_children(root):
        if child.name == "section" and child.get("data-element_type") == "section":
            sections.append(parse_section(child))
    return {
        "id": doc_id,
        "type": doc_type,
        "elements": sections,
    }


def extract_documents(path: Path) -> Dict[str, Dict]:
    soup = load_html(path)
    documents: Dict[str, Dict] = {}

    body = soup.body
    page_id = None
    if body and body.get("class"):
        for cls in body["class"]:
            match = re.match(r"page-id-(\d+)", cls)
            if match:
                page_id = int(match.group(1))
                break

    # parse page content
    if page_id is not None:
        doc = parse_document(soup, "wp-page", page_id)
        if doc:
            documents[f"page_{page_id}"] = doc

    # parse header/footer templates if present
    for tpl_type in ("header", "footer"):
        tpl = soup.find(attrs={"data-elementor-type": tpl_type})
        if tpl:
            try:
                tpl_id = int(tpl.get("data-elementor-id"))
            except (TypeError, ValueError):
                tpl_id = tpl.get("data-elementor-id")
            doc = parse_document(soup, tpl_type, tpl_id)
            if doc:
                documents[f"{tpl_type}_{tpl_id}"] = doc

    return documents


def main():
    all_docs = {}
    for html_file in sorted(ROOT.glob("index.html*")):
        docs = extract_documents(html_file)
        if docs:
            all_docs[str(html_file.name)] = docs

    output_path = OUTPUT_DIR / "elementor_data.json"
    output_path.write_text(json.dumps(all_docs, indent=2), encoding="utf-8")
    print(f"Saved Elementor data to {output_path}")


if __name__ == "__main__":
    main()
