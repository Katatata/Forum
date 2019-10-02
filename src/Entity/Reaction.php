<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReactionRepository")
 */
class Reaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="reactions")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reactions")
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Reaction", inversedBy="reactions")
     */
    private $parentReaction;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reaction", mappedBy="parentReaction")
     */
    private $reactions;

    public function __construct()
    {
        $this->reactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    
    public function __toString() {
        return $this->body;
    }

    public function getParentReaction(): ?self
    {
        return $this->parentReaction;
    }

    public function setParentReaction(?self $parentReaction): self
    {
        $this->parentReaction = $parentReaction;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(self $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions[] = $reaction;
            $reaction->setParentReaction($this);
        }

        return $this;
    }

    public function removeReaction(self $reaction): self
    {
        if ($this->reactions->contains($reaction)) {
            $this->reactions->removeElement($reaction);
            // set the owning side to null (unless already changed)
            if ($reaction->getParentReaction() === $this) {
                $reaction->setParentReaction(null);
            }
        }

        return $this;
    }
}
