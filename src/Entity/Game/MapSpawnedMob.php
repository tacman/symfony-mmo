<?php

namespace App\Entity\Game;

use App\Engine\Mob\MobCombatManager;
use App\Engine\Mob\MobToken;
use App\GameElement\Combat\Component\Combat;
use App\GameElement\Combat\HasCombatComponentInterface;
use App\GameElement\Core\GameObject\GameObjectReference;
use App\GameElement\Core\Token\TokenInterface;
use App\GameElement\Core\Token\TokenizableInterface;
use App\GameElement\Mob\AbstractMob;
use App\GameElement\Mob\AbstractMobInstance;
use App\GameObject\Map\AbstractBaseMap;
use App\Repository\Game\MapSpawnedMobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: MapSpawnedMobRepository::class)]
#[Broadcast(topics: ['@="map_spawned_mobs_" ~ entity.getMapId()'], private: true, template: 'map/spawned_mob_list.stream.html.twig')]
class MapSpawnedMob extends AbstractMobInstance implements HasCombatComponentInterface, TokenizableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 50)]
    private ?string $mapId;

    #[ORM\Column(length: 50)]
    private string $mobId;

    #[ORM\Column(type: 'json_document', nullable: false)]
    protected array $components = [];

    #[GameObjectReference(class: AbstractMob::class,objectIdProperty: 'mobId')]
    protected AbstractMob $mob;

    #[GameObjectReference(class: AbstractBaseMap::class,objectIdProperty: 'mapId')]
    protected AbstractBaseMap $map;

    public function __construct(AbstractBaseMap $map, AbstractMob $mob, array $components = [])
    {
        $this->id = Uuid::v7();
        $this->map = $map;
        $this->mapId = $map->getId();
        $this->mobId = $mob->getId();
        parent::__construct($mob, $components);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getMapId(): ?string
    {
        return $this->mapId;
    }

    public function setMapId(string $mapId): static
    {
        $this->mapId = $mapId;

        return $this;
    }

    public function cloneComponent(): void
    {
        $components = $this->getComponents();
        $this->components = [];
        foreach ($components as $component) {
            $this->setComponent($component::class, clone $component);
        }
    }

    public static function getCombatManagerClass(): string
    {
        return MobCombatManager::class;
    }

    public function getToken(): TokenInterface
    {
        return new MobToken($this->id);
    }

    public function getCombatComponent(): Combat
    {
        return $this->getComponent(Combat::class);
    }
}
