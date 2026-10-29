#!/usr/bin/env python3
"""Validate the complete Campus UI/library delta before any hosted write."""
import argparse
import hashlib
import json
from pathlib import Path
import re

RUNTIME_FILES = {
    'app/Http/Controllers/LearningController.php',
    'app/Http/Controllers/LearningResourceController.php',
    'app/Http/Controllers/SearchController.php',
    'app/Models/Lesson.php',
    'config/campus.php',
    'public/assets/css/campus-theme.css',
    'public/assets/css/campus-library.css',
    'resources/views/layouts/app.blade.php',
    'resources/views/welcome.blade.php',
    'resources/views/resources/index.blade.php',
    'routes/web.php',
    'public/build/manifest.json',
}
ASSET = re.compile(r'public/build/assets/[A-Za-z0-9_-]+\.(?:css|js|woff2?)\Z')


def inspect(root):
    root = Path(root).resolve()
    if not root.is_dir():
        raise ValueError('Missing runtime payload directory')
    files = {}
    for path in root.rglob('*'):
        if path.is_symlink():
            raise ValueError('Runtime payload contains a symlink')
        if not path.is_file():
            continue
        relative = path.relative_to(root).as_posix()
        if relative not in RUNTIME_FILES and not ASSET.fullmatch(relative):
            raise ValueError('Non-runtime or unexpected payload path: ' + relative)
        files[relative] = hashlib.sha256(path.read_bytes()).hexdigest()
    missing = RUNTIME_FILES - files.keys()
    if missing:
        raise ValueError('Incomplete runtime delta: ' + ', '.join(sorted(missing)))
    manifest = json.loads((root / 'public/build/manifest.json').read_text())
    for entry in manifest.values():
        if not isinstance(entry, dict) or not ASSET.fullmatch('public/build/' + str(entry.get('file', ''))):
            raise ValueError('Unsafe compiled asset path in Vite manifest')
    return files


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('directory', type=Path)
    args = parser.parse_args()
    try:
        files = inspect(args.directory)
    except (ValueError, OSError) as error:
        parser.exit(2, str(error) + '\n')
    print(json.dumps({'runtime_files': len(files), 'sha256': files}, indent=2))
