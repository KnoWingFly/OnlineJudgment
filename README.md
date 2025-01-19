# ONJ

> An online judge system for programming competitions and practice

## Table of Contents
- [Installation](#installation)
- [System Compatibility](#system-compatibility)
- [Important Warnings](#important-warnings)
- [Managing Problems](#managing-problems)
- [Configuration](#configuration)

##  Installation
1. Place these files in a directory under your web root
2. Edit `setup` and set the variables to appropriate values
3. Run `./setup`
4. If you need the database for its, just import from "onj.sql"

## System Compatibility
| Platform | Status | Notes |
|----------|--------|-------|
| WSL | Tested | Fully functional |
| Windows Native | Not Tested | Testing needed |
| Linux Native | Not Tested | Testing needed |

## Important Warnings
- **CRITICAL**: Do NOT run setup after the initial installation
- This will reset the database and all existing data will be lost

## Managing Problems
Problems are organized in the `problems/` directory with the following structure:

```
problems/
├── 1/
├── 2/
├── 3/
├── 4/
└── 5/
```

Each problem folder contains:
- `statement`: HTML file containing the problem description
- `in`: System test input file
- `out`: Expected output file

### Admin Features
Problems can also be edited through the web interface when logged in as an administrator.

## Configuration
Settings can be modified after installation by editing `settings.php`

---

### Contributing
Testing and verification on other platforms is welcome - please report any issues!