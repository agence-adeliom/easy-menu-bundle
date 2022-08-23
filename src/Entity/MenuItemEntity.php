<?php

namespace Adeliom\EasyMenuBundle\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityPublishableTrait;
use Adeliom\EasyCommonBundle\Traits\EntityThreeStateStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Adeliom\EasyFieldsBundle\Traits\PositionSortableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 */
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: 'Adeliom\EasyMenuBundle\Repository\MenuItemRepository')]
class MenuItemEntity
{
    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private __TimestampableConstruct;
    }
    use EntityThreeStateStatusTrait;
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private __PublishableConstruct;
    }

    /**
     * @Gedmo\TreeLeft
     */
    #[ORM\Column(name: 'lft', type: 'integer')]
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     */
    #[ORM\Column(name: 'lvl', type: 'integer')]
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     */
    #[ORM\Column(name: 'rgt', type: 'integer')]
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     */
    #[ORM\Column(name: 'root', type: 'integer', nullable: true)]
    protected $root;


    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param mixed $lft
     */
    public function setLft($lft): void
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

    /**
     * @param mixed $lvl
     */
    public function setLvl($lvl): void
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

    /**
     * @param mixed $rgt
     */
    public function setRgt($rgt): void
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

    /**
     * @param mixed $root
     */
    public function setRoot($root): void
    {
        $this->root = $root;
    }

    public function getSortableData($name)
    {
        return $this->{$name};
    }

    /**
     * @var MenuEntity | null
     */
    protected $menu;
    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    protected $name;
    /**
     * @var string | null
     */
    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    protected $url;
    /**
     * @var string | null
     */
    #[ORM\Column(name: 'class_attribute', type: 'string', length: 255, nullable: true)]
    protected $classAttribute;
    /**
     * @var integer
     */
    #[ORM\Column(name: 'position', type: 'smallint', options: ['unsigned' => true], nullable: true)]
    protected $position;
    /**
     * @var bool
     */
    #[ORM\Column(name: 'target', type: 'boolean', nullable: true, options: ['default' => false])]
    protected $target;
    /**
     * @Gedmo\TreeParent
     * @var MenuItemEntity | null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\EasyMenu\MenuItem', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $parent;
    /**
     * @var MenuItemEntity[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\EasyMenu\MenuItem', mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;
    public function __construct()
    {
        $this->__TimestampableConstruct();
        $this->__PublishableConstruct();
        $this->children = new ArrayCollection();
        $this->state = ThreeStateStatusEnum::PENDING();
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    /**
     * @return string | null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
    /**
     * @param string | null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
    /**
     * @return string | null
     */
    public function getClassAttribute(): ?string
    {
        return $this->classAttribute;
    }
    /**
     * @param string | null $classAttribute
     */
    public function setClassAttribute(?string $classAttribute): void
    {
        $this->classAttribute = $classAttribute;
    }
    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }
    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
    /**
     * @return bool
     */
    public function isTarget(): bool
    {
        return $this->target;
    }
    /**
     * @param bool $target
     */
    public function setTarget(bool $target): void
    {
        $this->target = $target;
    }
    /**
     * @return MenuEntity|null
     */
    public function getMenu(): ?MenuEntity
    {
        return $this->menu;
    }
    /**
     * @param MenuEntity|null $menu
     */
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
    /**
     * @param MenuItemEntity $parent
     */
    public function setParent(MenuItemEntity $parent)
    {
        $this->parent = $parent;

        if (!is_null($parent)) {
            $parent->addChild($this);
        }
    }
    /**
     * Add child
     * @param MenuItemEntity $child
     */
    public function addChild(MenuItemEntity $child)
    {
        $this->children[] = $child;
    }
    /**
     * Remove child
     *
     * @param MenuItemEntity $child
     */
    public function removeChild(MenuItemEntity $child)
    {
        $this->children->removeElement($child);
    }
    /**
     * Set children
     * @param ArrayCollection $children
     */
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;
    }
    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
    /**
     * Get only published children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPublishedChildren()
    {
        return $this->children->filter(function (MenuItemEntity $child) {
            return $child->getState() == ThreeStateStatusEnum::PUBLISHED();
        });
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
    public function onRemove() : void
    {
        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
    }
    /**
     * Has child
     */
    public function hasChild()
    {
        return count($this->children) > 0;
    }
    /**
     * Has parent
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
    public function getFlattenParents() : string
    {
        return implode(' / ', array_reverse($this->getParents()) );
    }
    public function __toString()
    {
        return isset($this->name) ? $this->name : "";
    }
}
