import json
import html
import uuid
import re
from dataclasses import dataclass, field
from pathlib import Path
from typing import Dict, List, Optional
from urllib.parse import urlparse

from bs4 import BeautifulSoup
from bs4.element import Tag, NavigableString


REPO_ROOT = Path(__file__).resolve().parent.parent
HTML_DIR = REPO_ROOT
UPLOADS_REL = Path("Content/wp-content/uploads")


def load_html(path: Path) -> BeautifulSoup:
    return BeautifulSoup(path.read_text(encoding="utf-8"), "html.parser")


def parse_meta(soup: BeautifulSoup):
    title_tag = soup.find("title")
    title = title_tag.get_text(strip=True) if title_tag else ""
    canonical = soup.find("link", rel="canonical")
    og_url = soup.find("meta", attrs={"property": "og:url"})
    url = None
    if og_url and og_url.get("content"):
        url = og_url["content"]
    elif canonical and canonical.get("href"):
        url = canonical["href"]
    slug = None
    if url:
        parsed = urlparse(url)
        path = parsed.path or "/"
        slug = path.strip("/")
        if not slug:
            slug = "home"
        else:
            slug = slug.split("/")[-1]
    return {"title": title, "url": url, "slug": slug}


def tidy_html(fragment: str) -> str:
    fragment = fragment.strip()
    return fragment


def parse_style(style_str: Optional[str]) -> Dict[str, str]:
    result: Dict[str, str] = {}
    if not style_str:
        return result
    for part in style_str.split(";"):
        if not part.strip():
            continue
        if ":" not in part:
            continue
        prop, value = part.split(":", 1)
        result[prop.strip().lower()] = value.strip()
    return result


def ensure_uuid() -> str:
    return uuid.uuid4().hex[:8]


@dataclass
class ElementorNode:
    id: str
    el_type: str
    data: Dict
    elements: List["ElementorNode"] = field(default_factory=list)
    widget_type: Optional[str] = None
    is_inner: bool = False

    def to_dict(self) -> Dict:
        payload = {
            "id": self.id,
            "elType": self.el_type,
            "isInner": self.is_inner,
            "settings": self.data,
            "elements": [child.to_dict() for child in self.elements],
        }
        if self.el_type == "widget" and self.widget_type:
            payload["widgetType"] = self.widget_type
        return payload


class BlueprintBuilder:
    def __init__(self):
        self.meta: Dict[str, Dict] = {}
        self.file_to_slug: Dict[str, str] = {}
        self.slug_to_title: Dict[str, str] = {}

    def load_meta(self, paths: List[Path]):
        for path in paths:
            soup = load_html(path)
            info = parse_meta(soup)
            self.meta[path.name] = info
            slug = info.get("slug") or path.stem
            self.file_to_slug[path.name] = slug
            if slug:
                self.slug_to_title[slug] = info.get("title", "")

    # Placeholder conversions -------------------------------------------------
    def convert_media_url(self, url: Optional[str]) -> Optional[str]:
        if not url:
            return url
        url = html.unescape(url)
        if url.startswith("//"):
            url = "https:" + url
        parsed = urlparse(url)
        if parsed.scheme in ("http", "https") and parsed.netloc:
            if "marbleclinicrestoration.com" in parsed.netloc and "/wp-content/uploads/" in parsed.path:
                rel = parsed.path.split("/wp-content/uploads/")[-1]
                return f"{{{{media:{rel}}}}}"
            return url
        if url.startswith("wp-content/uploads/"):
            rel = url.split("wp-content/uploads/")[-1]
            return f"{{{{media:{rel}}}}}"
        if url.startswith("../"):
            return url
        return url

    def convert_link(self, href: Optional[str]) -> Optional[str]:
        if not href:
            return href
        href = html.unescape(href)
        if href.startswith(("mailto:", "tel:", "#")):
            return href
        parsed = urlparse(href)
        if parsed.scheme in ("http", "https") and parsed.netloc:
            if "marbleclinicrestoration.com" in parsed.netloc:
                slug = parsed.path.strip("/")
                if not slug:
                    slug = "home"
                else:
                    slug = slug.split("/")[-1]
                return f"{{{{page:{slug}}}}}"
            return href
        # Relative link captured by HTTrack
        if href in self.file_to_slug:
            return f"{{{{page:{self.file_to_slug[href]}}}}}"
        if href.startswith("index.html") and href in self.file_to_slug:
            return f"{{{{page:{self.file_to_slug[href]}}}}}"
        return href

    def normalize_rich_html(self, html_content: str) -> str:
        fragment = BeautifulSoup(html_content, "html.parser")
        for tag in fragment.find_all("a"):
            href = tag.get("href")
            new_href = self.convert_link(href)
            if new_href:
                tag["href"] = new_href
        for tag in fragment.find_all("img"):
            src = tag.get("src")
            new_src = self.convert_media_url(src)
            if new_src:
                tag["src"] = new_src
        return fragment.decode()

    # Elementor parsing -------------------------------------------------------
    def parse_section(self, section_tag: Tag, is_inner: bool = False) -> ElementorNode:
        section_id = section_tag.get("data-id") or ensure_uuid()
        settings = self.parse_settings(section_tag)
        classes = section_tag.get("class", [])
        is_inner_section = "elementor-inner-section" in classes
        node = ElementorNode(section_id, "section", settings, is_inner=is_inner_section)
        containers = [child for child in section_tag.find_all(class_="elementor-container", recursive=False)]
        if not containers:
            containers = section_tag.find_all(class_="elementor-container")
        for container in containers:
            for column in container.find_all(attrs={"data-element_type": "column"}, recursive=False):
                node.elements.append(self.parse_column(column))
        return node

    def parse_column(self, column_tag: Tag) -> ElementorNode:
        column_id = column_tag.get("data-id") or ensure_uuid()
        settings = self.parse_settings(column_tag)
        size = self.extract_column_size(column_tag.get("class", []))
        settings.setdefault("_column_size", size)
        settings.setdefault("_inline_size", size)
        is_inner = "elementor-inner-column" in column_tag.get("class", [])
        node = ElementorNode(column_id, "column", settings, is_inner=is_inner)
        wrap = column_tag.find(class_="elementor-widget-wrap")
        if not wrap:
            return node
        for child in wrap.children:
            if isinstance(child, NavigableString):
                continue
            if not isinstance(child, Tag):
                continue
            el_type = child.get("data-element_type")
            if el_type == "section":
                node.elements.append(self.parse_section(child, is_inner=True))
            elif el_type == "widget":
                node.elements.append(self.parse_widget(child))
        return node

    def parse_widget(self, widget_tag: Tag) -> ElementorNode:
        widget_id = widget_tag.get("data-id") or ensure_uuid()
        widget_type_full = widget_tag.get("data-widget_type", "")
        widget_type = widget_type_full.split(".", 1)[0]
        settings = self.widget_settings(widget_type, widget_tag)
        settings.update(self.parse_settings(widget_tag))
        node = ElementorNode(widget_id, "widget", settings, widget_type=widget_type)
        # Adjust for fallbacks
        if settings.get("__convert_to_html"):
            content = settings.pop("__html_content", "")
            css_classes = settings.pop("__css_classes", "")
            settings.pop("__convert_to_html", None)
            node.widget_type = "html"
            node.data = settings
            node.data["content"] = content
            if css_classes:
                existing = node.data.get("_css_classes")
                node.data["_css_classes"] = f"{existing} {css_classes}".strip() if existing else css_classes
        return node

    def parse_settings(self, tag: Tag) -> Dict:
        settings_raw = tag.get("data-settings")
        if not settings_raw:
            return {}
        try:
            settings_json = html.unescape(settings_raw)
            return json.loads(settings_json)
        except Exception:
            return {}

    @staticmethod
    def extract_column_size(classes: List[str]) -> int:
        for cls in classes:
            if cls.startswith("elementor-col-"):
                tail = cls.split("-")[-1]
                if tail.isdigit():
                    return int(tail)
        return 100

    # Widget handlers --------------------------------------------------------
    def widget_settings(self, widget_type: str, widget_tag: Tag) -> Dict:
        handler = getattr(self, f"handle_{widget_type}", None)
        if handler:
            try:
                return handler(widget_tag)
            except Exception:
                pass
        return self.fallback_html(widget_tag)

    def handle_heading(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        heading = None
        for level in ("h1", "h2", "h3", "h4", "h5", "h6"):
            heading = container.find(level) if container else None
            if heading:
                break
        if not heading:
            return {}
        classes = heading.get("class", [])
        size = "default"
        for cls in classes:
            if cls.startswith("elementor-size-"):
                size = cls.split("-", 2)[-1]
        style_map = parse_style(heading.get("style"))
        settings: Dict[str, str] = {
            "title": heading.decode_contents(),
            "header_size": heading.name,
            "size": size,
        }
        if "color" in style_map:
            settings["title_color"] = style_map["color"]
        parent_classes = widget_tag.get("class", [])
        for cls in parent_classes:
            if cls.startswith("elementor-widget-align-"):
                settings["align"] = cls.split("-")[-1]
        return settings

    def handle_text_editor(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        if not container:
            return {"editor": ""}
        html_content = container.decode_contents()
        return {"editor": self.normalize_rich_html(html_content)}

    def handle_image(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        if not container:
            return {}
        img = container.find("img")
        if not img:
            return {}
        url = self.convert_media_url(img.get("src"))
        settings: Dict = {
            "image": {
                "url": url,
                "id": "",
                "size": "",
                "alt": img.get("alt", ""),
            },
            "image_size": "full",
        }
        parent_link = img.find_parent("a")
        if parent_link:
            settings["link_to"] = "custom"
            settings["link"] = {"url": self.convert_link(parent_link.get("href"))}
        else:
            settings["link_to"] = "none"
        return settings

    def handle_icon_box(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        wrapper = container.find(class_="elementor-icon-box-wrapper") if container else None
        if not wrapper:
            return {}
        icon_tag = wrapper.find(class_="elementor-icon")
        icon_value = None
        if icon_tag and icon_tag.find("i"):
            icon_classes = icon_tag.find("i").get("class", [])
            icon_value = " ".join(icon_classes)
        title_tag = wrapper.find(class_="elementor-icon-box-title")
        desc_tag = wrapper.find(class_="elementor-icon-box-description")
        settings: Dict = {
            "title_text": title_tag.get_text(strip=True) if title_tag else "",
            "description_text": desc_tag.decode_contents() if desc_tag else "",
        }
        if icon_value:
            settings.update(self.icon_settings(icon_value))
        link_tag = wrapper.find("a")
        if link_tag:
            settings["link"] = {"url": self.convert_link(link_tag.get("href"))}
        return settings

    def handle_icon_list(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        items = []
        if container:
            for item in container.find_all(class_="elementor-icon-list-item"):
                icon_value = None
                icon_holder = item.find(class_="elementor-icon-list-icon")
                if icon_holder and icon_holder.find("i"):
                    icon_value = " ".join(icon_holder.find("i").get("class", []))
                text_holder = item.find(class_="elementor-icon-list-text")
                link_tag = item.find("a")
                icon_data = self.icon_settings(icon_value) if icon_value else {"icon": "", "selected_icon": {"value": "", "library": ""}}
                entry = {
                    "text": text_holder.get_text(strip=True) if text_holder else "",
                    "icon": icon_data.get("icon"),
                    "selected_icon": icon_data.get("selected_icon"),
                    "link": {"url": self.convert_link(link_tag.get("href")) if link_tag else ""},
                    "_id": ensure_uuid(),
                }
                items.append(entry)
        return {"icon_list": items}

    def handle_social_icons(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        items = []
        if container:
            for anchor in container.find_all("a", class_=re.compile(r"elementor-social-icon")):
                classes = anchor.get("class", [])
                social = ""
                for cls in classes:
                    if cls.startswith("elementor-social-icon-"):
                        social = cls.replace("elementor-social-icon-", "")
                        break
                items.append({
                    "social_icon": social,
                    "link": {"url": self.convert_link(anchor.get("href")) or ""},
                    "_id": ensure_uuid(),
                })
        return {"social_icon_list": items}

    def handle_divider(self, widget_tag: Tag) -> Dict:
        return {}

    def handle_spacer(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        inner = container.find(class_="elementor-spacer-inner") if container else None
        style = parse_style(inner.get("style")) if inner else {}
        size = style.get("height") if style else None
        if size and size.endswith("px"):
            try:
                numeric = int(float(size.rstrip("px")))
            except ValueError:
                numeric = 50
        else:
            numeric = 50
        return {"space": numeric}

    def handle_image_box(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        if not container:
            return {}
        img = container.find("img")
        title_tag = container.find(class_="elementor-image-box-title")
        desc_tag = container.find(class_="elementor-image-box-description")
        settings: Dict = {
            "title_text": title_tag.get_text(strip=True) if title_tag else "",
            "description_text": desc_tag.decode_contents() if desc_tag else "",
        }
        if img:
            settings["image"] = {
                "url": self.convert_media_url(img.get("src")),
                "id": "",
                "size": "",
                "alt": img.get("alt", ""),
            }
        link_tag = container.find("a")
        if link_tag:
            settings["link"] = {"url": self.convert_link(link_tag.get("href"))}
        return settings

    def handle_button(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        button = container.find("a", class_=re.compile(r"elementor-button")) if container else None
        if not button:
            return {}
        text = button.get_text(strip=True)
        return {
            "text": text,
            "link": {"url": self.convert_link(button.get("href")) or ""},
            "align": "center",
        }

    def handle_counter(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        number = container.find(class_="elementor-counter-number") if container else None
        title = container.find(class_="elementor-counter-title") if container else None
        prefix = container.find(class_="elementor-counter-number-prefix") if container else None
        suffix = container.find(class_="elementor-counter-number-suffix") if container else None
        ending = number.get("data-to-value") if number else "0"
        starting = number.get("data-from-value") if number else "0"
        duration = number.get("data-duration") if number else "2000"
        return {
            "starting_number": float(starting) if starting else 0,
            "ending_number": float(ending) if ending else 0,
            "duration": float(duration) if duration else 2000,
            "prefix": prefix.get_text(strip=True) if prefix else "",
            "suffix": suffix.get_text(strip=True) if suffix else "",
            "title": title.get_text(strip=True) if title else "",
        }

    def handle_call_to_action(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        title = container.find(class_="elementor-cta__title") if container else None
        description = container.find(class_="elementor-cta__description") if container else None
        button = container.find(class_="elementor-cta__button") if container else None
        settings: Dict = {
            "title": title.get_text(strip=True) if title else "",
            "description": description.decode_contents() if description else "",
        }
        if button:
            settings["button_text"] = button.get_text(strip=True)
            settings["link"] = {"url": self.convert_link(button.get("href")) or ""}
        bg = container.find(class_="elementor-cta__bg") if container else None
        if bg and bg.get("style"):
            style_map = parse_style(bg["style"])
            if "background-image" in style_map:
                image_url = style_map["background-image"].strip("url()")
                settings["background_image"] = {
                    "url": self.convert_media_url(image_url.strip('"\'')),
                    "id": "",
                    "size": "",
                }
        return settings

    def handle_gallery(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-image-gallery")
        gallery_items = []
        if container:
            for link in container.find_all("a"):
                img = link.find("img")
                url = self.convert_media_url(img.get("src")) if img else None
                gallery_items.append({
                    "id": "",
                    "url": url,
                })
        return {"gallery": gallery_items}

    def handle_google_maps(self, widget_tag: Tag) -> Dict:
        iframe = widget_tag.find("iframe")
        if not iframe:
            return {}
        return {"address": iframe.get("src", ""), "zoom": 12}

    def handle_shortcode(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        return {"shortcode": container.get_text(strip=True) if container else ""}

    def handle_testimonial_carousel(self, widget_tag: Tag) -> Dict:
        return self.fallback_html(widget_tag)

    def handle_slider_revolution(self, widget_tag: Tag) -> Dict:
        return self.fallback_html(widget_tag)

    def handle_e_image_hover_effects(self, widget_tag: Tag) -> Dict:
        return self.fallback_html(widget_tag)

    def handle_wp_widget_widget_mfn_menu(self, widget_tag: Tag) -> Dict:
        return self.fallback_html(widget_tag)

    def handle_theme_site_logo(self, widget_tag: Tag) -> Dict:
        return self.fallback_html(widget_tag)

    def fallback_html(self, widget_tag: Tag) -> Dict:
        container = widget_tag.find(class_="elementor-widget-container")
        content = container.decode_contents() if container else ""
        classes = widget_tag.get("class", [])
        css_classes = " ".join(cls for cls in classes if cls.startswith("elementor-widget-"))
        return {
            "__convert_to_html": True,
            "__html_content": self.normalize_rich_html(content),
            "__css_classes": css_classes,
        }

    def icon_settings(self, icon_value: Optional[str]) -> Dict:
        if not icon_value:
            return {
                "icon": "",
                "selected_icon": {"value": "", "library": ""},
            }
        icon_parts = icon_value.split()
        library = "fa-solid"
        for part in icon_parts:
            if part.startswith("fa" ) and len(part) == 3:
                if part == "fas":
                    library = "fa-solid"
                elif part == "far":
                    library = "fa-regular"
                elif part == "fab":
                    library = "fa-brands"
        return {
            "icon": icon_value,
            "selected_icon": {"value": icon_value, "library": library},
        }

    # High-level orchestration -----------------------------------------------
    def extract_page(self, path: Path) -> Optional[Dict]:
        soup = load_html(path)
        meta = self.meta.get(path.name) or {}
        main = soup.find("div", attrs={"data-elementor-type": "wp-page"})
        if not main:
            return None
        data_id = main.get("data-elementor-id")
        elements: List[ElementorNode] = []
        for child in main.children:
            if isinstance(child, Tag) and child.name == "section":
                elements.append(self.parse_section(child))
        return {
            "source": path.name,
            "slug": meta.get("slug") or path.stem,
            "title": meta.get("title", ""),
            "elementor_id": data_id,
            "elements": [node.to_dict() for node in elements],
        }

    def extract_template(self, soup: BeautifulSoup, tag_name: str, elementor_type: str) -> Optional[Dict]:
        container = soup.find(tag_name, attrs={"data-elementor-type": elementor_type})
        if not container:
            return None
        data_id = container.get("data-elementor-id")
        elements: List[ElementorNode] = []
        for child in container.find_all("section", recursive=False):
            elements.append(self.parse_section(child))
        return {
            "elementor_id": data_id,
            "elements": [node.to_dict() for node in elements],
        }

    def build(self) -> Dict:
        html_files = sorted(p for p in HTML_DIR.glob("index*.html") if p.is_file())
        self.load_meta(html_files)
        pages = []
        templates = {}
        # Use main index for templates
        index_soup = load_html(HTML_DIR / "index.html")
        header_tpl = self.extract_template(index_soup, "header", "header")
        footer_tpl = self.extract_template(index_soup, "footer", "footer")
        if header_tpl:
            templates["header"] = header_tpl
        if footer_tpl:
            templates["footer"] = footer_tpl
        for path in html_files:
            page = self.extract_page(path)
            if page:
                pages.append(page)
        return {
            "meta": self.meta,
            "pages": pages,
            "templates": templates,
        }


def main():
    builder = BlueprintBuilder()
    blueprint = builder.build()
    out_path = REPO_ROOT / "blueprint.json"
    out_path.write_text(json.dumps(blueprint, indent=2), encoding="utf-8")


if __name__ == "__main__":
    main()
