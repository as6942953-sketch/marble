import shutil
from pathlib import Path


SOURCE = Path("Content_1/Content/wp-content/uploads")
DEST = Path("deliverables/uploads/imported")


def clean_name(name: str) -> str:
    if "@" in name:
        return name.split("@", 1)[0]
    return name


def main() -> None:
    if not SOURCE.exists():
        raise SystemExit(f"Source path does not exist: {SOURCE}")

    for src in SOURCE.rglob("*"):
        if not src.is_file():
            continue

        rel = src.relative_to(SOURCE)
        dest = DEST / Path(*rel.parts[:-1]) / clean_name(rel.name)
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dest)

    print(f"Copied uploads to {DEST}")


if __name__ == "__main__":
    main()
