<?php

namespace Adeliom\EasyMenuBundle\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityPublishableTrait;
use Adeliom\EasyCommonBundle\Traits\EntityThreeStateStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyMenuBundle\Repository\MenuItemRepository::class)]
#[Gedmo\Tree(type: 'nested')]
class MenuItemEntity implements \Stringable
{
    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private TimestampableConstruct;
    }
    use EntityThreeStateStatusTrait;
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private PublishableConstruct;
    }

    #[ORM\Column(name: 'lft', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[Gedmo\TreeLeft]
    protected ?int $lft = null;

    #[ORM\Column(name: 'lvl', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[Gedmo\TreeLevel]
    protected ?int $lvl = null;

    #[ORM\Column(name: 'rgt', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[Gedmo\TreeRight]
    protected ?int $rgt = null;

    #[ORM\Column(name: 'root', type: \Doctrine\DBAL\Types\Types::INTEGER, nullable: true)]
    #[Gedmo\TreeRoot]
    protected ?int $root = null;

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    public function setLft(mixed $lft): void
    {
        $this->lft = $lft;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    public function setLvl(mixed $lvl): void
    {
        $this->lvl = $lvl;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRgt(mixed $rgt): void
    {
        $this->rgt = $rgt;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    public function setRoot(mixed $root): void
    {
        $this->root = $root;
    }

    public function getSortableData($name)
    {
        return $this->{$name};
    }

    /**
     * @var MenuEntity|null
     */
    protected $menu;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    protected ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'url', type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $url = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'class_attribute', type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $classAttribute = null;

    /**
     * @var int
     */
    #[ORM\Column(name: 'position', type: \Doctrine\DBAL\Types\Types::SMALLINT, options: ['unsigned' => true], nullable: true)]
    protected ?int $position = null;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'target', type: \Doctrine\DBAL\Types\Types::BOOLEAN, nullable: true, options: ['default' => false])]
    protected ?bool $target = null;

    /**
     * @var MenuItemEntity|null
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\EasyMenu\MenuItem::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', onDelete: 'CASCADE')]
    #[Gedmo\TreeParent]
    protected ?\App\Entity\EasyMenu\MenuItem $parent = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\EasyMenu\MenuItem>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\EasyMenu\MenuItem::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected \Doctrine\Common\Collections\Collection $children;

    public function __construct()
    {
        $this->TimestampableConstruct();
        $this->PublishableConstruct();
        $this->children = new ArrayCollection();
        $this->state = ThreeStateStatusEnum::PENDING();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getClassAttribute(): ?string
    {
        return $this->classAttribute;
    }

    public function setClassAttribute(?string $classAttribute): void
    {
        $this->classAttribute = $classAttribute;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isTarget(): bool
    {
        return $this->target;
    }

    public function setTarget(bool $target): void
    {
        $this->target = $target;
    }

    public function getMenu(): ?MenuEntity
    {
        return $this->menu;
    }

    public function setMenu(?MenuEntity $menu): void
    {
        $this->menu = $menu;
    }

    /**
     * @return MenuItemEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(MenuItemEntity $parent)
    {
        $this->parent = $parent;

        if (!is_null($parent)) {
            $parent->addChild($this);
        }
    }

    /**
     * Add child.
     */
    public function addChild(MenuItemEntity $child)
    {
        $this->children[] = $child;
    }

    /**
     * Remove child.
     */
    public function removeChild(MenuItemEntity $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Set children.
     */
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;
    }

    /**
     * Get children.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get only published children.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPublishedChildren()
    {
        return $this->children->filter(static fn (MenuItemEntity $child) => $child->getState() == ThreeStateStatusEnum::PUBLISHED());
    }

    /**
     * @return string|ThreeStateStatusEnum|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string|ThreeStateStatusEnum|null $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    #[ORM\PreRemove]
    public function onRemove(): void
    {
        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
    }

    /**
     * Has child.
     */
    public function hasChild()
    {
        return count($this->children) > 0;
    }

    /**
     * Has parent.
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent);
    }

    public function getParents($parents = [], $parent = null)
    {
        if (empty($parent)) {
            $parents[] = (string) $this;
            $parent = $this;
        }

        if (!empty($parent->getParent())) {
            $parentParent = $parent->getParent();
            $parents[] = (string) $parentParent;
            $parents = $this->getParents($parents, $parentParent);
        }

        return $parents;
    }

    public function getFlattenParents(): string
    {
        return implode(' / ', array_reverse($this->getParents()));
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
