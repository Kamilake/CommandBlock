<?php

/*
 * @license GPL-3.0
 * @author KnosTx <nurazligaming@gmail.com>
 * @link https://github.com/KnosTx
 *
 * © Copyright 2024 KnosTx
 *
 * Copyright is protected by the Law of the country.
 *
 */

declare(strict_types=1);

namespace KnosTx\CommandBlock;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

/**
 * CommandBlock implementation for PocketMine-MP.
 */
class Main extends PluginBase implements Listener {

	/** @var int Unique block ID for the Command Block */
	public const COMMAND_BLOCK_ID = 77777;

	/**
	 * @var array Stores data for Command Blocks in the format:
	 * [
	 *    "blockKey" => [
	 *        "command" => string,
	 *        "blockType" => int,
	 *        "conditional" => bool,
	 *        "needsRedstone" => bool
	 *    ]
	 * ]
	 */
	private array $commandBlockData = [];

	/**
	 * Constructor of CommandBlock
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Called when the plugin is enabled.
	 *
	 * Registers the event listener for the plugin.
	 */
	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * Handles the placement of Command Blocks.
	 *
	 * @param BlockPlaceEvent $event The event triggered when a block is placed.
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void {
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
	 * Handles the breaking of Command Blocks.
	 *
	 * @param BlockBreakEvent $event The event triggered when a block is broken.
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void {
		$block = $event->getBlock();

		if ($block->getTypeId() === self::COMMAND_BLOCK_ID) {
			$this->removeCommandBlockData($block);
		}
	}

	/**
	 * Handles interaction with Command Blocks.
	 *
	 * @param PlayerInteractEvent $event The event triggered when a player interacts with a block.
	 */
	public function onInteract(PlayerInteractEvent $event) : void {
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
	 * Opens a form for editing Command Block settings.
	 *
	 * @param Player $player The player interacting with the Command Block.
	 * @param Block  $block  The Command Block being edited.
	 */
	private function openCommandBlockForm(Player $player, Block $block) : void {
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
	 * Initializes data for a newly placed Command Block.
	 *
	 * @param Block $block The Command Block to initialize data for.
	 */
	private function initializeCommandBlockData(Block $block) : void {
		$blockKey = $this->getBlockKey($block);
		$this->commandBlockData[$blockKey] = [
			"command" => "",
			"blockType" => 0,
			"conditional" => false,
			"needsRedstone" => true
		];
	}

	/**
	 * Removes data associated with a destroyed Command Block.
	 *
	 * @param Block $block The Command Block being removed.
	 */
	private function removeCommandBlockData(Block $block) : void {
		$blockKey = $this->getBlockKey($block);
		unset($this->commandBlockData[$blockKey]);
	}

	/**
	 * Gets a unique key for a block based on its position.
	 *
	 * @param Block $block The block to generate a unique key for.
	 * @return string The unique key for the block.
	 */
	private function getBlockKey(Block $block) : string {
		return $block->getPosition()->__toString();
	}
}
