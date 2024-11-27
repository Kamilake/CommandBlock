# CommandBlock
This plugin adds support for **Command Blocks** in PocketMine-MP servers. Command Blocks allow players or server administrators to run commands automatically when triggered. This plugin is designed to be easy to use while maintaining flexibility for advanced configurations.

## Features
- Placeable **Command Blocks** with customizable settings.
- Supports three block types:
  - Impulse
  - Chain
  - Repeat
- Conditional execution and redstone requirement options.
- Simple form-based UI for editing block settings.
- Permission-based access control:
  - `commandblock.use` - Allows players to place Command Blocks.
  - `commandblock.edit` - Allows players to edit Command Block settings.

## Installation
1. Download the plugin `.phar` file.
2. Place the `.phar` file in the `plugins` folder of your PocketMine-MP server.
3. Restart the server.

## Usage
1. Place a block with the **Command Block ID** (default: `77777`).
2. Right-click the block to open the settings form.
3. Configure the block:
   - Add a command (e.g., `say Hello, World!`).
   - Select the block type.
   - Toggle "Conditional" or "Needs Redstone" as needed.
4. Save the changes, and the Command Block will execute the command under the configured conditions.

## Permissions
- `commandblock.use`: Required to place Command Blocks.
- `commandblock.edit`: Required to edit Command Block settings.

## Support
For issues, please visit the [GitHub repository](https://github.com/KnosTx) or contact the author at **nurazligaming@gmail.com**.

## License
This plugin is licensed under the **GPL-3.0** license. Refer to the [LICENSE](LICENSE) file for details.
