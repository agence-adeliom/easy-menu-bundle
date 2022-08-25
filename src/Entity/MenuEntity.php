<?php

namespace Adeliom\EasyMenuBundle\Entity;

use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity('code')]
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyMenuBundle\Repository\MenuRepository::class)]
class MenuEntity implements \Stringable
{
    public $menuItems;

    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private TimestampableConstruct;
    }
    use EntityStatusTrait;

    /**
     * @var MenuItemEntity[] | null
     */
    protected $items;

    /**
     * @var string
     **/
    #[ORM\Column(name: 'code', type: \Doctrine\DBAL\Types\Types::STRING, length: 30)]
    protected ?string $code = null;

    /**
     * @var string | null
     */
    #[ORM\Column(name: 'name', type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $name = null;

    /**
     * @var MenuItemEntity | null
     */
    protected $rootItem;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->TimestampableConstruct();
        $this->items = new ArrayCollection();
    }

    /**
     * Set name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string | null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get menuItems
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * @return MenuItemEntity[]|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem(MenuItemEntity $item): void
    {
        $this->items->add($item);
        if ($item->getMenu() !== $this) {
            $item->setMenu($this);
        }
    }

    public function removeItem(MenuItemEntity $item): void
    {
        $this->items->removeElement($item);
        $item->setMenu(null);
    }

    #[ORM\PreRemove]
    public function onRemove(): void
    {
        $this->setStatus(false);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return MenuItemEntity|null
     */
    public function getRootItem(): ?MenuItemEntity
    {
        return $this->rootItem;
    }

    public function setRootItem(?MenuItemEntity $rootItem): void
    {
        $this->rootItem = $rootItem;
    }

    public function __toString(): string
    {
        return $this->name ?? "";
    }
}
