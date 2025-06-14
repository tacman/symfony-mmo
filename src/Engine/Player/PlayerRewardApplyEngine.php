<?php

namespace App\Engine\Player;

use App\Engine\Item\ItemInstantiator;
use App\Engine\Reward\MasteryReward;
use App\Entity\Data\PlayerCharacter;
use App\GameElement\Gathering\Reward\ItemReward;
use App\GameElement\Item\Exception\MaxBagSizeReachedException;
use App\GameElement\Mastery\Engine\MasteryTypeRepository;
use App\GameElement\Notification\Engine\NotificationEngine;
use App\GameElement\Reward\RewardApplierInterface;
use App\GameElement\Reward\RewardApply;
use App\GameObject\Item\AbstractBaseItemPrototype;
use App\Repository\Data\PlayerCharacterRepository;

readonly class PlayerRewardApplyEngine implements RewardApplierInterface
{
    public function __construct(
        private PlayerCharacterRepository $repository,
        private PlayerItemEngine          $playerEngine,
        private NotificationEngine        $notificationEngine,
        private ItemInstantiator          $instantiator,
        private MasteryTypeRepository     $masteryEngine,
    )
    {
    }

    public function supports(RewardApply $rewardApply): bool
    {
        return $rewardApply->getRecipe() instanceof PlayerCharacter;
    }

    public function apply(RewardApply $rewardApply): void
    {
        /** @var PlayerCharacter $player */
        $player = $rewardApply->getRecipe();

        $reward = $rewardApply->getReward();
        if ($reward instanceof MasteryReward) {
            $player->increaseMasteryExperience($reward->getMasteryId(), $reward->getExperience());
            $this->repository->save($player);
            $this->notificationEngine->success($player->getId(), sprintf('<span class="fas fa-dumbbell"></span> +%s experience on %s', $reward->getQuantity(), $this->masteryEngine->get($reward->getMasteryId())::getName()));
        }

        if ($reward instanceof ItemReward) {
            try {
                /** @var AbstractBaseItemPrototype $item */
                $item = $reward->getItem();
                $this->playerEngine->giveItem($player, $this->instantiator->createFrom($item, $reward->getQuantity()));
                $this->notificationEngine->success($player->getId(), sprintf('<span class="fas fa-gift"></span> +%s %s', $reward->getQuantity(), $reward->getItem()->getName()));
            } catch (MaxBagSizeReachedException $e) {
                $this->notificationEngine->danger($player->getId(), 'Your bag is full, you cannot receive the item.');
                return;
            }
        }

    }
}