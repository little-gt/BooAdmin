# Tailwind CSS Build Configuration

BooAdmin ships with two Tailwind CSS setups: **v4** (recommended) and **v3** (legacy). Both compile `config/<version>/input.css` into `admin/css/tailwind.css`.

## Directory Layout

```
config/
├── v4/                     # Tailwind CSS v4 (recommended)
│   ├── input.css           # Entry file — CSS-first config + Discord theme
│   └── tailwind.config.js  # Minimal config (content path only)
└── v3/                     # Tailwind CSS v3 (legacy)
    ├── input.css           # Entry file
    └── tailwind.config.js  # JS-based config
```

## Prerequisites

Standalone Tailwind CLI binaries are provided at the repository root, so no Node.js or `npm install` is required:

| Binary                  | Purpose   |
| ----------------------- | --------- |
| `tailwindcss-4.4.3.exe` | v4 builds |
| `tailwindcss-3.4.17.exe`| v3 builds |

Run all commands from the project root.

## Build

### Tailwind CSS v4 (recommended)

```bash
# Development — watch mode
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js -w

# Production — minified
.\tailwindcss-4.4.3.exe -i ./config/v4/input.css -o ./admin/css/tailwind.css --config ./config/v4/tailwind.config.js --minify
```

v4 uses a CSS-first configuration: content paths and theme tokens are declared directly in `input.css` via `@source` and `@theme`.

### Tailwind CSS v3 (legacy)

```bash
.\tailwindcss-3.4.17.exe -i ./config/v3/input.css -o ./admin/css/tailwind.css --config ./config/v3/tailwind.config.js
```

Append `-w` for watch mode or `--minify` for production output.

## Discord Theme Tokens

Design tokens are defined under the `discord` namespace and resolve to CSS variables:

| Token                     | Value    | Usage                        |
| ------------------------- | -------- | ---------------------------- |
| `--color-discord-light`   | `#f3f4f6`| Light background             |
| `--color-discord-sidebar` | `#202225`| Sidebar background           |
| `--color-discord-active`  | `#36393f`| Active / selected background |
| `--color-discord-accent`  | `#5865f2`| Accent / primary actions     |
| `--color-discord-text`    | `#dcddde`| Body text                    |
| `--color-discord-muted`   | `#72767d`| Muted / secondary text       |

Corresponding utility classes are generated automatically, e.g. `bg-discord-accent`, `text-discord-muted`, `border-discord-sidebar`.

## Tips

This version uses TailwindCSS 4 for style compilation. If you don't like TailwindCSS 4 or think there are issues with the compiled styles, you can compile the styles using version 3 yourself.