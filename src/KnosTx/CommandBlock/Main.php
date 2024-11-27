<?php

namespace KnosTx\CommandBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;

/**
 * CommandBlock implementation for PocketMine-MP.
 */
class Main extends PluginBase implements Listener {

    public const COMMAND_BLOCK_ID = 77777;

    /** @var array Data storage for command blocks */
    private array $commandBlockData = [];

    /** @var Block $blockVar */
    private Block $blockVar;

    /**
     * Constructor of CommandBlock
     */
    public function __construct() {
        parent::__construct();
        // No need to pass parameters; PluginBase handles them
    }

    /**
     * Called when the plugin is enabled.
     */
    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * Handle Command Block placement.
     *
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlockPlaced();

        if ($block->getTypeId() === self::COMMAND_BLOCK_ID) {
            if (!$player->hasPermission("commandblock.use")) {
                $player->sendMessage("§cYou do not have permission to place Command Blocks!");
                $event->cancel();
            } else {
                $this->initializeCommandBlockData($block);
            }
        }
    }

    /**
     * Handle Command Block destruction.
     *
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();

        if ($block->getTypeId() === self::COMMAND_BLOCK_ID) {
            $player = $event->getPlayer();
            $this->removeCommandBlockData($block);
        }
    }

    /**
     * Handle interaction with Command Blocks.
     *
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($block->getTypeId() === self::COMMAND_BLOCK_ID) {
            if (!$player->hasPermission("commandblock.edit")) {
                $player->sendMessage("§cYou do not have permission to edit Command Blocks!");
                return;
            }
            $this->openCommandBlockForm($player, $block);
        }
    }

    /**
     * Open a form for editing Command Block settings.
     *
     * @param Player $player
     * @param Block $block
     */
    private function openCommandBlockForm(Player $player, Block $block): void {
        $blockKey = $this->getBlockKey($block);
        $data = $this->commandBlockData[$blockKey] ?? [
            "command" => "",
            "blockType" => 0,
            "conditional" => false,
            "needsRedstone" => true
        ];

        $form = new CustomForm(function (Player $player, ?array $formData) use ($blockKey) {
            if ($formData === null) {
                $player->sendMessage("§eCommand editing cancelled.");
                return;
            }

            $this->commandBlockData[$blockKey] = [
                "command" => $formData[0] ?? "",
                "blockType" => $formData[1] ?? 0,
                "conditional" => $formData[2] ?? false,
                "needsRedstone" => $formData[3] ?? true
            ];

            $player->sendMessage("§aCommand Block updated successfully!");
        });

        $form->setTitle("Command Block Settings");
        $form->addInput("Command:", "e.g., say Hello, World!", $data["command"]);
        $form->addDropdown("Block Type:", ["Impulse", "Chain", "Repeat"], $data["blockType"]);
        $form->addToggle("Conditional?", $data["conditional"]);
        $form->addToggle("Needs Redstone?", $data["needsRedstone"]);
        $player->sendForm($form);
    }

    /**
     * Initialize data for a new Command Block.
     *
     * @param Block $block
     */
    private function initializeCommandBlockData(Block $block): void {
        $blockKey = $this->getBlockKey($block);
        $this->commandBlockData[$blockKey] = [
            "command" => "",
            "blockType" => 0,
            "conditional" => false,
            "needsRedstone" => true
        ];
    }

    /**
     * Remove data for a destroyed Command Block.
     *
     * @param Block $block
     */
    private function removeCommandBlockData(Block $block): void {
        $blockKey = $this->getBlockKey($block);
        unset($this->commandBlockData[$blockKey]);
    }

    /**
     * Get a unique key for a block based on its position.
     *
     * @param Block $block
     * @return string
     */
    private function getBlockKey(Block $block): string {
        return $block->getPosition()->__toString();
    }
}
