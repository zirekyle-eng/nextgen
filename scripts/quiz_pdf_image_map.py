import base64
import json
import re
import sys

import fitz


def is_noise(line: str) -> bool:
    patterns = [
        r"oak national academy",
        r"open government licence",
        r"produced in partnership",
        r"licensed on the",
        r"terms & conditions",
        r"exit quiz",
        r"multiplicative relationships",
    ]
    lowered = line.lower()
    return any(pat in lowered for pat in patterns)


def find_question_anchors(page):
    anchors = []
    text_dict = page.get_text("dict")
    for block in text_dict.get("blocks", []):
        if block.get("type") != 0:
            continue
        for line in block.get("lines", []):
            spans = line.get("spans", [])
            if not spans:
                continue
            line_text = "".join(span.get("text", "") for span in spans).strip()
            if not line_text or is_noise(line_text):
                continue
            match = re.match(r"^(\d{1,3})\s*[A-Za-z]", line_text)
            if not match:
                continue
            number = match.group(1)
            y0 = min(span.get("bbox", [0, 0, 0, 0])[1] for span in spans)
            anchors.append((number, y0))

    anchors.sort(key=lambda item: item[1])
    return anchors


def iter_image_blocks(page):
    text_dict = page.get_text("dict")
    blocks = [block for block in text_dict.get("blocks", []) if block.get("type") == 1]
    blocks.sort(key=lambda blk: blk.get("bbox", [0, 0, 0, 0])[1])
    return blocks


def main(pdf_path: str):
    doc = fitz.open(pdf_path)
    question_images = {}

    for page in doc:
        anchors = find_question_anchors(page)
        if not anchors:
            continue

        image_blocks = iter_image_blocks(page)
        if not image_blocks:
            continue

        for block in image_blocks:
            bbox = block.get("bbox", [0, 0, 0, 0])
            width = bbox[2] - bbox[0]
            height = bbox[3] - bbox[1]
            if width < 40 or height < 40:
                continue

            y0 = bbox[1]
            question_number = None
            for number, anchor_y in anchors:
                if anchor_y <= y0:
                    question_number = number
                else:
                    break
            if not question_number:
                continue

            xref = block.get("xref")
            if not xref:
                continue

            try:
                image = doc.extract_image(xref)
            except Exception:
                continue

            data = image.get("image", b"")
            if not data:
                continue

            ext = image.get("ext", "png")
            encoded = base64.b64encode(data).decode("ascii")
            name = f"q{question_number}_img{len(question_images.get(question_number, [])) + 1}.{ext}"
            mimetype = f"image/{ext}"

            question_images.setdefault(question_number, []).append(
                {"name": name, "data": encoded, "mimetype": mimetype}
            )

    payload = {"questions": question_images}
    sys.stdout.write(json.dumps(payload))


if __name__ == "__main__":
    if len(sys.argv) < 2:
        sys.stdout.write(json.dumps({"questions": {}}))
        sys.exit(0)
    main(sys.argv[1])
