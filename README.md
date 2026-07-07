# RegionGuardian

**A land-claim & region protection plugin for PocketMine-MP servers.**

RegionGuardian lets players claim territory, invite members, defend their base from griefing and PvP, and expand their claim by purchasing upgraded plans through EconomyAPI.

[![Plugin](https://img.shields.io/badge/plugin-RegionGuardian-blue)](#)
[![Version](https://img.shields.io/badge/version-0.14.x_0.15.10-brightgreen)](#)
[![Platform](https://img.shields.io/badge/platform-PocketMine--MP-orange)](#)
[![License](https://img.shields.io/badge/license-MIT-lightgrey)](#license)

---

## Table of Contents

- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Commands](#commands)
- [Region Plans](#region-plans)
- [How Protection Works](#how-protection-works)
- [Data Storage](#data-storage)
- [Known Limitations](#known-limitations)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

---

## About

RegionGuardian gives players a self-service way to protect land on your server. Once claimed, a region blocks building, breaking, item usage, and PvP from anyone who isn't the owner or an invited member — no staff intervention required. Owners can grow their claim over time by buying higher-tier plans, add or kick members, and transfer ownership to another player.

## Features

- 🛡️ **Region protection** — blocks building, breaking, tool/item use, and PvP damage from non-members inside a claim
- 💰 **Economy-driven plans** — 12 upgrade tiers (Dirt → Premium) purchased through [EconomyAPI](https://poggit.pmmp.io/p/EconomyAPI)
- 👥 **Member management** — add/kick members, with a per-plan member cap
- 🔁 **Ownership transfer** — request/accept/deny flow to hand a region to another player
- 📍 **Live boundary alerts** — players get a popup when entering/leaving a claimed region
- 📋 **Region listing & lookup** — browse claimed regions and inspect any region's details
- 🧱 **Hazard-block protection** — placing/breaking flowing & still water, flowing & still lava, TNT, and fire is blocked inside claims
- 🔧 **Restricted tools & items** — flint & steel, buckets, and stone-tier tools are blocked from use inside other players' regions

## Requirements
- EconomyAPI plugin
- PocketMine-MP API 2.0.0

## Installation

1. Download the Plugin from here: [RegionGuardian.phar]() (or the source `.zip`)
2. Drop it into your server's `plugins/` folder.
3. Make sure `EconomyAPI` is also installed in `plugins/`.
4. Restart the server.
5. Edit the two hardcoded values described below before going live — see [Configuration](#configuration).

## Configuration

RegionGuardian currently does **not** ship a `config.yml`. Two values are hardcoded directly in `src/VeoZax/RegionGuardian/Main.php` and must be edited manually before deploying on your own server:

```php
private $allowedWorld = "SurvivalRealm"; // The world where /rg commands are usable
private $bypassUser = "veozax";          // Player name exempt from the world restriction
```

- **`$allowedWorld`** — the only world name in which `/rg` commands can be used. Change `"SurvivalRealm"` to match your server's actual survival world folder name.
- **`$bypassUser`** — a player name (lowercase) exempt from the world lock. Change `"veozax"` to your own in-game name, or remove the check entirely if you don't need it.

Region data is stored automatically at runtime — see [Data Storage](#data-storage).

## Commands

All commands are run under `/rg` (alias: `/region`).

| Command | Description |
|---|---|
| `/rg create <name>` | Claim a new region centered on your position (starts on the Dirt plan) |
| `/rg plans` | List all available region plans and their prices |
| `/rg buy <plan>` | Upgrade your region to a higher plan |
| `/rg addmember <player>` | Add a player to your region |
| `/rg kick <player>` | Remove a member from your region |
| `/rg setowner <player>` | Request an ownership transfer to another player |
| `/rg accept` | Accept a pending ownership transfer |
| `/rg deny` | Decline a pending ownership transfer |
| `/rg delete <region>` | Delete your own region |
| `/rg leave` | Leave a region you're a member of |
| `/rg list [page]` | List all claimed regions |
| `/rg myinfo` | View info about your own region |
| `/rg seeinfo <region>` | View info about a specific region |
| `/rg remove <region>` | Force-delete any region (owner/bypass use only) |

## Region Plans

Prices are charged through EconomyAPI when upgrading with `/rg buy <plan>`.

| Plan | Radius (blocks) | Member Limit | Price |
|---|---:|---:|---:|
| Dirt (default) | 10 | 4 | Free |
| Coal | 30 | 6 | 15,000 |
| Copper | 50 | 8 | 50,000 |
| Gold | 70 | 10 | 70,000 |
| Emerald | 90 | 12 | 90,000 |
| Diamond | 110 | 14 | 110,000 |
| Obsidian | 130 | 16 | 150,000 |
| Netherite | 150 | 18 | 350,000 |
| Bedrock | 170 | 20 | 450,000 |
| Glowstone | 190 | 22 | 500,000 |
| Redstone | 210 | 24 | 800,000 |
| Premium | 230 | 26 | 5,000,000 |

> Regions are square, sized `radius × 2` on each side, centered on the claim point (or the region's midpoint when upgrading).

## How Protection Works

- **Building & breaking**: only the region owner and its members can place or break blocks inside the claim boundary.
- **PvP**: attacking another player while they stand inside a region is blocked unless the attacker owns or belongs to that region.
- **Item/tool use**: flint & steel, buckets, and stone tools cannot be used inside a region you don't belong to.
- **Overlap prevention**: new claims and plan upgrades are rejected if the resulting boundary would overlap an existing region.
- **Boundary awareness**: players receive an on-screen popup when they enter or leave a claimed region.

## Data Storage

Region data is saved as JSON in the plugin's data folder:

```
plugin_data/RegionGuardian/regions.json
```

This file is created automatically on first run and rewritten whenever regions are created, upgraded, or deleted. No database is required.

## Known Limitations

Contributions welcome — these are open areas for improvement:

- No `config.yml`; `$allowedWorld` and `$bypassUser` must be edited directly in source (see [Configuration](#configuration))
- No dedicated permission nodes — access control is handled entirely through the hardcoded bypass name and region ownership/membership
- Regions are square and world-bound; no support for multiple protected worlds out of the box
- No automated tests

## Contributing

Issues and pull requests are welcome. If you're proposing a larger change (e.g. adding `config.yml` support or permission nodes), please open an issue first to discuss the approach.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-change`)
3. Commit your changes
4. Open a pull request

## Credits

Developed and maintained by **[VeoZax](https://github.com/VeoZaxOfficial)**.

## License

Released under the [MIT License](LICENSE). Feel free to use, modify, and redistribute with credit to the original author.
