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

	public const COMMAND_BLOCK_ID = 247;

	/** @var array Data storage for command blocks */
	private array $commandBlockData = [];

	/**
	 * Called when the plugin is enabled.
	 */
	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * Handle Command Block placement.
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();
		$placedBlock = $event->getBlockAgainst();

		if ($placedBlock->getTypeId() === self::COMMAND_BLOCK_ID) {
			if (!$player->hasPermission("commandblock.use")) {
				$player->sendMessage("§cYou do not have permission to place Command Blocks!");
				$event->cancel();
			} else {
				$this->initializeCommandBlockData($placedBlock);
				$player->sendMessage("§aCommand Block placed successfully!");
			}
		}
	}

	/**
	 * Handle Command Block destruction.
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void {
		$brokenBlock = $event->getBlock();

		if ($brokenBlock->getTypeId() === self::COMMAND_BLOCK_ID) {
			$this->removeCommandBlockData($brokenBlock);
			$event->getPlayer()->sendMessage("§aCommand Block destroyed!");
		}
	}

	/**
	 * Handle interaction with Command Blocks.
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
	 * Open a form for editing Command Block settings.
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
	 * Initialize data for a new Command Block.
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
	 * Remove data for a destroyed Command Block.
	 */
	private function removeCommandBlockData(Block $block) : void {
		$blockKey = $this->getBlockKey($block);
		unset($this->commandBlockData[$blockKey]);
	}

	/**
	 * Get a unique key for a block based on its position.
	 */
	private function getBlockKey(Block $block) : string {
		$pos = $block->getPosition();
		return "{$pos->getX()}:{$pos->getY()}:{$pos->getZ()}:{$pos->getWorld()->getFolderName()}";
	}
}
